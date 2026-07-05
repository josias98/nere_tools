<?php

namespace WPSynchro\Files;

use WPSynchro\Utilities\CommonFunctions;
use WPSynchro\Utilities\DatabaseTables;
use WPSynchro\Files\Section;
use WPSynchro\Migration\MigrationController;
use WPSynchro\Utilities\SyncTimerList;

/**
 * Class for handling the file sync list used to migrate files
 */
class SyncList
{
    // Base data
    public $job = null;
    public $migration = null;
    // Cache
    public $section_lookup = [];
    // Dependencies
    private $logger;

    /**
     *  Constructor
     */
    public function __construct()
    {
        $this->logger = MigrationController::getInstance()->getLogger();
    }

    /**
     *  Initialize class
     */
    public function init(&$migration, &$job)
    {
        $this->migration = &$migration;
        $this->job = $job;

        if (!$this->job->files_sync_list_initialized) {
            // Setup initial data
            $this->setupInitialFileStructure();
            // Make sure table for sync list exist and is truncated
            $database_tables = new DatabaseTables();
            if (!$database_tables->createSyncListTable()) {
                $this->job->errors[] = __("Could not create table needed for file population on this site - This is normally because database user does not have access to create tables on the database.", "wpsynchro");
            }
            // Set it to initialized
            $this->job->files_sync_list_initialized = true;
            // Save sections
        }
    }

    /**
     *  Create section for sections array
     */
    public function createSection($type, $name, $is_file, $exclusions, $strategy, $temp_dirs = [], $source_basepath = "", $target_basepath = "")
    {
        $common = new CommonFunctions();

        $tmp_obj = new Section();
        // Section base data
        $tmp_obj->name = $name;
        $tmp_obj->type = $type;
        $tmp_obj->is_file = $is_file;
        $tmp_obj->exclusions = $exclusions;
        $tmp_obj->strategy = $strategy;
        $tmp_obj->temp_locations_in_basepath = $temp_dirs;
        $tmp_obj->source_basepath = trailingslashit($common->fixPath($source_basepath));
        $tmp_obj->target_basepath = trailingslashit($common->fixPath($target_basepath));
        // Population
        $tmp_obj->source_files_population_complete = false;
        $tmp_obj->target_files_population_complete = false;
        $tmp_obj->files_population_source_count = 0;          // Count of files found on source to this point (can increase)
        $tmp_obj->files_population_target_count = 0;          // Count of files found on target to this point (can increase)
        // Path handling
        $tmp_obj->source_id_last = -1;
        $tmp_obj->source_id_start = -1;
        $tmp_obj->source_id_end = -1;
        $tmp_obj->target_id_last = -1;
        $tmp_obj->target_id_start = -1;
        $tmp_obj->target_id_end = -1;
        $tmp_obj->files_path_handler_transfers = false;
        $tmp_obj->files_path_handled_deletes = false;
        $tmp_obj->files_path_handled = false;
        // Finalize
        $tmp_obj->finalize_path_reduced = false;

        return $tmp_obj;
    }

    /**
     *  Setup initial objects and data
     */
    public function setupInitialFileStructure()
    {
        if (!$this->job->files_sync_list_initialized) {
            // Preset: All or file_all
            if ($this->migration->sync_preset == 'all' || $this->migration->sync_preset == 'file_all') {
                $this->job->files_sections[] = $this->createSection("sync_preset_all_files", __("All files in webroot", "wpsynchro"), false, "", "clean", [], $this->job->from_files_home_dir, $this->job->to_files_home_dir);
            } else {
                if ($this->migration->sync_preset == 'none') {
                    // Add all the manually added file locations
                    $counter = 1;
                    foreach ($this->migration->file_locations as $filelocation) {
                        if ($filelocation->base == 'webroot') {
                            $source_basepath = $this->job->from_files_home_dir;
                            $target_basepath = $this->job->to_files_home_dir;
                        } elseif ($filelocation->base == 'outsidewebroot') {
                            $source_basepath = $this->job->from_files_above_webroot_dir;
                            $target_basepath = $this->job->to_files_above_webroot_dir;
                        } elseif ($filelocation->base == 'wpcontent') {
                            $source_basepath = $this->job->from_files_wp_content_dir;
                            $target_basepath = $this->job->to_files_wp_content_dir;
                        }

                        $this->job->files_sections[] = $this->createSection("filelocation_" . $counter, __("Filelocation", "wpsynchro") . "_" . $counter, $filelocation->is_file, $filelocation->exclusions, $filelocation->strategy, [$filelocation->path => true], $source_basepath, $target_basepath);
                        $counter++;
                    }
                }
            }

            $this->logger->log("DEBUG", "Section list after init:", $this->job->files_sections);
        }
    }

