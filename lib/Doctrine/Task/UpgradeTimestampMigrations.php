<?php
/**
 * Upgrades the migration_version table to use timestamp-based migrations.
 *
 * Populates altered table with which migrations have been run.
 *
 */
class Doctrine_Task_UpgradeTimestampMigrations extends Doctrine_Task
{
    public $description          =   'Upgrade migrations to use timestamped-based migrations',
           $requiredArguments    =   array('migrations_path' => 'Specify path to your migrations directory.');

    public function execute() {
        $conn = Doctrine_Manager::connection();
        $migration = new Doctrine_Migration($this->getArgument('migrations_path'));
        $migClasses = $migration->getMigrationClasses();
        ksort($migClasses, SORT_NUMERIC);

        $version = $conn->fetchOne("SELECT version FROM " .$migration->getTableName() . " WHERE version IS NOT NULL");

        $this->notify("Current Version: $version");
        $conn->export->alterTable($migration->getTableName(), array(
            'add' => array(
                'timestamp_value' => array(
                    'type' => 'integer'
                ),
                'class_name' => array(
                    'type' => 'string',
                    'length' => 255
                )
            )
        ));

        // Seed table with migrations that should have been run based on current version number
        $i = 0;
        foreach ($migClasses as $timestamp => $class_name) {
            $i++;
            if ($i > $version) {
                break;
            }
            $conn->exec("INSERT INTO " . $migration->getTableName() . " (timestamp_value, class_name) VALUES ({$timestamp}, '{$class_name}')");
        }

        // Remove old version row and column
        $conn->exec("DELETE FROM " .$migration->getTableName(). " WHERE version IS NOT NULL");
        $conn->export->alterTable($migration->getTableName(), array(
            'remove' => array('version' => array())
        ));
        $this->notify("Upgrade Complete");
    }
}
