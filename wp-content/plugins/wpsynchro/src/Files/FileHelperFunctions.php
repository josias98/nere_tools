<?php

namespace WPSynchro\Files;

use WPSynchro\Utilities\CommonFunctions;

/**
 * Box of helper functions used for file migration
 */
class FileHelperFunctions
{
    /**
     *  Get files in web root to exclude
     */
    public static function getWPFilesInWebrootToExclude()
    {
        $files = [
            ABSPATH . "wp-activate.php",
            ABSPATH . "wp-blog-header.php",
            ABSPATH . "wp-comments-post.php",
            ABSPATH . "wp-config.php",
            ABSPATH . "wp-config-sample.php",
            ABSPATH . "wp-cron.php",
            ABSPATH . "wp-links-opml.php",
            ABSPATH . "wp-load.php",
            ABSPATH . "wp-login.php",
            ABSPATH . "wp-mail.php",
            ABSPATH . "wp-settings.php",
            ABSPATH . "wp-signup.php",
            ABSPATH . "wp-trackback.php",
            ABSPATH . "xmlrpc.php",
        ];

        $common = new CommonFunctions();
        $files = array_map([$common, 'fixPath'], $files);

        return $files;
    }

    /**
     * Recursively delete files in directory (with max timer)
     */
    public static function removeDirectory($dir, &$timer)
    {

        if ($timer->getRemainingSyncTime() < 2) {
            return false;
        }
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $response = self::removeDirectory($dir . "/" . $object, $timer);
                        if ($response === false) {
                            return false;
                        }
                    } else {
                        @unlink($dir . "/" . $object);
                    }
                }
            }
            @rmdir($dir);
            return true;
        } else {
            return false;
        }
    }
}
