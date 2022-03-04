<?php
/**
 * This is automatically added by Prime Mover to the must use plugins directory. 
 * This code will only run when doing export/import process with Prime Mover. This will be deleted automatically when plugin is deactivated.
 */

if (defined('PRIME_MOVER_OVERRIDE_PLUGIN_MANAGER') && PRIME_MOVER_OVERRIDE_PLUGIN_MANAGER) {
    return;
}

if (!defined('PRIME_MOVER_DEFAULT_FREE_BASENAME')) {
    define('PRIME_MOVER_DEFAULT_FREE_BASENAME', 'prime-mover/prime-mover.php');
}

if (!defined('PRIME_MOVER_DEFAULT_PRO_BASENAME')) {
    define('PRIME_MOVER_DEFAULT_PRO_BASENAME', 'prime-mover-pro/prime-mover.php');
}

if (!defined('PRIME_MOVER_DEFAULT_ELEMENTOR_BASENAME')) {
    define('PRIME_MOVER_DEFAULT_ELEMENTOR_BASENAME', 'elementor/elementor.php');
}

if (!defined('PRIME_MOVER_ALLOW_THIRDPARTY_PLUGINS')) {
    define('PRIME_MOVER_ALLOW_THIRDPARTY_PLUGINS', true);
}

const PRIME_MOVER_CORE_EXPORT_PROCESSES = ['prime_mover_process_export', 'prime_mover_monitor_export_progress', 'prime_mover_shutdown_export_process'];
const PRIME_MOVER_CORE_IMPORT_PROCESSES = ['prime_mover_process_import', 'prime_mover_monitor_import_progress', 'prime_mover_shutdown_import_process'];
const PRIME_MOVER_CORE_UPLOAD_PROCESSES = ['prime_mover_process_uploads'];

function primeMoverMaybeEnablePluginManagerLog()
{
    return (defined('PRIME_MOVER_PLUGIN_MANAGER_LOG') && PRIME_MOVER_PLUGIN_MANAGER_LOG && file_exists(PRIME_MOVER_PLUGIN_MANAGER_LOG));
}

function primeMoverLogPluginManagerEvents($event = 'option_active_plugins', $plugin = '', $required = [])
{
    if (primeMoverMaybeEnablePluginManagerLog()) {
        file_put_contents(PRIME_MOVER_PLUGIN_MANAGER_LOG, "$event FILTER:" . PHP_EOL, FILE_APPEND | LOCK_EX);
        file_put_contents(PRIME_MOVER_PLUGIN_MANAGER_LOG, "GIVEN: $plugin"  . PHP_EOL, FILE_APPEND | LOCK_EX);
        file_put_contents(PRIME_MOVER_PLUGIN_MANAGER_LOG, print_r($required, true)  . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

function primeMoverMaybeLoadPluginManager()
{
    $input_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    $input_get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
    if (!wp_doing_ajax() && isset($input_get['prime_mover_export_hash']) && isset($input_get['prime_mover_blogid'])) {
        return true;
    }
    
    if (wp_doing_ajax() && isset($input_post['action']) && in_array($input_post['action'], PRIME_MOVER_CORE_EXPORT_PROCESSES)) {
        return true;
    }
    
    if (wp_doing_ajax() && isset($input_post['action']) && in_array($input_post['action'], PRIME_MOVER_CORE_IMPORT_PROCESSES)) {
        return true;
    }
    
    $bypass_upload_plugin_control = false;
    if (defined('PRIME_MOVER_BYPASS_UPLOAD_PLUGIN_CONTROL') && true === PRIME_MOVER_BYPASS_UPLOAD_PLUGIN_CONTROL) {
        $bypass_upload_plugin_control = true;
    }
    if (wp_doing_ajax() && isset($input_post['action']) && in_array($input_post['action'], PRIME_MOVER_CORE_UPLOAD_PROCESSES) & false === $bypass_upload_plugin_control) {
        return true;
    }
    return false;
}

function primeMoverMaybeAddThirdPartyApp($input_post = [])
{
    $required = [PRIME_MOVER_DEFAULT_PRO_BASENAME, PRIME_MOVER_DEFAULT_FREE_BASENAME];
    if (true !== PRIME_MOVER_ALLOW_THIRDPARTY_PLUGINS) {
        return $required;
    }
    
    if (!empty($input_post['prime_mover_next_import_method']) && 'markImportSuccess' === $input_post['prime_mover_next_import_method']) {
        $required[] = PRIME_MOVER_DEFAULT_ELEMENTOR_BASENAME;
    }  
    
    return $required;
}

if (primeMoverMaybeLoadPluginManager()) {    
  
    add_filter('site_option_active_sitewide_plugins', 'loadOnlyPrimeMoverPlugin');    
    add_filter('option_active_plugins', 'loadOnlyPrimeMoverPlugin');     
    add_filter('stylesheet_directory', 'disableThemeOnPrimeMoverProcesses', 10000);
    add_filter('template_directory', 'disableThemeOnPrimeMoverProcesses', 10000);
    
    function disableThemeOnPrimeMoverProcesses($theme) {
        return '';
    }
    
    function loadOnlyPrimeMoverPlugin($plugins)
    {        
        $input_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $required = primeMoverMaybeAddThirdPartyApp($input_post);
        
        if ('site_option_active_sitewide_plugins' === current_filter()) {            
            $plugins = array_filter(
                $plugins,
                function ($key) use ($required) {
                    primeMoverLogPluginManagerEvents('site_option_active_sitewide_plugins', $key, $required);
                    return in_array($key, $required);
                },
                ARRAY_FILTER_USE_KEY
                );
        }
        
        if ('option_active_plugins' === current_filter()) {
            $plugins = array_filter($plugins, function($plugin) use ($required) {
                primeMoverLogPluginManagerEvents('option_active_plugins', $plugin, $required);
                return (in_array($plugin, $required));
            });
        }
        
        if (primeMoverMaybeEnablePluginManagerLog()) {
            file_put_contents(PRIME_MOVER_PLUGIN_MANAGER_LOG, "Filtered plugins FINAL RESULT:" . PHP_EOL, FILE_APPEND | LOCK_EX);
            file_put_contents(PRIME_MOVER_PLUGIN_MANAGER_LOG, print_r($plugins, true)  . PHP_EOL, FILE_APPEND | LOCK_EX);
        } 
        
        return $plugins;   
    }
}
