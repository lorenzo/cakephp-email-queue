<?php

namespace EmailQueue\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use EmailQueue\Model\Table\EmailQueueTable;
use Cake\Network\Exception\SocketException;
use Cake\Mailer\Email;

class SenderShell extends Shell
{
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser
            ->description('Sends queued emails in a batch')
            ->addOption('limit', array(
                'short' => 'l',
                'help' => 'How many emails should be sent in this batch?',
                'default' => 50,
            ))
            ->addOption('template', array(
                'short' => 't',
                'help' => 'Name of the template to be used to render email',
                'default' => 'default',
            ))
            ->addOption('layout', array(
                'short' => 'w',
                'help' => 'Name of the layout to be used to wrap template',
                'default' => 'default',
            ))
            ->addOption('stagger', array(
                'short' => 's',
                'help' => 'Seconds to maximum wait randomly before proceeding (useful for parallel executions)',
                'default' => false,
            ))
            ->addOption('config', array(
                'short' => 'c',
                'help' => 'Name of email settings to use as defined in email.php',
                'default' => 'default',
            ))
            ->addSubCommand('clearLocks', array(
                'help' => 'Clears all locked emails in the queue, useful for recovering from crashes',
            ));

        return $parser;
    }

    /**
     * Sends queued emails.
     */
    public function main()
    {
        if ($this->params['stagger']) {
            sleep(rand(0, $this->params['stagger']));
        }

        Configure::write('App.baseUrl', '/');
        $emailQueue = TableRegistry::get('EmailQueue', ['className' => EmailQueueTable::class]);
        $emails = $emailQueue->getBatch($this->params['limit']);

        $count = count($emails);
        foreach ($emails as $e) {
            $configName = $e->layout === 'default' ? $this->params['config'] : $e->config;
            $template = $e->layout === 'default' ? $this->params['template'] : $e->template;
            $layout = $e->layout === 'default' ? $this->params['layout'] : $e->layout;
            $headers = empty($e->headers) ? array() : (array) $e->headers;
            $theme = empty($e->theme) ? '' : (string) $e->theme;

            try {
                $email = $this->_newEmail($configName);

                if (!empty($e->from_email) && !empty($e->from_name)) {
                    $email->from($e->from_email, $e->from_name);
                }

                $transport = $email->transport();

                if ($transport && $transport->config('additionalParameters')) {
                    $from = key($email->from());
                    $transport->config(['additionalParameters' => "-f $from"]);
                }

                $sent = $email
                    ->to($e->email)
                    ->subject($e->subject)
                    ->template($template, $layout)
                    ->emailFormat($e->format)
                    ->addHeaders($headers)
                    ->theme($theme)
                    ->viewVars($e->template_vars)
                    ->messageId(false)
                    ->returnPath($email->from())
                    ->send();
            } catch (SocketException $exception) {
                $this->err($exception->getMessage());
                $sent = false;
            }

            if ($sent) {
                $emailQueue->success($e->id);
                $this->out('<success>Email '.$e->id.' was sent</success>');
            } else {
                $emailQueue->fail($e->id);
                $this->out('<error>Email '.$e->id.' was not sent</error>');
            }
        }
        if ($count > 0)
            $emailQueue->releaseLocks(collection($emails)->extract('id')->toList());
    }

    /**
     * Clears all locked emails in the queue, useful for recovering from crashes.
     **/
    public function clearLocks()
    {
        TableRegistry::get('EmailQueue', ['className' => EmailQueueTable::class])->clearLocks();
    }

    /**
     * Returns a new instance of CakeEmail.
     *
     * @return Email
     **/
    protected function _newEmail($config)
    {
        return new Email($config);
    }
}
