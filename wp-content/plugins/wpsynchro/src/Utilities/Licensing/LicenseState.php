<?php

namespace WPSynchro\Utilities\Licensing;

/**
 * License state
 */
class LicenseState
{
    public $key = "";
    public $status = null;
    public $timestamp = null;
    public $retries = 0;
    public $last_retry = null;

    const SECONDS_BETWEEN_CHECKS = 86400; // 86400 = 1 day
    const TIME_BETWEEN_RETRIES = 300; // 5min
    const MAX_RETRIES = 10;


    public function __construct()
    {
    }

    public function isValid()
    {
        // Status needs to be valid
        if ($this->status === true) {
            // And timestamp is within acceptable period
            if ($this->timestamp > (time() - self::SECONDS_BETWEEN_CHECKS)) {
                return true;
            }
        }
        return false;
    }

    public function resetState()
    {
        $this->status = null;
        $this->timestamp = null;
        $this->retries = 0;
        $this->last_retry = null;
    }

    public function needsUpdate()
    {
        // If everything is good, we dont need update
        if (is_bool($this->status) && !is_null($this->timestamp) && $this->timestamp > (time() - self::SECONDS_BETWEEN_CHECKS)) {
            return false;
        }

        // Needs update if it was never checked
        if ($this->last_retry == null) {
            return true;
        }

        // Needs update if had last result too long ago
        if (is_bool($this->status) && $this->timestamp < (time() - self::SECONDS_BETWEEN_CHECKS)) {
            $this->resetState();
            return true;
        }

        // If we do not yet have a result from the license server
        if (!is_bool($this->status)) {
            // If we have done to many retries
            if ($this->retries > (self::MAX_RETRIES - 1)) {
                $this->status = false;
                $this->timestamp = time();
                return false;
            }
            // But only if the last retry is some time ago
            if (($this->last_retry + self::TIME_BETWEEN_RETRIES) < time()) {
                return true;
            }
        }
        return false;
    }
}
