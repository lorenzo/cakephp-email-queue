<?php
declare(strict_types=1);

namespace EmailQueue;

use Cake\ORM\TableRegistry;

class EmailQueue
{
    /**
     * Stores a new email message in the queue.
     *
     * @param mixed $to      email or array of emails as recipients
     * @param array $data    associative array of variables to be passed to the email template
     * @param array $options list of options for email sending. Possible keys:
     *
     * - subject : Email's subject
     * - send_at : date time sting representing the time this email should be sent at (in UTC)
     * - template :  the name of the element to use as template for the email message
     * - layout : the name of the layout to be used to wrap email message
     * - format: Type of template to use (html, text or both)
     * - headers: Key => Value list of extra headers for the email
     * - theme: The View Theme to find the email templates
     * - config : the name of the email config to be used for sending
     * @return bool
     */
    public static function enqueue($to, array $data, array $options = [])
    {
        return TableRegistry::getTableLocator()
            ->get('EmailQueue.EmailQueue')
            ->enqueue($to, $data, $options);
    }
}
