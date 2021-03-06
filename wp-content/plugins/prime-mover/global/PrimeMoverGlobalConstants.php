<?php
/********************************************************************
 * PRIME MOVER CONSTANTS
 * Some of these constants can be overriden via WordPress config file.
 * ******************************************************************
 */
if (!defined('ABSPATH')) {
    exit;
}

define('PRIME_MOVER_VERSION', '1.5.0');
define('PRIME_MOVER_PLUGIN_CODENAME', 'Prime Mover');
define('PRIME_MOVER_BACKUP_MARKUP_VERSION', '1.1.8');
define('PRIME_MOVER_PLUGIN_PATH', plugin_dir_path(PRIME_MOVER_MAINPLUGIN_FILE));
define('PRIME_MOVER_SHELL_ARCHIVER_FILENAME', 'prime-mover-shell-archiver.php');
define('PRIME_MOVER_MUST_USE_PLUGIN_FILENAME', 'prime-mover-cli-plugin-manager.php');
define('PRIME_MOVER_PLUGIN_CORE_PATH', dirname(PRIME_MOVER_PLUGIN_PATH) . DIRECTORY_SEPARATOR);
define('PRIME_MOVER_WPRIME_CONFIG', 'wprime-config.json');
define('PRIME_MOVER_WPRIME_CLOSED_IDENTIFIER', 'wprime-readme.txt');
define('PRIME_MOVER_PLUGIN_UTILITIES_PATH', PRIME_MOVER_PLUGIN_PATH. 'utilities' . DIRECTORY_SEPARATOR);
define('PRIME_MOVER_THEME_CORE_PATH', get_theme_root());
define('PRIME_MOVER_EXPORT_DIR_SLUG', 'prime-mover-export-files');
define('PRIME_MOVER_TMP_DIR_SLUG', 'prime-mover-tmp-downloads');
define('PRIME_MOVER_LOCK_DIR_SLUG', 'prime-mover-lock-files');
define('PRIME_MOVER_IMPORT_DIR_SLUG', 'prime-mover-import-files');

if (!defined('PRIME_MOVER_DONT_TRACK_USERIP')) {
    define('PRIME_MOVER_DONT_TRACK_USERIP', true);
}

if (!defined('PRIME_MOVER_PLUGIN_URI')) {
    define('PRIME_MOVER_PLUGIN_URI', 'https://codexonics.com/');
}

if (!defined('CODEXONICS_CONTACT')) {
    define('CODEXONICS_CONTACT', 'https://codexonics.com/contact/');
}

if (!defined('CODEXONICS_ACTIVATE_LICENSE_GUIDE')) {
    define('CODEXONICS_ACTIVATE_LICENSE_GUIDE', 'https://codexonics.com/prime_mover/prime-mover/faq-i-cannot-activate-license-please-help/');
}

if (!defined('CODEXONICS_LICENSING_GUIDE')) {
    define('CODEXONICS_LICENSING_GUIDE', 'https://codexonics.com/prime_mover/prime-mover/a-complete-guide-on-using-prime-mover-licenses/');
}

if (!defined('CODEXONICS_ACTIVATE_PRO_GUIDE')) {
    define('CODEXONICS_ACTIVATE_PRO_GUIDE', 'https://codexonics.com/prime_mover/prime-mover/faq-how-to-activate-prime-mover-pro/');
}

if (!defined('CODEXONICS_DOCUMENTATION')) {
    define('CODEXONICS_DOCUMENTATION', 'https://codexonics.com/prime_mover/prime-mover/');
}

if (!defined('PRIME_MOVER_PLUGIN_FILE')) {
    define('PRIME_MOVER_PLUGIN_FILE', basename(PRIME_MOVER_MAINPLUGIN_FILE));
}

if (!defined('PRIME_MOVER_SECURE_PROTOCOL')) {
    define('PRIME_MOVER_SECURE_PROTOCOL', 'https://');
}

if (!defined('PRIME_MOVER_NON_SECURE_PROTOCOL')) {
    define('PRIME_MOVER_NON_SECURE_PROTOCOL', 'http://');
}

if (!defined('PRIME_MOVER_UPLOADRETRY_LIMIT')) {
    define('PRIME_MOVER_UPLOADRETRY_LIMIT', 75);
}

if (!defined('PRIME_MOVER_TOTAL_WAITING_ERROR_SECONDS')) {
    define('PRIME_MOVER_TOTAL_WAITING_ERROR_SECONDS', 120);
}

if (!defined('PRIME_MOVER_JS_ERROR_ANALYSIS')) {
    define('PRIME_MOVER_JS_ERROR_ANALYSIS', false);
}

if (!defined('PRIME_MOVER_UPLOAD_REFRESH_INTERVAL')) {
    define('PRIME_MOVER_UPLOAD_REFRESH_INTERVAL', 20000);
}

if (!defined('PRIME_MOVER_UPLOAD_REFRESH_INTERVAL_LOCAL')) {
    define('PRIME_MOVER_UPLOAD_REFRESH_INTERVAL_LOCAL', 5000);
}

if (!defined('PRIME_MOVER_BATCH_COPY_MEDIA_ARCHIVE')) {
    define('PRIME_MOVER_BATCH_COPY_MEDIA_ARCHIVE', 100);
}

if (!defined('PRIME_MOVER_DEFAULT_FREE_BASENAME')) {
    define('PRIME_MOVER_DEFAULT_FREE_BASENAME', 'prime-mover/prime-mover.php');
}