    /**
     *  Add file list from population
     */
    public function addUpdateFilelistFromPopulation($type, $section_id, &$filelist)
    {
        global $wpdb;
        // Start timer
        $timer = SyncTimerList::getInstance();
        $run_timer = $timer->startTimer("synclist", "addupdatefilelistfrompopulation", "lastrun");
        $allotted_time = $timer->getRemainingSyncTime();

        $insert_query_part = "INSERT INTO " . $wpdb->prefix . "wpsynchro_sync_list (origin,section, source_file, is_dir, size, hash) VALUES ";
        $insert_value_part_arr = [];
        $insert_counter = 0;
        $insert_total_counter = 0;

        foreach ($filelist as $file) {
            $insert_counter++;
            $insert_total_counter++;
            if ($insert_counter > 995) {
                if ($timer->getElapsedTimeToNow($run_timer) >= $allotted_time) {
                    $this->logger->log("ERROR", sprintf("Could not complete file population sql inserts in time. Had %f seconds to complete. Got to %d out of %d", $allotted_time, $insert_total_counter, count($filelist)));
                    $this->job->errors[] = __("Could not complete inserting file list into database in time before PHP timeout. You should increse PHP max_execution_time to prevent this error. Or you can make a custom migration with only the needed files.", "wpsynchro");
                    return false;
                }

                $wpdb->query($insert_query_part . " " . implode(",", $insert_value_part_arr));
                $insert_counter = 0;
                $insert_value_part_arr = [];
            }

            // Add value part
            $insert_value_part_arr[] = $wpdb->prepare("(%s,%s,%s,%d,%d,%s)", $type, $section_id, $file->source_file, ($file->is_dir ? 1 : 0), $file->size, $file->hash);
        }
        if ($insert_counter > 0) {
            $wpdb->query($insert_query_part . " " . implode(",", $insert_value_part_arr));
        }

        // Set delete hash and transfer hash
        $this->setDeleteAndTransferHashInDB();

        $this->logger->log("INFO", sprintf("Inserted %d rows in database from file population. It took %f seconds.", $insert_total_counter, $timer->getElapsedTimeToNow($run_timer)));
        return true;
    }

