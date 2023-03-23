<?php

use Migrations\AbstractMigration;

class AddCcAndBccToEmailQueue extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('email_queue');
        $table->addColumn('cc', 'string', [
            'default' => null,
            'limit' => 129,
            'null' => true,
            'after' => 'email'
        ]);
        $table->addColumn('bcc', 'string', [
            'default' => null,
            'limit' => 129,
            'null' => true,
            'after' => 'cc'
        ]);
        $table->addIndex([
            'cc',
        ], [
            'name' => 'BY_CC',
            'unique' => false,
        ]);
        $table->addIndex([
            'bcc',
        ], [
            'name' => 'BY_BCC',
            'unique' => false,
        ]);
        $table->update();
    }
}