if (!defined('PRIME_MOVER_DEFAULT_PRO_BASENAME')) {
    define('PRIME_MOVER_DEFAULT_PRO_BASENAME', 'prime-mover-pro/prime-mover.php');
}

if (!defined('PRIME_MOVER_COPYMEDIA_SCRIPT')) {
    define('PRIME_MOVER_COPYMEDIA_SCRIPT', PRIME_MOVER_PLUGIN_PATH. 'scripts' . DIRECTORY_SEPARATOR . PRIME_MOVER_SHELL_ARCHIVER_FILENAME);
}

if (!defined('PRIME_MOVER_MUST_USE_PLUGIN_SCRIPT')) {
    define('PRIME_MOVER_MUST_USE_PLUGIN_SCRIPT', PRIME_MOVER_PLUGIN_PATH. 'scripts' . DIRECTORY_SEPARATOR . PRIME_MOVER_MUST_USE_PLUGIN_FILENAME);
}

if (!defined('PRIME_MOVER_LARGE_FILESIZE')) {
    define('PRIME_MOVER_LARGE_FILESIZE', 104857600);
}

if (!defined('PRIME_MOVER_CRON_TEST_MODE')) {
    define('PRIME_MOVER_CRON_TEST_MODE', false);
}

if (!defined('PRIME_MOVER_CRON_DELETE_TMP_INTERVALS')) {
    define('PRIME_MOVER_CRON_DELETE_TMP_INTERVALS', 86400);
}

if (!defined('PRIME_MOVER_RETRY_TIMEOUT_SECONDS')) {
    define('PRIME_MOVER_RETRY_TIMEOUT_SECONDS', 15);
}

if (!defined('PRIME_MOVER_TEST_CORE_DOWNLOAD')) {
    define('PRIME_MOVER_TEST_CORE_DOWNLOAD', false);
}

if (!defined('PRIME_MOVER_RESTORE_URL_DOC') ) {
    define('PRIME_MOVER_RESTORE_URL_DOC', 'https://codexonics.com/prime_mover/prime-mover/how-to-export-and-restore-using-pro-version/');
}

if (!defined('PRIME_MOVER_PRICING_PAGE') ) {
    define('PRIME_MOVER_PRICING_PAGE', 'https://codexonics.com/prime_mover/prime-mover/pricing/');
}

if (!defined('PRIME_MOVER_GET_BLOGID_TUTORIAL') ) {
    define('PRIME_MOVER_GET_BLOGID_TUTORIAL', 'https://codexonics.com/prime_mover/prime-mover/how-to-get-multisite-target-blog-id/');
}

if (!defined('PRIME_MOVER_CLI_TIMEOUT_SECONDS') ) {
    define('PRIME_MOVER_CLI_TIMEOUT_SECONDS', 450);
}

if (!defined('PRIME_MOVER_PHPDUMP_BATCHSIZE') ) {
    define('PRIME_MOVER_PHPDUMP_BATCHSIZE', 5000);
}

if (!defined('PRIME_MOVER_THRESHOLD_BYTES_MEDIA') ) {
    define('PRIME_MOVER_THRESHOLD_BYTES_MEDIA', 52428800);
}

if (!defined('PRIME_MOVER_THRESHOLD_BYTES_PLUGIN') ) {
    define('PRIME_MOVER_THRESHOLD_BYTES_PLUGIN', 15728640);
}

if (!defined('PRIME_MOVER_ENABLE_EVENT_LOG') ) {
    define('PRIME_MOVER_ENABLE_EVENT_LOG', true);
}

if (!defined('PRIME_MOVER_ENABLE_FILE_LOG') ) {
    define('PRIME_MOVER_ENABLE_FILE_LOG', false);
}

if (!defined('PRIME_MOVER_STREAM_COPY_CHUNK_SIZE')) {
    define('PRIME_MOVER_STREAM_COPY_CHUNK_SIZE', 1048576);
}

if (!defined('PRIME_MOVER_CURL_BUFFER_SIZE')) {
    define('PRIME_MOVER_CURL_BUFFER_SIZE', 16384);
}

if (!defined('PRIME_MOVER_SLOW_WEB_HOST')) {
    define('PRIME_MOVER_SLOW_WEB_HOST', true);
}

if (!defined('PRIME_MOVER_CORE_GEARBOX_CURL_TIMEOUT')) {
    define('PRIME_MOVER_CORE_GEARBOX_CURL_TIMEOUT', 300);
}

if (!defined('PRIME_MOVER_CACHE_DB_FILE')) {
    define('PRIME_MOVER_CACHE_DB_FILE', WP_CONTENT_DIR . '/db.php');
}

if (!defined('PRIME_MOVER_OBJECT_CACHE_FILE')) {
    define('PRIME_MOVER_OBJECT_CACHE_FILE', WP_CONTENT_DIR . '/object-cache.php');
} 

if (!defined('PRIME_MOVER_FORCE_DOMAIN_REPLACE')) {
    define('PRIME_MOVER_FORCE_DOMAIN_REPLACE', false);
}

if (!defined('PRIME_MOVER_CUSTOMER_LOOKUP_LIMIT')) {
    define('PRIME_MOVER_CUSTOMER_LOOKUP_LIMIT', 20);
}

if (!defined('PRIME_MOVER_POSTAUTHORS_UPDATE_LIMIT')) {
    define('PRIME_MOVER_POSTAUTHORS_UPDATE_LIMIT', 30);
}

if (!defined('PRIME_MOVER_LOWEST_PRIORITY')) {
    define('PRIME_MOVER_LOWEST_PRIORITY', 100000);
}