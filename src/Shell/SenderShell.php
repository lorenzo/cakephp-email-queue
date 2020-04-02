<?php
declare(strict_types=1);

namespace EmailQueue\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use Cake\Network\Exception\SocketException;
use Cake\ORM\TableRegistry;
use EmailQueue\Model\Table\EmailQueueTable;

class SenderShell extends Shell
{
    /**
     * Gets the option parser instance and configures it.
     *
     * By overriding this method you can configure the ConsoleOptionParser before returning it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     * @link https://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     */
    public function getOptionParser(): ConsoleOptionParser
    {
        $parser = parent::getOptionParser();
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
            ->addSubCommand(
                'clearLocks',
                [
                'help' => 'Clears all locked emails in the queue, useful for recovering from crashes',
                ]
            );

        return $parser;
    }

    /**
     * Sends queued emails.
     *
     * @return void
     */
    public function main(): void
    {
        if ($this->params['stagger']) {
            sleep(rand(0, $this->params['stagger']));
        }

        Configure::write('App.baseUrl', '/');
        $emailQueue = TableRegistry::getTableLocator()->get('EmailQueue', ['className' => EmailQueueTable::class]);
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
                $this->err($exception->getMessage());
                $errorMessage = $exception->getMessage();
                $sent = false;
            }

            if ($sent) {
                $emailQueue->success($e->id);
                $this->out('<success>Email ' . $e->id . ' was sent</success>');
            } else {
                $emailQueue->fail($e->id, $errorMessage);
                $this->out('<error>Email ' . $e->id . ' was not sent</error>');
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
