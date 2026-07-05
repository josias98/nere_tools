<?php
define('DB_NAME', 'wordpress');
define('DB_USER', 'wordpress');
define('DB_PASSWORD', 'wordpress');
define('DB_HOST', 'db');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

define('AUTH_KEY',         'local-dev');
define('SECURE_AUTH_KEY',  'local-dev');
define('LOGGED_IN_KEY',    'local-dev');
define('NONCE_KEY',        'local-dev');
define('AUTH_SALT',        'local-dev');
define('SECURE_AUTH_SALT', 'local-dev');
define('LOGGED_IN_SALT',   'local-dev');
define('NONCE_SALT',       'local-dev');

$table_prefix = 'wp_1402045_';

define('WP_HOME', 'http://localhost:8080');
define('WP_SITEURL', 'http://localhost:8080');
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('WP_MEMORY_LIMIT', '256M');

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
