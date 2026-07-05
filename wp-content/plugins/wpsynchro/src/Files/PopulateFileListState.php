<?php

namespace WPSynchro\Files;

/**
 * Simple data class for file population result
 *
 */
class PopulateFileListState
{
    public $job_id = "";
    public $section_id = "";
    public $in_progress = false;
    public $state = "start";
    public $files_found = 0;
    public $current_dir_state = null;

    public function __construct()
    {
        $this->current_dir_state = new \stdClass();
        $this->resetCurrentDirState();
    }

    public function resetCurrentDirState()
    {
        $this->current_dir_state->id = 0;
        $this->current_dir_state->current_path = "";
    }
}
