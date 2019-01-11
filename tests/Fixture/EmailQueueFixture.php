<?php

namespace EmailQueue\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * EmailQueueFixture.
 */
class EmailQueueFixture extends TestFixture
{
    public $table = 'email_queue';

    /**
     * Fields.
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'uuid', 'null' => false],
        'email' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'from_name' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'from_email' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'subject' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 255, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'config' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 30, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'template' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'layout' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'format' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 5, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'template_vars' => ['type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'headers' => ['type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'error' => ['type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'sent' => ['type' => 'boolean', 'null' => false, 'default' => 0],
        'locked' => ['type' => 'boolean', 'null' => false, 'default' => 0],
        'send_tries' => ['type' => 'integer', 'null' => false, 'default' => 0, 'length' => 2],
        'send_at' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'created' => ['type' => 'datetime', 'null' => false, 'default' => null],
        'modified' => ['type' => 'datetime', 'null' => false, 'default' => null],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ];

    /**
     * Records.
     *
     * @var array
     */
    public $records = [
        [
            'id' => 'email-1',
            'email' => 'example@example.com',
            'from_name' => null,
            'from_email' => null,
            'subject' => 'Free dealz',
            'config' => 'default',
            'template' => 'default',
            'layout' => 'default',
            'format' => 'both',
            'template_vars' => '{"a":1,"b":2}',
            'headers' => '{"foo":"bar"}',
            'sent' => 0,
            'locked' => 0,
            'send_tries' => 1,
            'send_at' => '2011-06-20 13:50:48',
            'created' => '2011-06-20 13:50:48',
            'modified' => '2011-06-20 13:50:48',
        ],
        [
            'id' => 'email-2',
            'email' => 'example2@example.com',
            'from_name' => null,
            'from_email' => null,
            'subject' => 'Free dealz',
            'config' => 'default',
            'template' => 'default',
            'layout' => 'default',
            'format' => 'both',
            'template_vars' => '{"a":1,"b":2}',
            'headers' => '{"foo":"bar"}',
            'sent' => 0,
            'locked' => 0,
            'send_tries' => 2,
            'send_at' => '2011-06-20 13:50:48',
            'created' => '2011-06-20 13:50:48',
            'modified' => '2011-06-20 13:50:48',
        ],
        [
            'id' => 'email-3',
            'email' => 'example3@example.com',
            'from_name' => null,
            'from_email' => null,
            'subject' => 'Free dealz',
            'config' => 'default',
            'template' => 'default',
            'layout' => 'default',
            'format' => 'both',
            'template_vars' => '{"a":1,"b":2}',
            'headers' => '{"foo":"bar"}',
            'sent' => 0,
            'locked' => 0,
            'send_tries' => 3,
            'send_at' => '2011-06-20 13:50:48',
            'created' => '2011-06-20 13:50:48',
            'modified' => '2011-06-20 13:50:48',
        ],
        [
            'id' => 'email-4',
            'email' => 'example@example.com',
            'from_name' => null,
            'from_email' => null,
            'subject' => 'Free dealz',
            'config' => 'default',
            'template' => 'default',
            'layout' => 'default',
            'format' => 'both',
            'template_vars' => '{"a":1,"b":2}',
            'headers' => '{"foo":"bar"}',
            'sent' => 1,
            'locked' => 0,
            'send_tries' => 0,
            'send_at' => '2011-06-20 13:50:48',
            'created' => '2011-06-20 13:50:48',
            'modified' => '2011-06-20 13:50:48',
        ],
        [
            'id' => 'email-5',
            'email' => 'example@example.com',
            'from_name' => null,
            'from_email' => null,
            'subject' => 'Free dealz',
            'config' => 'default',
            'template' => 'default',
            'layout' => 'default',
            'format' => 'both',
            'template_vars' => '{"a":1,"b":2}',
            'headers' => '{"foo":"bar"}',
            'sent' => 0,
            'locked' => 1,
            'send_tries' => 0,
            'send_at' => '2011-06-20 13:50:48',
            'created' => '2011-06-20 13:50:48',
            'modified' => '2011-06-20 13:50:48',
        ],
        [
            'id' => 'email-6',
            'email' => 'example@example.com',
            'from_name' => null,
            'from_email' => null,
            'subject' => 'Free dealz',
            'config' => 'default',
            'template' => 'default',
            'layout' => 'default',
            'format' => 'both',
            'template_vars' => '{"a":1,"b":2}',
            'headers' => '{"foo":"bar"}',
            'sent' => 0,
            'locked' => 0,
            'send_tries' => 0,
            'send_at' => '2115-06-20 13:50:48',
            'created' => '2011-06-20 13:50:48',
            'modified' => '2011-06-20 13:50:48',
        ],
    ];
}
