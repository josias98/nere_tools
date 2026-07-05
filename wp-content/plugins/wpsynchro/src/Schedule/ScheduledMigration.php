<?php

/**
 * Scheduled migration
 */

namespace WPSynchro\Schedule;

use WPSynchro\Logger\FileLogger;
use WPSynchro\Migration\MigrationController;
use WPSynchro\Migration\MigrationFactory;
use WPSynchro\Utilities\CommonFunctions;

class ScheduledMigration
{
    public $id = '';
    public $migration_id = '';
    public $cron_schedule = '';

    // State variables
    public $job_id = '';
    public $last_run_time = 0;
    public $last_completion_time = 0;

    // Instance references
    private $logger;

    // Decorated variables, used for presentation
    public $last_run_time_formatted = '';
    public $last_completion_time_formatted = '';
    public $next_approx_run = '';
    public $schedule_text = '';
    public $migration_name = '';
    public $migration_add_edit_url = '';

    // Constants
    public const BASIC_CRON_HOURLY = 'hourly';
    public const BASIC_CRON_DAILY = 'daily';
    public const BASIC_CRON_WEEKLY = 'weekly';

    public function __construct()
    {
        $this->id = sha1(uniqid());
        $this->logger = new FileLogger((new CommonFunctions())->getCronLogFilename());
    }

    public function shouldRun(): bool
    {
        // Check if we are running already
        if ($this->last_run_time + 35 > time()) {
            return false;
        }

        // Check if schedule tell we should run
        $seconds_between_run = $this->getScheduleSecondsTillNextRun();
        if ($seconds_between_run == -1) {
            return false;
        }
        if (($this->last_run_time + $seconds_between_run) < time()) {
            return true;
        }


        return false;
    }

    private function getScheduleSecondsTillNextRun(): int
    {
        if ($this->cron_schedule == self::BASIC_CRON_HOURLY) {
            return 3600;
        }
        if ($this->cron_schedule == self::BASIC_CRON_DAILY) {
            return 86400;
        }
        if ($this->cron_schedule == self::BASIC_CRON_WEEKLY) {
            return 604800;
        }
        return -1;
    }

    public function run(): void
    {
        $run_uniqid_id_str = '[' . uniqid() . '] ';

        $this->logger->log('INFO', $run_uniqid_id_str . 'Starting scheduled migration with id: ' . $this->id);
        $schedule_factory = ScheduleFactory::getInstance();

        $this->last_run_time = time();
        $schedule_factory->updateScheduledMigration($this);

        // Check that migrations id exists
        $migrationFactory = MigrationFactory::getInstance();
        $migration = $migrationFactory->retrieveMigration($this->migration_id);
        if (!$migration) {
            $this->logger->log('INFO', $run_uniqid_id_str . 'Attempting to start migration, but could not find the migration with id: ' . $this->id);
            return;
        }

        // Check if it is a new job
        if (empty($this->job_id)) {
            $this->job_id = uniqid();
            $this->logger->log('INFO', $run_uniqid_id_str . 'Fresh migration with new job id: ' . $this->job_id);
        }

        $sync = MigrationController::getInstance();
        $sync->setup($migration->id, $this->job_id);
        $sync->migration->setAsynCronMode();
        $result = $sync->runMigration();

        if ($result->is_completed || !empty($result->errors)) {
            if ($result->is_completed) {
                $this->logger->log('INFO', $run_uniqid_id_str . 'Scheduled migration is completed');
            }
            if (!empty($result->errors)) {
                $this->logger->log('ERROR', $run_uniqid_id_str . 'Scheduled migration had errors:', $result->errors);
            }
            $this->job_id = '';
            $this->last_completion_time = time();
            $schedule_factory->updateScheduledMigration($this);
            return;
        }

        $this->last_run_time = time();
        $schedule_factory->updateScheduledMigration($this);

        sleep(1);
        $this->logger->log('INFO', $run_uniqid_id_str . 'Trigger new request to continue migration');
        $this->triggerStartRequest();
    }

    public function checkKey(string $key): bool
    {
        if ($this->getKey() == $key) {
            return true;
        }
        return false;
    }

    /**
     * Initiate a request to the site itself, that starts/continue the migration process
     */
    public function triggerStartRequest(): void
    {
        $run_scheduled_migration = add_query_arg(
            [
                'action' => 'wpsynchro_scheduled_migration_run',
                'smi' => $this->id,
                'key' => $this->getKey()
            ],
            get_home_url()
        );

        // Do request
        $args = [
            'blocking' => false,
            'timeout' => 0,
        ];

        wp_remote_get($run_scheduled_migration, $args);
    }

    /**
     * Get key used to trigger migration,
     */
    private function getKey(): string
    {
        return sha1($this->id . $this->migration_id . get_home_url());
    }

    /**
     * Decorate object for presentation
     */
    public function decorateForPresentation(): void
    {
        // Format date time according to wp settings
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $timezone_string = wp_timezone_string();

        $dt = new \DateTime();
        $dt->setTimezone(new \DateTimeZone($timezone_string));
        if ($this->last_run_time == 0) {
            $this->last_run_time_formatted = __('None yet', 'wpsynchro');
            $this->next_approx_run = __('As soon as possible', 'wpsynchro');
        } else {
            $dt->setTimestamp($this->last_run_time);
            $this->last_run_time_formatted = $dt->format($date_format . ' ' . $time_format);

            $seconds_between_seconds = $this->getScheduleSecondsTillNextRun();
            if ($seconds_between_seconds == -1) {
                $this->next_approx_run = 'N/A';
            } else {
                $dt->setTimestamp($this->last_run_time + $seconds_between_seconds);
            }
            $this->next_approx_run = $dt->format($date_format . ' ' . $time_format);
        }

        if ($this->last_completion_time == 0) {
            $this->last_completion_time_formatted = '';
        } else {
            $dt->setTimestamp($this->last_completion_time);
            $this->last_completion_time_formatted = $dt->format($date_format . ' ' . $time_format);
        }

        $this->schedule_text = $this->getPrettySchedule($this->cron_schedule);

        $this->migration_add_edit_url = admin_url('admin.php?page=wpsynchro_addedit') . '&migration_id=' . $this->migration_id;

        $migration_factory = MigrationFactory::getInstance();
        $migration = $migration_factory->retrieveMigration($this->migration_id);
        if ($migration === false) {
            $this->migration_name = "";
        } else {
            $this->migration_name = $migration->name;
        }
    }

    /**
     * Get pretty text of schedule
     */
    public function getPrettySchedule(string $cron_schedule)
    {
        if ($cron_schedule == self::BASIC_CRON_HOURLY) {
            return __('Hourly', 'wpsynchro');
        }
        if ($cron_schedule == self::BASIC_CRON_DAILY) {
            return __('Daily', 'wpsynchro');
        }
        if ($cron_schedule == self::BASIC_CRON_WEEKLY) {
            return __('Weekly', 'wpsynchro');
        }

        return '';
    }
}
