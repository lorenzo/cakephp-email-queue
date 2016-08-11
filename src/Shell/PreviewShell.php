<?php

namespace EmailQueue\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use EmailQueue\Model\Table\EmailQueueTable;
use Cake\Mailer\Email;

class PreviewShell extends Shell
{
    public function main()
    {
        Configure::write('App.baseUrl', '/');

        $conditions = [];
        if ($this->args) {
            $conditions['id IN'] = $this->args;
        }

        $emailQueue = TableRegistry::get('EmailQueue', ['className' => EmailQueueTable::class]);
        $emails = $emailQueue->find()->where($conditions)->toList();

        if (!$emails) {
            $this->out('No emails found');

            return;
        }

        $this->clear();
        foreach ($emails as $i => $email) {
            if ($i) {
                $this->in('Hit a key to continue');
                $this->clear();
            }
            $this->out('Email :'.$email['EmailQueue']['id']);
            $this->preview($email);
        }
    }

    public function preview($e)
    {
        $configName = $e['config'];
        $template = $e['template'];
        $layout = $e['layout'];
        $headers = empty($e['headers']) ? [] : (array) $e['headers'];
        $theme = empty($e['theme']) ? '' : (string) $e['theme'];

        $email = new Email($configName);
	
        if (!empty($e['attachments'])) {
            $email->attachments($e['attachments']);
        }
	
        $email->transport('Debug')
            ->to($e['email'])
            ->subject($e['subject'])
            ->template($template, $layout)
            ->emailFormat($e['format'])
            ->addHeaders($headers)
            ->theme($theme)
            ->messageId(false)
            ->returnPath($email->from())
            ->viewVars($e['template_vars']);

        $return = $email->send();

        $this->out('Content:');
        $this->hr();
        $this->out($return['message']);
        $this->hr();
        $this->out('Headers:');
        $this->hr();
        $this->out($return['headers']);
        $this->hr();
        $this->out('Data:');
        $this->hr();
        debug($e['template_vars']);
        $this->hr();
        $this->out();
    }
}
