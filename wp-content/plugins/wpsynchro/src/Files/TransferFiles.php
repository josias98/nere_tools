<?php

namespace WPSynchro\Files;

use WPSynchro\Migration\MigrationController;
use WPSynchro\Transport\Destination;
use WPSynchro\Transport\RemoteTransport;
use WPSynchro\Transport\RemoteTransportResult;
use WPSynchro\Utilities\Configuration\PluginConfiguration;
use WPSynchro\Utilities\SyncTimerList;

/**
 * Class for handling files transfer from source to target
 */
class TransferFiles
{
    // Data objects
    public $job = null;
    public $migration = null;
    public $sync_list = null;
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
     *  Initialize class
     */
    public function init(&$sync_list, &$migration, &$job)
    {
        $this->sync_list = $sync_list;
        $this->migration = $migration;
        $this->job = $job;
    }

    /**
     * Transfer the rest of the files in file list
     */
    public function transferFiles()
    {
        // Determine max size for file uploading
        $plugin_configuration = PluginConfiguration::factory();
        if ($plugin_configuration->getSlowHostingSetting()) {
            $max_transfer_chunk_size = 1048576;    // 1 mb
        } else {
            $max_transfer_chunk_size = 5 * 1048576;    // 5 mb
        }

        $max_file_size = min($this->job->to_max_post_size, $this->job->from_max_post_size) * 0.9;
        if ($max_file_size > $max_transfer_chunk_size) {
            $max_file_size = $max_transfer_chunk_size;
        }


        // Get data on files for transfer
        $filesync = $this->sync_list->getFilesToMoveToTarget($max_file_size);

        if (count($filesync) == 0) {
            // All done
            return;
        }

        // Determine if we need to pull the files or push them
        if ($this->migration->type == 'push') {
            $this->pushFiles($filesync, $max_file_size);
        } elseif ($this->migration->type == 'pull') {
            $this->pullFiles($filesync, $max_file_size);
        }
    }

    /**
     *  Push files to target
     */
    public function pushFiles(&$filesync, $max_file_size)
    {
        $destination = new Destination(Destination::TARGET);

        $url = $destination->getFullURL() . '?action=wpsynchro_file_push';

        // Get remote transfer object
        $remotetransport = new RemoteTransport();
        $remotetransport->setDestination($destination);
        $remotetransport->init();
        $remotetransport->setMaxRequestSize($max_file_size);
        $remotetransport->setUrl($url);

        $filesync_added = [];
        foreach ($filesync as $file) {
            $more_space = $remotetransport->addFiledata($file);
            $filesync_added[] = $file;
            // If it could not be added, probably due to hitting max size, break off
            if ($more_space === false) {
                break;
            }
        }
        $remotetransport->setDataObject($filesync_added);
        $push_files_result = $remotetransport->remotePOST();

        if ($push_files_result->isSuccess()) {
            $body = $push_files_result->getBody();
            $this->handleRemoteTransportResult($body->data);
        } else {
            $this->job->errors[] = sprintf(__("Failed during transfer of files to remote site, which means we can not continue the migration.", "wpsynchro"));
        }
    }

    /**
     *  Pull files to target
     */
    public function pullFiles(&$filesync, $max_file_size)
    {
        $body = new \stdClass();
        $body->files = $filesync;
        $body->max_file_size = $max_file_size;

        $destination = new Destination(Destination::SOURCE);

        $url = $destination->getFullURL() . '?action=wpsynchro_file_pull';

        // Get remote transfer object
        $remotetransport = new RemoteTransport();
        $remotetransport->setDestination($destination);
        $remotetransport->init();
        $remotetransport->setUrl($url);
        $remotetransport->setDataObject($body);

        $this->logger->log("DEBUG", "Calling remote service 'getfiles' with request for " . count($body->files) . " files and maxsize " . $body->max_file_size);
        foreach ($filesync as $file) {
            $this->logger->log("DEBUG", sprintf("Requesting file %s", $file->filename));
        }

        $pullfiles_result = $remotetransport->remotePOST();

        if ($pullfiles_result->isSuccess()) {
            $files = $pullfiles_result->getFiles();
            $data = $pullfiles_result->getBody();
            $this->logger->log("DEBUG", "Got a proper response from 'getfiles' with " . count($files) . " files");

            // Handle the files and filedata, writing it to disk as needed
            $transporthandler = new TransportHandler();
            $result = $transporthandler->handleFileTransport($data, $files);

            // Use remotetransferresult to log errors, warning etc.
            $remotetransferresult = new RemoteTransportResult();
            $remotetransferresult->errors = $result->errors;
            $this->job->errors = array_merge($this->job->errors, $result->errors);
            $remotetransferresult->warnings = $result->warnings;
            $this->job->warnings = array_merge($this->job->warnings, $result->warnings);
            $remotetransferresult->debugs = $result->debugs;
            $remotetransferresult->writeMessagesToLog();

            $this->handleRemoteTransportResult($result->data);
        } else {
            $this->job->errors[] = __("Failed during fetching files from remote site, which means we can not continue the migration.", "wpsynchro");
        }
    }

    /**
     *  Handle setting file key to completed or partially completed
     */
    public function handleRemoteTransportResult($data)
    {

        foreach ($data as $key => $single_result) {
            $single_result = (array) $single_result;

            if ($single_result['success'] == true) {
                if (isset($single_result['partial'])) {
                    // Handle partial
                    $this->sync_list->setFileKeyToCompleted($key, $single_result['size'], true, $single_result['partial_position'], $single_result['last_partial_position']);
                } else {
                    $this->sync_list->setFileKeyToCompleted($key, $single_result['size']);
                }
            } else {
                // It failed, so just set it to completed
                $this->sync_list->setFileKeyToCompleted($key, $single_result['size']);
            }
        }
    }
}
