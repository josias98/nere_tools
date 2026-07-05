<?php

namespace WPSynchro\Files;

use WPSynchro\Migration\MigrationController;
use WPSynchro\Transport\Destination;
use WPSynchro\Transport\RemoteTransport;
use WPSynchro\Utilities\SyncTimerList;

/**
 * Class for handling files finalize
 */
class FinalizeFiles
{
    // Data objects
    public $job = null;
    public $migration = null;
    public $sync_list = null;
    public $target_token = null;
    // Dependencies
    public $timer = null;
    public $logger = null;
    /**
     *  Constructor
     */
    public function __construct()
    {
        $this->logger = MigrationController::getInstance()->getLogger();
        $this->timer = SyncTimerList::getInstance();
    }

    /**
     *  Initialize class
     */
    public function init(&$sync_list, &$migration, &$job)
    {

        $this->sync_list = $sync_list;
        $this->migration = $migration;
        $this->job = $job;
    }

    /**
     * Clean up files on target
     */
    public function finalizeFiles()
    {
        $this->logger->log("INFO", "Starting file finalize with remaining time: " . $this->timer->getRemainingSyncTime());
        // Reduce the paths to delete
        if (!$this->job->finalize_files_paths_reduced) {
            // Set progress description
            $this->job->finalize_progress_description = sprintf(__("Optimizing files to delete: %d", "wpsynchro"), $this->sync_list->getRemainingFileDeletes());
            // Reduce paths
            $this->reduceFileDeletionPaths();
            return;
        }

        // Execute the deletes
        $this->job->finalize_progress_description = sprintf(__("Remaining files to delete: %d", "wpsynchro"), $this->sync_list->getRemainingFileDeletes());
        $this->handleFileDeletion();
    }

    /**
     * Attempt to reduce the paths to delete
     */
    public function reduceFileDeletionPaths()
    {

        global $wpdb;
        $this->logger->log("INFO", "Starting file finalize path reduction");
        $max_paths_to_reduce = 20;
        // Do reduce paths
        foreach ($this->job->files_sections as $section) {
            if (!$section->finalize_path_reduced) {
                if ($this->timer->shouldContinueWithLastrunTime(5)) {
                    $this->logger->log("DEBUG", "Starting file path reduction on section: " . $section->name);
                    // Fetch the shortest paths
                    $shortest_paths = $wpdb->get_col($wpdb->prepare("SELECT source_file FROM " . $wpdb->prefix . "wpsynchro_sync_list WHERE section=%s and needs_delete=1 order by length(source_file) asc limit %d", $section->id, $max_paths_to_reduce));
                    if ($shortest_paths && count($shortest_paths) > 0) {
                            // Run through all paths and remove those that start with those in the db
                        foreach ($shortest_paths as $shortpath) {
                            $this->logger->log("DEBUG", "Remove files that need to be deleted that starts with " . $shortpath);
                            $wpdb->query($wpdb->prepare("update " . $wpdb->prefix . "wpsynchro_sync_list set needs_delete=0 where section=%s and source_file like %s", $section->id, ($shortpath . "/%")));
                        }
                    }

                    $this->logger->log("DEBUG", "Completed file path reduction on section: " . $section->name);
                    $section->finalize_path_reduced = true;
                } else {
                    $this->logger->log("INFO", "Aborting file finalize path reduction due to remaining time: " . $this->timer->getRemainingSyncTime());
                    return;
                }
            }
        }

        $this->logger->log("INFO", "Completed file finalize path reduction with remaining time: " . $this->timer->getRemainingSyncTime());
        $this->job->finalize_files_paths_reduced = true;
    }

    /**
     * Call file finalize service on target
     */
    public function handleFileDeletion()
    {
        // Fetch a chunk of files to delete on target
        $limit = 100;
        $filelist = $this->sync_list->getFilesChunkForDeletion($limit);
        if (count($filelist) == 0) {
            $this->job->finalize_files_completed = true;
            return;
        }

        // Call service with delete list
        $returned_file_list = $this->callFileFinalizeService($filelist);
        // Set files to deleted
        if ($returned_file_list) {
            $this->sync_list->setFilesToDeleted($returned_file_list);
        }
    }

    /**
     * Call file finalize service on target
     */
    public function callFileFinalizeService($deletes)
    {
        // Now we have all the work needed, so call the finalize service on the target
        $body = new \stdClass();
        $body->allotted_time = $this->timer->getRemainingSyncTime();
        $body->delete = $deletes;
        // Get remote transfer object
        $destination = new Destination(Destination::TARGET);
        $url = $destination->getFullURL() . '?action=wpsynchro_file_finalize';
        $remotetransport = new RemoteTransport();
        $remotetransport->setDestination($destination);
        $remotetransport->init();
        $remotetransport->setUrl($url);
        $remotetransport->setDataObject($body);
        $finalize_result = $remotetransport->remotePOST();
        if ($finalize_result->isSuccess()) {
            $body = $finalize_result->getBody();
            return $body->delete;
        } else {
            $this->job->errors[] = __("Failed during finalizing files, which means we can not continue the migration.", "wpsynchro");
        }
    }
}