    /**
     *  Update current state of this object
     */
    public function updateSectionState()
    {
        global $wpdb;

        /**
         *  Check if all is populated
         */
        if (!$this->job->files_all_sections_populated) {
            // Source count
            $source_count = 0;
            foreach ($this->job->files_sections as &$section) {
                $source_count += $section->files_population_source_count;
            }
            $this->job->files_population_source_count = $source_count;

            // Target count
            $target_count = 0;
            foreach ($this->job->files_sections as &$section) {
                $target_count += $section->files_population_target_count;
            }
            $this->job->files_population_target_count = $target_count;

            $this->job->files_all_sections_populated = true;
            foreach ($this->job->files_sections as &$section) {
                if (!$section->source_files_population_complete || !$section->target_files_population_complete) {
                    $this->job->files_all_sections_populated = false;
                    break;
                }
            }

            // If all sections are population, get some data (only runs once)
            if ($this->job->files_all_sections_populated) {
                // When all sections are populated, request full time frame (only runs once)
                $this->job->request_full_timeframe = true;

                foreach ($this->job->files_sections as &$section) {
                    if ($section->strategy == "clean") {
                        // Get id start/end to be used in determining deletes needed (only in clean mode)
                        $section->target_id_start = $wpdb->get_var("SELECT min(id) FROM `" . $wpdb->prefix . "wpsynchro_sync_list` WHERE origin='target' and section='" . $section->id . "'");
                        $section->target_id_end = $wpdb->get_var("SELECT max(id) FROM `" . $wpdb->prefix . "wpsynchro_sync_list` WHERE origin='target' and section='" . $section->id . "'");
                        $section->target_id_last = $section->target_id_start - 1;
                    }
                    // Get id start/end to be used in determining transfer needed
                    $section->source_id_start = $wpdb->get_var("SELECT min(id) FROM `" . $wpdb->prefix . "wpsynchro_sync_list` WHERE origin='source' and section='" . $section->id . "'");
                    $section->source_id_end = $wpdb->get_var("SELECT max(id) FROM `" . $wpdb->prefix . "wpsynchro_sync_list` WHERE origin='source' and section='" . $section->id . "'");
                    $section->source_id_last = $section->source_id_start - 1;
                }
            }

            return;
        }

        /**
         *  Check when path handling is running/done
         */
        if (!$this->job->files_all_sections_path_handled) {
            // Cycle through to see if section is completed
            foreach ($this->job->files_sections as &$section) {
                if ($section->strategy == "clean") {
                    if ($section->files_path_handler_transfers && $section->files_path_handled_deletes) {
                        $section->files_path_handled = true;
                    } else {
                        $section->files_path_handled = false;
                    }
                } elseif ($section->strategy == "keep") {
                    if ($section->files_path_handler_transfers) {
                        $section->files_path_handled = true;
                    } else {
                        $section->files_path_handled = false;
                    }
                }
            }

            // Check if all sections are done
            $this->job->files_all_sections_path_handled = true;
            foreach ($this->job->files_sections as &$section) {
                if (!$section->files_path_handled) {
                    $this->job->files_all_sections_path_handled = false;
                }
            }

            // When all sections are done
            if ($this->job->files_all_sections_path_handled) {
                // Set data when we are done
                $this->job->files_needs_transfer = $wpdb->get_var("select count(*) from " . $wpdb->prefix . "wpsynchro_sync_list where needs_transfer=1");
                $this->job->files_needs_transfer_size = $wpdb->get_var("select sum(size) from " . $wpdb->prefix . "wpsynchro_sync_list where needs_transfer=1");
                $this->job->files_needs_delete = $wpdb->get_var("select count(*) from " . $wpdb->prefix . "wpsynchro_sync_list where needs_delete=1");
                // Set debug data on
                $this->logger->log("INFO", sprintf("Marked %d files for transfer with size %d and %d files for deletion.", $this->job->files_needs_transfer, $this->job->files_needs_transfer_size, $this->job->files_needs_delete));
            }
            return;
        }

        /**
         *  Check for all files completed
         */
        if (!$this->job->files_all_completed) {
            $remaining_transfers = $wpdb->get_var("select count(*) from " . $wpdb->prefix . "wpsynchro_sync_list where needs_transfer=1");
            $this->job->files_transfer_completed_counter = $this->job->files_needs_transfer - $remaining_transfers;

            if ($this->job->files_transfer_completed_counter >= $this->job->files_needs_transfer) {
                $this->job->files_all_completed = true;
            }
            return;
        }
    }

