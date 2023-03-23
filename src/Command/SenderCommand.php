<?php

declare(strict_types=1);

namespace EmailQueue\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use Cake\Network\Exception\SocketException;
use Cake\ORM\TableRegistry;
use EmailQueue\Model\Table\EmailQueueTable;

/**
 * Sender command.
 */
class SenderCommand extends Command
{
	/**
	 * Hook method for defining this command's option parser.
	 *
	 * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
	 * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
	 * @return \Cake\Console\ConsoleOptionParser The built parser.
	 */
	public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
	{
		$parser = parent::buildOptionParser($parser);

		$parser
			->setDescription('Sends queued emails in a batch')
			->addOption(
				'limit',
				[
					'short' => 'l',
					'help' => 'How many emails should be sent in this batch?',
					'default' => 50,
				]
			)
			->addOption(
				'template',
				[
					'short' => 't',
					'help' => 'Name of the template to be used to render email',
					'default' => 'default',
				]
			)
			->addOption(
				'layout',
				[
					'short' => 'w',
					'help' => 'Name of the layout to be used to wrap template',
					'default' => 'default',
				]
			)
			->addOption(
				'stagger',
				[
					'short' => 's',
					'help' => 'Seconds to maximum wait randomly before proceeding (useful for parallel executions)',
					'default' => false,
				]
			)
			->addOption(
				'config',
				[
					'short' => 'c',
					'help' => 'Name of email settings to use as defined in email.php',
					'default' => 'default',
				]
			)
			/* ->addSubCommand(
				'clearLocks',
				[
					'help' => 'Clears all locked emails in the queue, useful for recovering from crashes',
				]
			) */;


		// $this->params = [];

		// $this->params['stagger'] = $parser->

		return $parser;
	}

	protected function _fillParams(Arguments $args)
	{
		$this->params = [
			'stagger' => $args->getArgument('stagger'),
			'layout' => $args->getArgument('layout'),
			'config' => $args->getArgument('config'),
			'template' => $args->getArgument('template'),
			'limit' => $args->getArgument('limit'),
		];
	}

	/**
	 * Implement this method with your command's logic.
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io)
	{
		$this->_fillParams($args);
		$this->io = &$io;
		if ($this->params['stagger']) {
			sleep(random_int(0, $this->params['stagger']));
		}

		Configure::write('App.baseUrl', '/');
		// $emailQueue = TableRegistry::getTableLocator()->get('EmailQueue', ['className' => EmailQueueTable::class]);
		$emailQueue = $this->fetchTable('EmailQueueTable', [
			'className' => EmailQueueTable::class
		]);
		$emails = $emailQueue->getBatch($this->params['limit']);

		$count = count($emails);
		foreach ($emails as $e) {
			$configName = $e->config === 'default' ? $this->params['config'] : $e->config;
			$template = $e->template === 'default' ? $this->params['template'] : $e->template;
			$layout = $e->layout === 'default' ? $this->params['layout'] : $e->layout;
			$headers = empty($e->headers) ? [] : (array)$e->headers;
			$theme = empty($e->theme) ? '' : (string)$e->theme;
			$viewVars = empty($e->template_vars) ? [] : $e->template_vars;
			$errorMessage = null;

			try {
				$email = $this->_newEmail($configName);

				if (!empty($e->from_email) && !empty($e->from_name)) {
					$email->setFrom($e->from_email, $e->from_name);
				}

				$transport = $email->getTransport();

				if ($transport && $transport->getConfig('additionalParameters')) {
					$from = key($email->getFrom());
					$transport->setConfig(['additionalParameters' => "-f $from"]);
				}

				// set cc
				if (!empty($e->cc)) {
					$email->setCC($e->cc);
				}

				// set bcc
				if (!empty($e->bcc)) {
					$email->setBcc($e->bcc);
				}

				if (!empty($e->attachments)) {
					$email->setAttachments($e->attachments);
				}

				$sent = $email
					->setTo($e->email)
					->setSubject($e->subject)
					->setEmailFormat($e->format)
					->addHeaders($headers)
					->setViewVars($viewVars)
					->setMessageId(false)
					->setReturnPath($email->getFrom());

				$email->viewBuilder()
					->setLayout($layout)
					->setTheme($theme)
					->setTemplate($template);

				$email->deliver();
			} catch (SocketException $exception) {
				$this->io->error($exception->getMessage());
				$errorMessage = $exception->getMessage();
				$sent = false;
			}

			if ($sent) {
				$emailQueue->success($e->id);
				$this->io->out('<success>Email ' . $e->id . ' was sent</success>');
			} else {
				$emailQueue->fail($e->id, $errorMessage);
				$this->io->out('<error>Email ' . $e->id . ' was not sent</error>');
			}
		}
		if ($count > 0) {
			$locks = collection($emails)->extract('id')->toList();
			$emailQueue->releaseLocks($locks);
		}
	}

	/**
	 * Clears all locked emails in the queue, useful for recovering from crashes.
	 *
	 * @return void
	 */
	public function clearLocks(): void
	{
		TableRegistry::getTableLocator()
			->get('EmailQueue', ['className' => EmailQueueTable::class])
			->clearLocks();
	}

	/**
	 * Returns a new instance of CakeEmail.
	 *
	 * @param array|string $config array of configs, or string to load configs from app.php
	 * @return \Cake\Mailer\Mailer
	 */
	protected function _newEmail($config): Mailer
	{
		return new Mailer($config);
	}
}
