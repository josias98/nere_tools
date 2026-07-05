<?php

/**
 * Handle scheduled migrations
 */

namespace WPSynchro\Schedule;

use WPSynchro\Logger\FileLogger;
use WPSynchro\Migration\Migration;
use WPSynchro\Utilities\CommonFunctions;
use WPSynchro\Utilities\SingletonTrait;

class ScheduleFactory
{
    use SingletonTrait;

    private $scheduled_tasks = [];

    public const SCHEDULED_OPTIONS_KEY = 'wpsynchro_scheduled_migrations';
    public const CRON_LOCK_OPTIONS_KEY = 'wpsynchro_scheduled_migration_cron_lock';
    public const WP_CRON_HOOK = 'wpsynchro_cron_scheduled_migrations';

    /**
     * Setup wp cron scheduler
     */
    public function setupCron()
    {
        add_action(self::WP_CRON_HOOK, function () {
            (ScheduleFactory::getInstance())->runScheduler();
        }, 10, 0);

        if (!wp_next_scheduled(self::WP_CRON_HOOK)) {
            wp_schedule_single_event(time() + 60, self::WP_CRON_HOOK);
        }
    }

    /**
     * Trigger scheduler from cron and check if we should run any jobs
     */
    public function runScheduler()
    {
        $logger = new FileLogger((new CommonFunctions())->getCronLogFilename());
        $logger->log('INFO', 'Starting WP Synchro cron');
        $common_functions = new CommonFunctions();

        // If no scheduled migration, do nothing
        $scheduled_migrations = $this->getScheduledMigrations();
        if (empty($scheduled_migrations)) {
            $logger->log('INFO', 'No scheduled migration - Aborting');
            return;
        }

        // Check cron lock, so make sure nothing else is running
        if (!$common_functions->isSafeToStartNewMigration()) {
            $logger->log('INFO', 'A migration is already running on this site - Aborting');
            return;
        }

        // Run through migrations to see if we should run any
        foreach ($scheduled_migrations as $scheduled_migration) {
            if ($scheduled_migration->shouldRun()) {
                $logger->log('INFO', 'Migration with id ' . $scheduled_migration->id . ' should run and will be started', $scheduled_migration);
                $scheduled_migration->triggerStartRequest();
                return; // We only trigger once per run
            }
        }

        $logger->log('INFO', 'No migrations are scheduled to run');
    }

    /**
     * Add scheduled migration
     */
    public function addScheduledMigration(ScheduledMigration $scheduled_migration): bool
    {
        $scheduled_migrations = $this->getScheduledMigrations();
        $scheduled_migrations[] = $scheduled_migration;
        return $this->saveScheduledMigrations($scheduled_migrations);
    }

    /**
     * Remove scheduled migration
     */
    public function removeScheduledMigration(string $scheduled_migration_id)
    {
        $scheduled_migrations = $this->getScheduledMigrations();
        $scheduled_migrations  = array_filter(
            $scheduled_migrations,
            function (ScheduledMigration $scheduled_migration) use ($scheduled_migration_id) {
                if ($scheduled_migration->id == $scheduled_migration_id) {
                    return false;
                }
                return true;
            }
        );
        $this->saveScheduledMigrations($scheduled_migrations);
    }

    /**
     * Get list of schedule migrations
     * @return ScheduledMigration[] Array of scheduled migrations
     */
    public function getScheduledMigrations()
    {
        $scheduled_migrations = get_option(self::SCHEDULED_OPTIONS_KEY);
        if ($scheduled_migrations === false || !is_array($scheduled_migrations)) {
            return [];
        }

        // Convert to objects
        $migrations = [];
        foreach ($scheduled_migrations as $scheduled_migration) {
            $sm = new ScheduledMigration();
            $array_keys = array_keys($scheduled_migration);
            foreach ($array_keys as $key) {
                if (\property_exists($sm, $key)) {
                    $sm->$key = $scheduled_migration[$key] ?? $sm->$key;
                }
            }

            $migrations[] = $sm;
        }

        return $migrations;
    }

    /**
     * Save scheduled migrations
     */
    public function saveScheduledMigrations(array $scheduled_migrations): bool
    {
        $migrations = [];
        foreach ($scheduled_migrations as $scheduled_migration) {
            $migrations[] = (array) $scheduled_migration;
        }

        update_option(self::SCHEDULED_OPTIONS_KEY, $migrations, false);
        return true;
    }

    /**
     * Run scheduled migration triggered by request
     */
    public function runScheduledMigration(string $scheduled_migration_id, string $key)
    {
        $scheduled_migrations = $this->getScheduledMigrations();
        foreach ($scheduled_migrations as $scheduled_migration) {
            if ($scheduled_migration->id == $scheduled_migration_id) {
                if ($scheduled_migration->checkKey($key)) {
                    $scheduled_migration->run();
                }
            }
        }
    }

    /**
     * Update scheduled migration
     */
    public function updateScheduledMigration(ScheduledMigration $updated_scheduled_migration)
    {
        $scheduled_migrations = $this->getScheduledMigrations();
        foreach ($scheduled_migrations as &$scheduled_migration) {
            if ($scheduled_migration->id == $updated_scheduled_migration->id) {
                $scheduled_migration = $updated_scheduled_migration;
            }
        }
        $this->saveScheduledMigrations($scheduled_migrations);
    }

    /**
     * Synchronize schedule migrations with migrations
     * Add/update/delete the scheduled migrations
     * @param Migration[] $migrations
     */
    public function synchronizeWithMigrations(array $migrations): void
    {
        $scheduled_migrations = $this->getScheduledMigrations();
        $scheduled_migration_seen = [];

        // Check for any we need to add
        foreach ($migrations as $migration) {
            if (!empty($migration->schedule_interval)) {
                // Check if it exists
                $found = false;
                foreach ($scheduled_migrations as $scheduled_migration) {
                    if ($scheduled_migration->migration_id == $migration->id) {
                        // It exist, so we update
                        $scheduled_migration->cron_schedule = $migration->schedule_interval;
                        $this->updateScheduledMigration($scheduled_migration);
                        $scheduled_migration_seen[$scheduled_migration->id] = true;
                        $found = true;
                    }
                }
                if (!$found) {
                    // It doesnt exist, so we create it
                    $scheduled_migration = new ScheduledMigration();
                    $scheduled_migration->migration_id = $migration->id;
                    $scheduled_migration->cron_schedule = $migration->schedule_interval;
                    $this->addScheduledMigration($scheduled_migration);
                    $scheduled_migration_seen[$scheduled_migration->id] = true;
                }
            }
        }

        // Check if we need to delete any
        foreach ($scheduled_migrations as $scheduled_migration) {
            if (!isset($scheduled_migration_seen[$scheduled_migration->id])) {
                // Need to delete
                $this->removeScheduledMigration($scheduled_migration->id);
            }
        }
    }
}
