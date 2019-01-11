<?php
use Migrations\AbstractMigration;

class Initial extends AbstractMigration
{

    /**
     * Up Method
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-up-method
     *
     * @return void
     */
    public function up()
    {
        $table = $this->table('email_queue');
        $table
            ->addColumn(
                'email',
                'string',
                [
                'default' => null,
                'limit' => 129,
                'null' => false,
                ]
            )
            ->addColumn(
                'from_name',
                'string',
                [
                'default' => null,
                'limit' => 255,
                'null' => true,
                ]
            )
            ->addColumn(
                'from_email',
                'string',
                [
                'default' => null,
                'limit' => 255,
                'null' => true,
                ]
            )
            ->addColumn(
                'subject',
                'string',
                [
                'default' => null,
                'limit' => 255,
                'null' => false,
                ]
            )
            ->addColumn(
                'config',
                'string',
                [
                'default' => null,
                'limit' => 30,
                'null' => false,
                ]
            )
            ->addColumn(
                'template',
                'string',
                [
                'default' => null,
                'limit' => 50,
                'null' => false,
                ]
            )
            ->addColumn(
                'layout',
                'string',
                [
                'default' => null,
                'limit' => 50,
                'null' => false,
                ]
            )
            ->addColumn(
                'theme',
                'string',
                [
                'default' => null,
                'limit' => 50,
                'null' => false,
                ]
            )
            ->addColumn(
                'format',
                'string',
                [
                'default' => null,
                'limit' => 5,
                'null' => false,
                ]
            )
            ->addColumn(
                'template_vars',
                'text',
                [
                'default' => null,
                'limit' => null,
                'null' => false,
                ]
            )
            ->addColumn(
                'headers',
                'text',
                [
                'default' => null,
                'limit' => null,
                'null' => true,
                ]
            )
            ->addColumn(
                'sent',
                'boolean',
                [
                'default' => 0,
                'limit' => null,
                'null' => false,
                ]
            )
            ->addColumn(
                'locked',
                'boolean',
                [
                'default' => 0,
                'limit' => null,
                'null' => false,
                ]
            )
            ->addColumn(
                'send_tries',
                'integer',
                [
                'default' => 0,
                'limit' => 2,
                'null' => false,
                ]
            )
            ->addColumn(
                'send_at',
                'datetime',
                [
                'default' => null,
                'limit' => null,
                'null' => true,
                ]
            )
            ->addColumn(
                'created',
                'datetime',
                [
                'default' => null,
                'limit' => null,
                'null' => false,
                ]
            )
            ->addColumn(
                'modified',
                'datetime',
                [
                'default' => null,
                'limit' => null,
                'null' => true,
                ]
            )
            ->create();
    }

    /**
     * Down Method
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-down-method
     *
     * @return void
     */
    public function down()
    {
        $this->table('email_queue')->drop()->save();
    }
}
