=== InfiniteWP Client ===
Contributors: infinitewp, amritanandh, rajkuppus
Tags: Multiple admin, backup, updates, security, multi site
Requires at least: 3.1
Tested up to: 6.9.1
Stable tag: 1.13.5
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Install this plugin on unlimited sites and manage them all from a central dashboard.
This plugin communicates with your InfiniteWP Admin Panel.

== Description ==

[InfiniteWP](https://infinitewp.com/ "Manage Multiple WordPress") allows users to manage unlimited number of WordPress sites from their own server.

Main features:

*   Self-hosted system: Resides on your own server and totally under your control
*   One-click updates for WordPress, plugins and themes across all your sites
*   Instant backup and restore your entire site or just the database
*   One-click access to all WP admin panels
*   Bulk Manage plugins & themes: Activate & Deactive multiple plugins & themes on multiple sites simultaneously
*   Bulk Install plugins & themes in multiple sites at once
*   and more..

Visit us at [InfiniteWP.com](https://infinitewp.com/ "Manage Multiple WordPress").

Check out the [InfiniteWP Overview Video](https://www.youtube.com/watch?v=s35ZoW95cnU) below.

https://www.youtube.com/watch?v=s35ZoW95cnU

Credits: [Vladimir Prelovac](http://prelovac.com/vladimir) for his worker plugin on which the client plugin is being developed.


== Installation ==

1. Upload the plugin folder to your /wp-content/plugins/ folder
2. Go to the Plugins page and activate InfiniteWP Client
3. If you have not yet installed the InfiniteWP Admin Panel, visit [InfiniteWP.com](http://infinitewp.com/ "Manage Multiple WordPress"), download the free InfiniteWP Admin Panel & install on your server.
4. Add your WordPress site to the InfiniteWP Admin Panel and start using it.

== Screenshots ==

1. Sites & Group Management
2. Search WordPress Plugin Repository
3. Bulk Plugin & Theme Management
4. One-click access to WordPress admin panels
5. One-click updates

== Changelog ==

= 1.13.5 - Feb 10th 2026 =
* Fix: Fatal Error in Theme Update Detection (Limited Impact).

= 1.13.4 - Feb 5th 2026 =
* Fix: Introduced new IWP_MU_PLUGIN_LOADER_DISABLED constant to allow disabling the MU plugin loader when required.
* Fix: SSL certificate problem: unable to get local issuer certificate error when connecting to Dropbox API.

= 1.13.3 - June 5th 2025 =
* Fix: Multicall limit reached for multical php db backup for the large site for few users.

= 1.13.2 - Feb 17th 2025 =
* Fix: MySQL DB Dump was not functioning when the database host was specified as localhost:3306
* Fix: Added MySQL DB Dump path configuration support for Flex servers.
* Fix: Old Dropbox backups were not being deleted as per the retained limit settings.
* Fix: Fixed an intermittent issue where Dropbox backups failed to upload.
* Fix: Addressed a PHP fatal error: Uncaught ValueError: base and exponent overflow in iwp-client/lib/phpseclib/phpseclib/phpseclib/Math/BigInteger.php.
* Fix: Addressed a PHP fatal error:Uncaught TypeError: Cannot access offset of type string on string in /iwp-client/installer.class.php:931

= 1.13.1 - Dec 5th 2024 =
* Fix: Open Admin failed when the Duo Universal plugin was active.
* Fix: An issue causing SFTP backups to repeatedly upload, resulting in a "Multicall call limit reached" error.
* Fix: Old Dropbox backups were not being deleted as per the retained limit settings.
* Fix: WP Rocket clear cache notifications not disappearing in the wp-admin page after clearing the cache via the IWP admin panel.
* Fix: Enhanced the clarity of update error messages for better troubleshooting.

= 1.13.0 - April 29th 2024 =
* Feature: SFTP support for multicall method backups.
* Fix: SFTP key-based backup not working for AWS S3.
* Fix: Openssl verification issue in the red hat server.
* Fix: PHP Fatal error occurred: Uncaught mysqli_sql_exception: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'IF' at line 1.
* Fix: Code snippet empty responses.
* Fix: The PHP dump fails when the table name is in the format 'wp_example-1034'.
* Fix: While running Phoenix backup failed to update the IWP_backup_status option and value 0.
* Fix: PHP Fatal error occurred: Uncaught TypeError: openssl_verify(): Argument #2 ($signature) must be of type string, array given in /wp-content/plugins/iwp-client/helper.class.php:387 – while adding or re-adding a site on a Red hat server with PHP version 8.3.
* Fix: WP Engine updated the API key.
* Fix: The plugin and theme update issue will show as an error (the theme is at the latest version).
* Fix: '_iwp_redirects' logging in a slow query log for certain users.

= 1.12.5 - Jan 3rd 2024 =
* Improvement: Plugin update response improved.
* Fix: PHP Fatal error occurred: Uncaught ArgumentCountError: Too few arguments to function IWP_MMB_S3::abortMultipartUpload(), 1 passed in /iwp-client/lib/amazon/s3IWPBackup.php
* Fix: PHP Fatal error occurred: Uncaught TypeError: fseek(): Argument #1 ($stream) must be of type resource, bool given in /iwp-client/backup.class.multicall.php:4356
* Fix: IWP Client plugin connection error while performing WP fastest plugin cache clear.
* Fix: Backup taken using Phoenix method not satisfying number of backups to keep.
* Fix: php8 related warnings fixed.

= 1.12.3.1 - Dec 8th 2023 =
* Improvement: New constant (IWP_BROKEN_LINK_RESULT_LIMIT) introduced to limit the Broken Linker checker result.
* Improvement: PHP secure library updated.
* Improvement: Better naming convention adopted.

[See changelog for all versions.](https://infinitewp.com/change-log/iwp-client-plugin/)