<?php
class Doctrine_Task_MigrateUp extends Doctrine_Task
{
    public $description          =   'Execute the up migration method of a particular version',
           $requiredArguments    =   array('migrations_path' => 'Specify path to your migrations directory.'),
           $optionalArguments    =   array('timestamp' => 'Timestamp of migration that you want to migrate up.');
    
    public function execute()
    {
        $migration = new Doctrine_Migration($this->getArgument('migrations_path'));
        $migration->migrateUp($this->getArgument('timestamp'));
        $this->notify('migrated successfully');
    }
}