    /**
     *  File complete sync: Get the next file that needs moving
     */
    public function getFilesToMoveToTarget($max_size)
    {
        $files = [];
        $file_size_counter = 0;

        // Get work from DB
        global $wpdb;
        $files_chunk = $wpdb->get_results(
            "SELECT `id`, `section`, `source_file`, `is_dir`, `size`, `hash`, `is_partial`, `partial_position`
            FROM `{$wpdb->prefix}wpsynchro_sync_list`
            WHERE `needs_transfer`=1 AND `source_file` NOT LIKE '%user.ini' AND `source_file` NOT LIKE '%.htaccess'
            ORDER BY `source_file`
            ASC LIMIT 100"
        );

        if (is_null($files_chunk) || $files_chunk == []) {
            // No more files, so check if any user.ini/.htaccess we need to transfer last
            $files_chunk = $wpdb->get_results(
                "SELECT `id`, `section`, `source_file`, `is_dir`, `size`, `hash`, `is_partial`, `partial_position`
                FROM `{$wpdb->prefix}wpsynchro_sync_list`
                WHERE `needs_transfer`=1 AND (`source_file` LIKE '%user.ini' OR `source_file` LIKE '%.htaccess')
                ORDER BY `source_file`
                ASC LIMIT 100"
            );
        }

        if ($files_chunk) {
            foreach ($files_chunk as $file) {
                $transferfile = new \WPSynchro\Transport\TransferFile();
                $transferfile->key = $file->id;
                $transferfile->is_dir = $file->is_dir;
                $transferfile->hash = $file->hash;
                $transferfile->is_partial = ($file->is_partial == 1 ? true : false);
                $transferfile->partial_start = $file->partial_position;
                $transferfile->size = $file->size;

                // Set target file
                $currentsection = $this->getSectionByID($file->section);
                $relativepath = ltrim($file->source_file, "/\\");
                $transferfile->filename = trailingslashit($currentsection->source_basepath) . $relativepath;
                $transferfile->target_file = trailingslashit($currentsection->target_basepath) . $relativepath;
                $file_size_counter += $transferfile->size;
                $files[$transferfile->key] = $transferfile;
                if ($file_size_counter >= $max_size) {
                    break;
                }
            }
        }

        return $files;
    }

