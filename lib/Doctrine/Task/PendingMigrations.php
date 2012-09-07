<?php
class Doctrine_Task_PendingMigrations extends Doctrine_Task
{
    public $description          =   'Displays all migrations available on filesystem that have not been applied to the database',
           $requiredArguments    =   array('migrations_path' => 'Specify path to your migrations directory.');
    
    public function execute()
    {
        $conn = Doctrine_Manager::connection();
        $migration = new Doctrine_Migration($this->getArgument('migrations_path'));

        $migrationFiles = $migration->getMigrationClasses();
        $executedMigrations = $conn->fetchColumn("SELECT timestamp_value FROM " . $migration->getTableName() . " ORDER BY timestamp_value ASC");
        $pendingMigrations = array_diff(array_keys($migrationFiles), $executedMigrations);
        if (count($pendingMigrations) > 0) {
            $notify = "Pending migrations:\n------------------";
            foreach ($pendingMigrations as $pendingMigration) {
                $notify .= "\n$pendingMigration -";
                $files = glob(rtrim($this->getArgument('migrations_path'), '/')."/{$pendingMigration}_*");
                foreach ($files as $file) {
                    $notify .= " ". basename($file);
                }
                $notify .= " - {$migrationFiles[$pendingMigration]}";
            }
            $this->notify($notify);
        }
        else {
            $this->notify("No pending migrations.");
        }
    }
}
