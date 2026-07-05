<?php

namespace WPSynchro\Files;

/**
 * Class for handling a file section, which is a specific location to migrate as chosen in migration configuration
 */
class Section
{
    // Base data
    public $id;
    public $name = '';
    public $type = 'clean';
    public $is_file = false;
    public $exclusions = "";
    public $strategy = "";
    public $temp_locations_in_basepath = "";
    public $source_basepath = "";
    public $target_basepath = "";
    // Population
    public $source_request_count = 0;
    public $source_is_remote_complete = false;
    public $source_files_population_complete = false;
    public $target_request_count = 0;
    public $target_is_remote_complete = false;
    public $target_files_population_complete = false;
    public $files_population_source_count = 0;
    public $files_population_target_count = 0;
    // Path handling
    public $source_id_last = -1;
    public $source_id_start = -1;
    public $source_id_end = -1;
    public $target_id_last = -1;
    public $target_id_start = -1;
    public $target_id_end = -1;
    public $files_path_handler_transfers = false;
    public $files_path_handled_deletes = false;
    public $files_path_handled = false;
    // Finalize
    public $finalize_path_reduced = false;

    public function __construct()
    {
        $this->id = uniqid();
    }
}