    /**
     *  File complete sync: Set file key to completed and count up the completed file size
     */
    public function setFileKeyToCompleted($file_key, $file_size, $partial = false, $partial_position = 0, $last_partial_position = 0)
    {
        //error_log("Call completed with key:" . $file_key . " and size: " . $file_size . " and partial: " . ($partial ? "yes" : "no") . " and partialposition: " . $partial_position . " and last partialposition: " . $last_partial_position);

        $needs_transfer = 1;
        $is_partial = ($partial ? 1 : 0);

        if ($partial) {
            $bytes_processed = $partial_position - $last_partial_position;
            $this->job->files_transfer_completed_size += $bytes_processed;

            if ($partial_position >= $file_size) {
                // We are done
                $needs_transfer = 0;
            }
        } else {
            $needs_transfer = 0;
            $this->job->files_transfer_completed_size += $file_size;
        }

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . "wpsynchro_sync_list",
            [
                'is_partial' => $is_partial,
                'partial_position' => $partial_position,
                'needs_transfer' => $needs_transfer
            ],
            ['id' => $file_key],
            [
                '%d',
                '%d',
                '%d'
            ],
            ['%d']
        );
    }

    /**
     *  Get chunk of files that needs to be deleted
     */
    public function getFilesChunkForDeletion($max_files = 100)
    {
        $files = [];

        // Get work from DB
        global $wpdb;
        $files_chunk = $wpdb->get_results("select id, section, source_file from " . $wpdb->prefix . "wpsynchro_sync_list where needs_delete=1 limit " . $max_files);

        if ($files_chunk) {
            foreach ($files_chunk as $file) {
                $currentsection = $this->getSectionByID($file->section);
                $newfile = new \stdClass();
                $newfile->target_file = trailingslashit($currentsection->target_basepath) . ltrim($file->source_file, "/\\");
                $files[$file->id] = $newfile;
            }
        }

        return $files;
    }

    /**
     *  Set files to deleted
     */
    public function setFilesToDeleted($filelist)
    {
        global $wpdb;
        foreach ($filelist as $key => $file) {
            if (isset($file->deleted) && $file->deleted === true) {
                $wpdb->update(
                    $wpdb->prefix . "wpsynchro_sync_list",
                    [
                        'needs_delete' => 0
                    ],
                    ['id' => $key],
                    [
                        '%d'
                    ],
                    ['%d']
                );
            }
        }
    }

    /**
     *  Get remaining files to delete
     */
    public function getRemainingFileDeletes()
    {
        global $wpdb;
        $delete_count = $wpdb->get_var("select count(*) from " . $wpdb->prefix . "wpsynchro_sync_list where needs_delete=1");
        return $delete_count;
    }

    /**
     *  Get progress part for file description - Just part with (File: X / Y - Size: Z / V)
     */
    public function getFileProgressDescriptionPart()
    {
        if (!$this->job->files_all_sections_populated) {
            $source_count = number_format_i18n($this->job->files_population_source_count, 0);
            $target_count = number_format_i18n($this->job->files_population_target_count, 0);
            return sprintf(__("(Source: %s files - Target: %s files)", "wpsynchro"), $source_count, $target_count);
        }

        $one_mb = 1024 * 1024;
        $completed_size = intval($this->job->files_transfer_completed_size);
        $total_size = intval($this->job->files_needs_transfer_size);

        // Show in mb
        $completed_size = number_format_i18n($completed_size / $one_mb, 0) . " MB";
        $total_size = number_format_i18n($total_size / $one_mb, 0) . " MB";

        return sprintf(__("(File: %d / %d - Size: %s / %s)", "wpsynchro"), $this->job->files_transfer_completed_counter, $this->job->files_needs_transfer, $completed_size, $total_size);
    }

    /**
     *  Get section by id
     */
    public function getSectionByID($id)
    {
        if (isset($this->section_lookup[$id])) {
            return $this->section_lookup[$id];
        }

        foreach ($this->job->files_sections as &$section) {
            if ($section->id == $id) {
                $this->section_lookup[$id] = $section;
                return $section;
            }
        }
        return null;
    }

    /**
     *  Set hashes for delete and transfer detection
     */
    public function setDeleteAndTransferHashInDB()
    {
        global $wpdb;

        // Set transfer hash
        $setsql = "update `" . $wpdb->prefix . "wpsynchro_sync_list` set needs_transfer_hash=md5(concat(section,source_file,is_dir,size,hash)) where needs_transfer_hash is null";
        $rows_changed = $wpdb->query($setsql);
        $this->logger->log("DEBUG", "Updated needs_transfer_hash on " . $rows_changed . " rows");

        // Set delete hash
        $deletehashsql = "update `" . $wpdb->prefix . "wpsynchro_sync_list` set needs_delete_hash=md5(concat(section,source_file)) where needs_delete_hash is null";
        $delete_hash_rows_changed = $wpdb->query($deletehashsql);
        $this->logger->log("DEBUG", "Updated needs_delete_hash on " . $delete_hash_rows_changed . " rows");
    }

    /**
     *  Get all files for transfer or delete
     */
    public function getFileChangesByType($type)
    {
        global $wpdb;
        if ($type == "delete") {
            $files = $wpdb->get_results(
                "SELECT `section`, `source_file`
                FROM `{$wpdb->prefix}wpsynchro_sync_list`
                WHERE `needs_delete`=1
                ORDER BY `source_file`"
            );
        } elseif ($type == "add") {
            $files = $wpdb->get_results(
                "SELECT `section`, `source_file`
                FROM `{$wpdb->prefix}wpsynchro_sync_list`
                WHERE `needs_transfer`=1
                ORDER BY `source_file`"
            );
        }

        $file_list = [];

        if ($files) {
            foreach ($files as $file) {
                $currentsection = $this->getSectionByID($file->section);
                $relativepath = ltrim($file->source_file, "/\\");
                $file_list[] = trailingslashit($currentsection->target_basepath) . $relativepath;
            }
        }

        return $file_list;
    }
}
