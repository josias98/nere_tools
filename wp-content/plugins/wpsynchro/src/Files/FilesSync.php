<?php

/**
 * Class for handling files migration
 */

namespace WPSynchro\Files;

use WPSynchro\Files\SyncList;
use WPSynchro\Migration\MigrationController;
use WPSynchro\Utilities\SyncTimerList;

class FilesSync
{
    // Data objects
    public $job = null;
    public $migration = null;
    // Specific task classes
    public $sync_list = null;
    public $populate_list_handler = null;
    public $path_handler = null;
    public $transfer_files_handler = null;
    // Dependencies
    public $logger = null;
    public $timer = null;

    /**
     *  Constructor
     */
    public function __construct()
    {
        $this->logger = MigrationController::getInstance()->getLogger();
        $this->timer = SyncTimerList::getInstance();
    }

    /**
     * Initialize needed objects
     */
    public function init()
    {
        // File sync list object
        $this->sync_list = new SyncList();
        $this->sync_list->init($this->migration, $this->job);

        // File populate list handler
        $this->populate_list_handler = new PopulateListHandler();
        $this->populate_list_handler->init($this->sync_list, $this->migration, $this->job);

        // Path handler object
        $this->path_handler = new PathHandler();
        $this->path_handler->init($this->sync_list, $this->job);

        // TransferFiles object
        $this->transfer_files_handler = new TransferFiles();
        $this->transfer_files_handler->init($this->sync_list, $this->migration, $this->job);
    }

    /**
     * Handle files step
     */
    public function runFilesSync(&$migration, &$job)
    {
        $this->migration = $migration;
        $this->job = $job;

        // Init
        $this->init();

        // If not locations, just return
        if (count($this->job->files_sections) == 0) {
            $this->job->files_all_completed = true;
            return;
        }

        // Now, do some work
        $lastrun_time = 0;
        while ($this->timer->shouldContinueWithLastrunTime($lastrun_time)) {
            $lastrun_timer = $this->timer->startTimer("filessync", "while", "lastrun");
            $this->logger->log("INFO", "Running files sync loop - With remaining time: " . $this->timer->getRemainingSyncTime() . " and last run time: " . $lastrun_time);

            // If there is errors, break out
            if (count($this->job->errors) > 0) {
                break;
            }

            // If run requires full time frame
            if ($this->job->request_full_timeframe) {
                $this->logger->log("DEBUG", "Breaking out of file sync and requesting full timeframe");
                break;
            }

            // Calculate progress
            $this->calculateProgress();

            // Start processing
            if (!$this->job->files_all_sections_populated) {
                // Populated from source (list files)
                $this->setFilesProgressDescription(__("Indexing files (can take a while)", "wpsynchro") . " - " . $this->sync_list->getFileProgressDescriptionPart());
                $this->logger->log("INFO", "Files: Gather file data from source and target - " . $this->sync_list->getFileProgressDescriptionPart());
                $this->populate_list_handler->populateFilelist();
            } elseif (!$this->job->files_all_sections_path_handled) {
                // Determine files to transfer and delete
                $this->setFilesProgressDescription(__("Determine files to delete and transfer", "wpsynchro"));
                $this->logger->log("INFO", "Files: Determine files to delete/transfer");
                $this->path_handler->processFilelist();
            } elseif (!$this->job->files_user_confirmed_actions) {
                // Check if we are waiting
                if ($this->job->files_ready_for_user_confirm) {
                    // Just stall a bit and break out
                    $this->logger->log("DEBUG", "Files: Waiting for user confirm");
                    sleep(1);
                    break;
                }
                // Check if we need user to confirm action, but only if any files should be transferred/deleted
                if ($this->migration->files_ask_user_for_confirm && ($this->job->files_needs_transfer > 0 || $this->job->files_needs_delete > 0)) {
                    $this->setFilesProgressDescription(__("Waiting for confirmation on file changes", "wpsynchro"));
                    $this->logger->log("INFO", "Files: Confirmation of file changes executing");
                    $this->job->files_ready_for_user_confirm = true;
                } else {
                    $this->logger->log("INFO", "Files: Confirmation of file changes not enabled or not needed, so skipping");
                    $this->job->files_user_confirmed_actions = true;
                }
            } elseif (!$this->job->files_all_completed) {
                // Transfer the needed files - Copy from source to target
                $this->setFilesProgressDescription(__("Transferring files", "wpsynchro") . " - " . $this->sync_list->getFileProgressDescriptionPart());
                $this->logger->log("INFO", "Files: Transferring files - " . $this->sync_list->getFileProgressDescriptionPart());
                $this->transfer_files_handler->transferFiles();
            } else {
                $this->setFilesProgressDescription("");
                // Nothing more to do
                break;
            }

            // Make sure we dont pass the max allotted time
            $lastrun_time = $this->timer->getElapsedTimeToNow($lastrun_timer);

            // Update state
            $this->sync_list->updateSectionState();

            // Calculate progress
            $this->calculateProgress();

            // Save status to DB
            $this->job->save();
        }
    }

    /**
     *  Set a new progress description and save if neeeded
     */
    public function setFilesProgressDescription($desc)
    {
        if ($this->job->files_progress_description != $desc) {
            $this->job->files_progress_description = $desc;
            $this->job->save();
        }
    }

    /**
     * Calculate progress
     */
    public function calculateProgress()
    {
        // We start at 5, to show we are started
        $progress_number = 5;

        /**
         *  File population
         */
        $progress_per_section_part = 65 / (count($this->job->files_sections) * 2);
        foreach ($this->job->files_sections as &$section) {
            if ($section->source_files_population_complete) {
                $progress_number += $progress_per_section_part;
            }
            if ($section->target_files_population_complete) {
                $progress_number += $progress_per_section_part;
            }
        }
        $progress_number = ceil($progress_number);

        /**
         * When population is done, we need to transfer the needed files
         */
        $total_size = $this->job->files_needs_transfer_size;
        $size_completed = $this->job->files_transfer_completed_size;
        if ($total_size > 0) {
            $service_progress = 100 - $progress_number;
            if ($size_completed > 0) {
                $hash_percent_completed = $size_completed / $total_size;
                if ($hash_percent_completed == 1) {
                    $progress_number = 100;
                } else {
                    $progress_number += floor($service_progress * $hash_percent_completed);
                }
            }
        }

        $this->job->files_progress = $progress_number;

        if ($this->job->files_progress >= 100) {
            $this->job->files_progress = 100;
            $this->job->files_progress_description = "";
            $this->job->files_all_completed = true;
        }
    }
}
