<?php

declare(strict_types=1);

namespace EmailQueue\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use Cake\ORM\TableRegistry;

/**
 * Preview command.
 */
class PreviewCommand extends Command
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

		return $parser;
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
		$this->io = &$io;
		Configure::write('App.baseUrl', '/');

		$conditions = [];
		if ($args->getArgumentAt(0)) {
			$conditions['id IN'] = $args->getArgumentAt(0);
		}

		$emailQueue = TableRegistry::getTableLocator()->get('EmailQueue', ['className' => EmailQueueTable::class]);
		$emails = $emailQueue->find()->where($conditions)->all()->toList();

		if (!$emails) {
			$this->io->out('No emails found');

			return;
		}

		// $this->io->clear();
		foreach ($emails as $i => $email) {
			if ($i) {
				$this->io->ask('Hit a key to continue');
				// $this->clear();
			}
			$this->io->out('Email :' . $email['id']);
			$this->preview($email);
		}
	}

	/**
	 * Preview email
	 *
	 * @param array $e email data
	 * @return void
	 */
	public function preview($e)
	{
		$configName = $e['config'];
		$template = $e['template'];
		$layout = $e['layout'];
		$headers = empty($e['headers']) ? [] : (array)$e['headers'];
		$theme = empty($e['theme']) ? '' : (string)$e['theme'];

		$email = new Mailer($configName);

		// set cc
		if (!empty($e['cc'])) {
			$email->setCC($e['cc']);
		}

		// set bcc
		if (!empty($e['bcc'])) {
			$email->setBcc($e['bcc']);
		}

		if (!empty($e['attachments'])) {
			$email->setAttachments(unserialize($e['attachments']));
		}

		$email->setTransport('Debug')
			->setTo($e['email'])
			->setSubject($e['subject'])
			->setEmailFormat($e['format'])
			->addHeaders($headers)
			->setMessageId(false)
			->setReturnPath($email->getFrom())
			->setViewVars(unserialize($e['template_vars']));

		$email->viewBuilder()
			->setTheme($theme)
			->setTemplate($template)
			->setLayout($layout);

		$return = $email->deliver();

		$this->io->out('Content:');
		$this->io->hr();
		$this->io->out($return['message']);
		$this->io->hr();
		$this->io->out('Headers:');
		$this->io->hr();
		$this->io->out($return['headers']);
		$this->io->hr();
		$this->io->out('Data:');
		$this->io->hr();
		debug($e['template_vars']);
		$this->io->hr();
		$this->io->out('');
	}
}
