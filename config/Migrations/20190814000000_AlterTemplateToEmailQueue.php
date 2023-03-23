<?php
use Migrations\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AlterTemplateToEmailQueue extends AbstractMigration
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
        $this->table('email_queue')
            ->changeColumn('template', 'string', [
                'limit' => 100,
            ])
            ->update();
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
        $this->table('email_queue')
            ->changeColumn('template', 'string', [
                'limit' => 50,
            ])
            ->update();
    }
}
