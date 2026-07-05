<?php

namespace WPSynchro\Utilities\Licensing;

use WPSynchro\Transport\RemoteTransport;
use WPSynchro\Utilities\CommonFunctions;

/**
 * Class used for license handling (only used for PRO version)
 */
class Licensing
{
    const LICENSE_SERVER_SERVICE = 'https://daev.tech/api/wpsynchro/v1/license/';
    const MIGRATION_SERVER_SERVICE = 'https://daev.tech/api/wpsynchro/v1/synchronizerequest/';
    const LICENSE_STATE_OPTION_NAME = 'wpsynchro_license_key_validation';
    const LICENSE_KEY_OPTION = 'wpsynchro_license_key';

    /**
     * Get license state
     * @return LicenseState
     */
    public function getLicenseState()
    {
        $license_state = $this->getState();
        if ($license_state === false) {
            $this->verifyLicense();
            return get_option(self::LICENSE_STATE_OPTION_NAME);
        }
        return $license_state;
    }

    /**
     * Save license state
     * @return LicenseState|false
     */
    public function getState()
    {
        return get_option(self::LICENSE_STATE_OPTION_NAME);
    }

    /**
     * Save license state
     * @param LicenseState $license_state
     */
    public function saveState(LicenseState $license_state)
    {
        return update_option(self::LICENSE_STATE_OPTION_NAME, $license_state, false);
    }

    /**
     * Update license state
     * @param LicenseState $license_state
     */
    public function updateState(LicenseState $license_state)
    {
        $new_status = $this->checkLicenseKeyOnLicenseServer($license_state->key);
        if ($new_status === null) {
            // No useful result
            $license_state->retries++;
            $license_state->last_retry = time();
        } else {
            $license_state->status = $new_status;
            $license_state->timestamp = time();
        }
        $this->saveState($license_state);
    }

    /**
     * Verify license
     */
    public function verifyLicense()
    {
        // Check if we have a state or need a new one
        $license_state = $this->getState();
        if ($license_state === false) {
            // No validation data - Must be first time or new key
            $license_key = $this->getCurrentLicenseKey();

            // Set base state
            $license_state = new LicenseState();
            $license_state->key = $license_key;

            $this->saveState($license_state);
        }

        // Make sure state is refreshed if needed
        if ($license_state->needsUpdate()) {
            $this->updateState($license_state);
        }

        $this->saveState($license_state);

        return $license_state->isValid();
    }

    /**
     * Check license key on license server
     */
    public function checkLicenseKeyOnLicenseServer($key)
    {
        if (empty($key)) {
            return false;
        }

        // Contact license server
        $bodydata = new \stdClass();
        $bodydata->homeurl = \get_home_url();

        // Do request
        $args = [
            'body' => json_encode($bodydata),
            'headers' => [
                'Content-Type' => "application/json; charset=utf-8",
            ],
        ];

        $license_server_response = wp_remote_post(self::LICENSE_SERVER_SERVICE . $key, $args);

        if (!is_wp_error($license_server_response)) {
            if (wp_remote_retrieve_response_code($license_server_response) == 200) {
                $body = json_decode(wp_remote_retrieve_body($license_server_response));
                // TODO: Actually try to use the returning "reason", as it contains why the license was not valid
                return $body->valid ?? null;
            }
        }
        return null;
    }

    /**
     * Get current license key
     */
    public function getCurrentLicenseKey()
    {
        $license_key = "";

        // Check if it is defined first, which overriddes db value
        if (defined('WPSYNCHRO_LICENSE_KEY')) {
            $license_key = WPSYNCHRO_LICENSE_KEY;
            if (strlen($license_key) > 0) {
                return $license_key;
            }
        }

        $licensekey = get_option(self::LICENSE_KEY_OPTION, "");
        return $licensekey;
    }

    /**
     * Set current license key
     */
    public function setCurrentLicenseKey($license_key)
    {
        // Save new license key
        update_option(self::LICENSE_KEY_OPTION, $license_key, false);
        // Reset current validation in db, if it exist, so new is validated
        delete_option(self::LICENSE_STATE_OPTION_NAME);
        // Reset last healthcheck, to make sure things are okay after
        delete_site_option("wpsynchro_healthcheck_timestamp");
    }

    /**
     * Get license error message
     */
    public function getLicenseErrorMessage()
    {
        return sprintf(__("%s uses a WP Synchro PRO version that can not be validated using the current license key. Update license key on this site to a valid one and try again. This can be done in menu WP Synchro > Licensing.", "wpsynchro"), get_home_url());
    }

    /**
     * Check if problems with licensing
     */
    public function hasProblemWithLicensing()
    {
        if (CommonFunctions::isPremiumVersion()) {
            $state = $this->getLicenseState();
            if ($state->isValid()) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     *  Verify license when synchronizing
     */
    public function verifyLicenseForMigration($from_url, $to_url)
    {
        $result = new \stdClass();
        $result->state = false;
        $result->errors = [];

        $verified_license = $this->verifyLicense();
        if ($verified_license) {
            // If licens is verified, send a request for migration to license server
            $bodydata = new \stdClass();
            $bodydata->from_url = $from_url;
            $bodydata->to_url = $to_url;

            // Do request
            $args = [
                'body' => json_encode($bodydata),
                'headers' => [
                    'Content-Type' => "application/json; charset=utf-8",
                ],
            ];

            $license_server_response = $this->wpRemotePOST(self::MIGRATION_SERVER_SERVICE . $this->getCurrentLicenseKey(), $args);

            if (!is_wp_error($license_server_response)) {
                if (wp_remote_retrieve_response_code($license_server_response) == 200) {
                    $body = json_decode(wp_remote_retrieve_body($license_server_response));
                    if (isset($body->valid) && $body->valid === true) {
                        $result->state = true;
                    } else {
                        $result->state = false;
                        $result->errors[] = __("Migration was denied by WP Synchro license server, which can be caused by invalid license key or not having enough available sites on your current subscription. Log into https://daev.tech to check out your subscription state.", "wpsynchro");
                    }
                }
            } else {
                $result->state = false;
                $result->errors[] = $this->getLicenseErrorMessage();
            }
        } else {
            $result->state = false;
            $result->errors[] = $this->getLicenseErrorMessage();
        }


        return $result;
    }

    public function wpRemotePOST($url, $args = [])
    {
        return wp_remote_post($url, $args);
    }
}
