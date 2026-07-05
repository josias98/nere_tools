<?php

/**
 * Class for handling what to show when clicking licensing on the menu in wp-admin
 */

namespace WPSynchro\Pages;

use WPSynchro\Utilities\CommonFunctions;
use WPSynchro\Utilities\Licensing\LicenseState;
use WPSynchro\Utilities\Licensing\Licensing;

class AdminLicensing
{
    /**
     *  Called from WP menu to show setup
     */
    public static function render()
    {
        $instance = new self();
        // Handle post
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $instance->handlePOST();
        }
        $instance->handleGET();
    }

    /**
     *  Handle the update of data from licensing screen
     */
    private function handlePOST()
    {
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'wpsynchro_set_license_key')) {
            echo "<div class='notice wpsynchro-notice'><p>" . __('Security token is no longer valid - Go back and try again.', 'wpsynchro') . '</p></div>';
            return;
        }

        // Save access key
        if (isset($_POST['wpsynchro_license_key'])) {
            $licensekey = sanitize_key(trim($_POST['wpsynchro_license_key']));
        } else {
            $licensekey = "";
        }

        $licensing = new Licensing();
        $licensing->setCurrentLicenseKey($licensekey);
    }

    /**
     *  Show WP Synchro licensing screen
     */
    private function handleGET()
    {
        $licensing = new Licensing();
        $licensekey = $licensing->getCurrentLicenseKey();
        $licensing_details = $licensing->getLicenseState();

        $commonfunctions = new CommonFunctions();

        $is_license_key_defined_as_constant = false;
        if (defined('WPSYNCHRO_LICENSE_KEY') && strlen(WPSYNCHRO_LICENSE_KEY) > 0) {
            $is_license_key_defined_as_constant = true;
        }

        $headline = __('Enter your license key', 'wpsynchro');
        $headline_background = "#324e67";
        if ($licensing_details) {
            if ($licensing_details->status === true) {
                $headline = __('Your license is active and valid', 'wpsynchro');
                $headline_background = "#56ab2f";
            } elseif ($licensing_details->status === false) {
                $headline = __('Your license is not valid', 'wpsynchro');
                $headline_background = "#e22525";
            }
        }

        // Nonces
        $set_license_key_nonce = wp_create_nonce('wpsynchro_set_license_key');

        ?>
        <div class="wrap wpsynchro-licensing wpsynchro">
            <h2 class="wpsynchro-page-header-title"><img src="<?= $commonfunctions->getAssetUrl("icon.png") ?>" width="35" height="35" />WP Synchro <?= WPSYNCHRO_VERSION ?> <?php echo (\WPSynchro\Utilities\CommonFunctions::isPremiumVersion() ? 'PRO' : 'FREE'); ?> - <?php _e('Licensing', 'wpsynchro'); ?></h2>
            <p><?php _e('Insert your license key for your PRO version, so you can use the full functionality of WP Synchro PRO.', 'wpsynchro'); ?></p>


            <div class="sectionheader" style="background-color: <?= $headline_background ?>"><span class="dashicons dashicons-shield-alt"></span><?= $headline ?></div>
            <p><?php _e('Your license key can be found on <a href="https://daev.tech" target="_blank">https://daev.tech</a> on My Account after login in with your credentials.', 'wpsynchro'); ?><br><?php _e('The license key will be validated against license server and will be revalidated every day automatically.', 'wpsynchro'); ?></p>

            <?php
            echo '<b>' . __('Currently used license key', 'wpsynchro') . ':</b> ';
            if (strlen($licensekey) > 10) {
                echo sprintf(__('starts with %s and ends with %s', 'wpsynchro'), substr($licensekey, 0, 5), substr($licensekey, strlen($licensekey) - 5, 5));
                if ($is_license_key_defined_as_constant) {
                    echo ' ' . __('(Set as constant in code)', 'wpsynchro');
                }
            } else {
                echo __('no license key', 'wpsynchro');
            }

            if ($licensing_details) {
                $diff_seconds = time() - $licensing_details->timestamp;
                $diff_mins = floor($diff_seconds / 60);
                $diff_hours = floor($diff_seconds / 3600);

                $diff_last_retry = time() - $licensing_details->last_retry;
                $diff_last_retry_mins = floor($diff_last_retry / 60);

                if ($licensing_details->status === null) {
                    ?>
                    <p><b><?php echo sprintf(__("License is in a unknown state - We are retrying to contact license server to determine state - Attempt %d of %d", "wpsynchro"), $licensing_details->retries, 10) ?></b></p>
                    <p><b><?php echo sprintf(__("Last retry was %s minutes ago and we will retry with ~%d min intervals up to %d attempts.", "wpsynchro"), $diff_last_retry_mins, floor(LicenseState::TIME_BETWEEN_RETRIES / 60), LicenseState::MAX_RETRIES) ?></b></p>
                    <p><?php _e('Make sure it is possible to connect out of this webserver to license server at daev.tech domain.', 'wpsynchro'); ?></p>
                    <?php
                } elseif ($licensing_details->status === true) {
                    ?>
                    <p><b><?php echo sprintf(__("License is <u>valid</u> and <u>active</u>. Last checked %s hours ago.", "wpsynchro"), $diff_hours) ?></b></p>
                    <?php
                } elseif ($licensing_details->status === false) {
                    ?>
                    <p><b><?php echo sprintf(__("License is NOT valid. Last checked %s hours ago. Change your key to a valid one or contact support if you have questions.", "wpsynchro"), $diff_hours) ?></b></p>
                    <p><?php _e('To recheck your current key, just save the key again and it will be re-validated.', 'wpsynchro'); ?></p>
                    <?php
                }
            } else {
                ?>
                <p><b><?php _e("License is not yet validated - Insert your license key and validate it.", "wpsynchro") ?></b></p>

                <?php
            }

            ?>

            <div class="sectionheader"><span class="dashicons dashicons-admin-network"></span> <?php _e('Set license key', 'wpsynchro'); ?></div>
            <?php
            if ($is_license_key_defined_as_constant) {
                echo __('Your license key is hardcoded in code, as a PHP constant. It is called WPSYNCHRO_LICENSE_KEY and it overrides all others.', 'wpsynchro') . "<br>";
                echo __('To change it, you need to find the constant in code and change it there.', 'wpsynchro') . '<br>';
                echo __('If you remove the constant from code, you can change the license key right here after.', 'wpsynchro');

                ?>
                <form id="wpsynchro-licensing-form" method="POST">
                    <input type="hidden" name="nonce" value="<?= $set_license_key_nonce ?>" />
                    <p><?= __('If you want to re-validate the license key defined in constant, press the button below:', 'wpsynchro') ?></p>
                    <p><input class="btn btn-primary" type="submit" value="<?php _e('Re-check license key', 'wpsynchro'); ?>" /></p>
                </form>

                <?php
            } else {
                ?>
                <form id="wpsynchro-licensing-form" method="POST">
                    <input type="hidden" name="nonce" value="<?= $set_license_key_nonce ?>" />
                    <table class="">
                        <tr>
                            <td><label for="wpsynchro_license_key_field"><?php _e('New license key', 'wpsynchro'); ?></label></td>
                            <td>
                                <input type="text" name="wpsynchro_license_key" id="wpsynchro_license_key_field" value="" class="regular-text ltr" autocomplete="off"><br>
                            </td>
                        </tr>
                    </table>
                    <p>By saving your new license key, you agree to the terms of use, outlined in section "TERMS OF USE" on this page.</p>
                    <p><input class="btn btn-primary" type="submit" value="<?php _e('Save license key and validate', 'wpsynchro'); ?>" /></p>

                </form>
                <?php
            }
            ?>

            <div class="sectionheader"><span class="dashicons dashicons-media-default"></span> <?php _e('Terms of use', 'wpsynchro'); ?> </div>
            <p><?php _e('By inserting your license key and saving it, you accept that we use this information to contact WP Synchro license server.', 'wpsynchro'); ?>
                <br><?php _e('After successful validation, the key will be revalidated every day to make sure it is still valid.', 'wpsynchro'); ?>
                <br><?php _e('On the server, the key will be checked and request is logged, along with your public IP address and site url.', 'wpsynchro'); ?>
                <br><?php _e('We dont save or send other information to the license server.', 'wpsynchro'); ?>
            </p>
            <p><?php _e('For every migration, the license server will be contacted to verify the license and the active sites limit for your subscription.', 'wpsynchro'); ?>
                <br><?php _e('These requests contain only your license key and the urls of migration. These will be logged along with your public IP address.', 'wpsynchro'); ?>
            </p>
        </div>
        <?php
    }
}
