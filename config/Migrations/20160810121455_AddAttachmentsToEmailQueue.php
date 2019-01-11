<?php
use Migrations\AbstractMigration;

class AddAttachmentsToEmailQueue extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('email_queue');
        $table->addColumn(
            'attachments',
            'text',
            [
            'default' => null,
            'null' => true,
            ]
        );
        $table->update();
    }
}
