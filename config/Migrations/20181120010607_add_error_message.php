<?php

use Phinx\Migration\AbstractMigration;

class AddErrorMessage extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('email_queue');
        $table->addColumn('error', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
