<?php
namespace Codexonics\PrimeMoverFramework\streams;

/*
 * This file is part of the Codexonics.PrimeMoverFramework package.
 *
 * (c) Codexonics Ltd
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Codexonics\PrimeMoverFramework\classes\PrimeMover;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Prime Mover Database utility class
 * Helper class on database stream processes
 * Responsible for efficient/fast database related processing.
 *
 */
class PrimeMoverDatabaseUtilities
{     
    private $prime_mover;
    private $getmayberandomizedbprefix;
    private $dbprefix_of_site;    
    private $random_prefix;
    private $openssl_utilities;
    private $db_encryption_key;
    private $maybe_enc;
    
    /**
     * Construct
     * @param PrimeMover $prime_mover
     * @param array $utilities
     */
    public function __construct(PrimeMover $prime_mover, $utilities = [])
    {
        $this->prime_mover = $prime_mover;
        $this->getmayberandomizedbprefix = false;
        $this->dbprefix_of_site = '';
        $this->random_prefix = '';
        $this->openssl_utilities = $utilities['openssl_utilities'];
        $this->db_encryption_key = '';
        $this->maybe_enc = false;
    }
    
    /**
     * Get openSSL utilities
     */
    public function getOpenSSLUtilities()
    {
        return $this->openssl_utilities;
    }
    
    /**
     * Get Prime Mover object
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMover
     */
    public function getPrimeMover()
    {
        return $this->prime_mover;
    }
    
    /**
     * Get system authorization
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMoverSystemAuthorization
     */
    public function getSystemAuthorization()
    {
        return $this->getPrimeMover()->getSystemAuthorization();
    }
    
    /**
     * Initialize hooks
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverDatabaseUtilities::itDoesNotAddInitHooksWhenNotAuthorized()
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverDatabaseUtilities::itChecksIfHooksAreOutdated()
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverDatabaseUtilities::itAddsInitHooksWhenAuthorized()
     */
    public function initHooks()
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized()) {
            return;
        }
       
        add_filter('prime_mover_before_mysqldump_php', [$this, 'initializeGetMaybeRandomizeDbPrefix'], 10, 1);
        add_filter('prime_mover_before_mysqldump_php', [$this, 'initializeGetDbPrefixOfSite'], 15, 1);        
        add_filter('prime_mover_before_mysqldump_php', [$this, 'initializeRandomPrefix'], 20, 1); 
        
        
        add_filter('prime_mover_before_mysqldump_php', [$this, 'initializeEncKey'], 25, 1);
        add_filter('prime_mover_before_mysqldump_php', [$this, 'initializeMaybeEnc'], 30, 1);
        add_filter('prime_mover_before_mysqldump_php', [$this, 'computePrimaryKeys'], 35, 2);
       
        add_filter('prime_mover_db_primary_keys_dump', [$this, 'dBPrimaryKeysDump'], 10, 3);            
        add_filter('prime_mover_filter_export_db_data', [$this, 'randomizeDbPrefix'], 5, 1);        
        add_filter('prime_mover_filter_export_db_data', [$this, 'updateUserRoleToRandomPrefix'], 7, 1);
        
        add_filter('prime_mover_filter_export_db_data', [$this, 'encryptData'], 10, 1);        
        add_filter('prime_mover_cleanup_ret_array_afterdump', [$this, 'cleanUpDbRetArrayDump'], 10, 1);
        add_filter('prime_mover_filter_db_port', [$this, 'initializePortForPDO'], 10, 2);
        
        add_action('prime_mover_before_doing_export', [$this, 'maybeFixedCorruptedUserMetaTable'], 99, 2);
        add_action('prime_mover_before_doing_import', [$this, 'maybeFixedCorruptedUserMetaTable'], 99, 2);        
    }
 
    /**
     * Maybe fix corrupted user meta table before any import/export process
     * @param number $blog_id
     * @param boolean $process_initiated
     */
    public function maybeFixedCorruptedUserMetaTable($blog_id = 0, $process_initiated = false)
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized()) {
            return;
        }
        if ($process_initiated) {
            return;
        }
        if (!$this->isUserMetaTableCorrupted()) {
            return;
        }
       
        $this->reEnableAutoIncrementUserMeta();       
    }
    
    /**
     * Re-enable auto-increment on user meta table if needed
     */
    protected function reEnableAutoIncrementUserMeta()
    {
        global $wpdb;
        $usermeta_table = $this->getSystemFunctions()->getUserMetaTableName();
        
        $umeta_id_max = $wpdb->get_var("SELECT max(umeta_id) FROM `{$usermeta_table}`");
        $umeta_id_max = (int)$umeta_id_max;       
        if (!$umeta_id_max) {
            return;
        }
        
        $new_max = $umeta_id_max + 20;        
        $res = $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE `{$usermeta_table}`
                 AUTO_INCREMENT = %d,
                 CHANGE COLUMN `umeta_id` `umeta_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
                 $new_max                
                )
        );
        
        if (false === $res) {
            $this->terminatedBProcess(sprintf(
                esc_html__("%s database table is corrupted. umeta_id field is not using AUTO_INCREMENT. Please check this with your WordPress administrator or hosting company.", 'prime-mover'), 
                $usermeta_table));
        }
    }
    
    /**
     * Terminate dB process
     * @param string $errors
     */
    protected function terminatedBProcess($errors = '')
    {
        do_action('prime_mover_shutdown_actions', [
            'type' => 1,
            'message' => $errors
        ]);    
    
        wp_die();
    }
    
    /**
     * Return TRUE when site user meta table is corrupted
     * @return boolean
     */
    protected function isUserMetaTableCorrupted()
    {
        global $wpdb;
        $usermeta_table = $this->getSystemFunctions()->getUserMetaTableName();
        $columns = $wpdb->get_results("SHOW COLUMNS FROM `{$usermeta_table}` WHERE Extra = 'auto_increment'", ARRAY_A);
        
        return (is_array($columns) && empty($columns));        
    }
    
    /**
     * Initialize port for MySQLDump PHP PDO
     * @param number $port
     * @param array $ret
     * @return number
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverDatabaseUtilities::itInitializesPortForPdo()
     */
    public function initializePortForPDO($port = 0, $ret = [])
    {        
        if ($port) {
            return $port;
        }
        
        if (!empty($ret['db_port'])) {
            return $ret['db_port'];
        }
        
        global $wpdb;
        $result = $wpdb->get_results("SHOW VARIABLES WHERE Variable_name = 'port'", ARRAY_N);
        if (!is_array($result) ) {
            return $port;
        }
        if (empty($result[0])) {
            return $port;
        }
        $data = $result[0];
        if (!is_array($data)) {
            return $port;
        }
        if (empty($data[1]) ) {
            return $port;
        }        
        $computed_port = (int)$data[1];
        if ($computed_port) {
            return $computed_port;
        }
       
        return $port;
    }
    
    /**
     * Clean up large array after dB dump.
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverDatabaseUtilities::itCleansUpDbRetArrayDump()
     * @param array $ret
     * @return array
     */
    public function cleanUpDbRetArrayDump($ret = [])
    {        
        if (!empty($ret['tbl_primary_keys'])) {
            unset($ret['tbl_primary_keys']);
        }        
        
        return $ret;
    }
    /**
     * Db primary keys
     * @param array $keys
     * @param array $ret
     * @param string $table
     * @return array
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverDatabaseUtilities::itDumpsDbPrimaryKeys()
     */
    public function dBPrimaryKeysDump($keys = [], $ret = [], $table = '')
    {
        $primary_keys = [];
        $orderby_keys = [];
        if (!empty($ret['tbl_primary_keys'][$table]['primary_keys'])) {
            $primary_keys = $ret['tbl_primary_keys'][$table]['primary_keys'];
        } elseif (!empty($ret['tbl_primary_keys'][$table]['orderby'])) {
            $orderby_keys = $ret['tbl_primary_keys'][$table]['orderby'];
        }
        
        return [$primary_keys, $orderby_keys];        
    }
    
    /**
     * Compute primary keys for tables
     * Used for MySQLdump seek method implementation
     * @param array $ret
     * @param array $clean_tables
     * @return array
     */
    public function computePrimaryKeys($ret = [], $clean_tables = [])
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized()) {
            return $ret;
        }
        
        if (isset($ret['tbl_primary_keys'])) {
            return $ret;
        }        
        $primary_keys = [];
        foreach ($clean_tables as $table) {   
            $res = $this->queryPrimaryKeys($table);
            if (empty($res)) {
                $primary_keys = $this->processOrderByKeys($table, $primary_keys);                
            } else {
                $primary_keys = $this->processPrimaryKeys($res, $table, $primary_keys);
            }
        }
        if (!empty($primary_keys)) {
            $ret['tbl_primary_keys'] = $primary_keys;
        }        
        return $ret;
    }
    
    /**
     * Query primary keys given table
     * @param string $table
     * @return array|object|NULL
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverDatabaseUtilities::itQueriesPrimaryKeys()
     */
    protected function queryPrimaryKeys($table = '')
    {
        global $wpdb;
        return $wpdb->get_results("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY' OR (Non_unique = 0 AND `Null` = '')", ARRAY_A);  
    }
    
    /**
     * Process order by keys
     * @param string $table
     * @param array $primary_keys
     */
    protected function processOrderByKeys($table = '', $primary_keys = [])
    {
        global $wpdb;
        $columns = $wpdb->get_results("SHOW COLUMNS FROM `{$table}`", ARRAY_A);  
        $orderbycolumn = '';
        foreach ($columns as $column) {
            if (!empty($column['Field']) && !empty($column['Null']) && 'NO' === $column['Null']) {                
                $orderbycolumn = $column['Field'];
                $orderbycolumn = "`${orderbycolumn}`";
                $primary_keys[$table] = ['orderby' => [$orderbycolumn]];
                
                break;
            }
        }
        
        return $primary_keys;
    }
    
    /**
     * Process primary keys
     * @param array $res
     * @param string $table
     * @param array $primary_keys
     * @return array
     */
    protected function processPrimaryKeys($res = [], $table = '', $primary_keys = [])
    {
        $keys = [];
        $uniquecol = [];        
        foreach ($res as $val) {
            
            $pri = false;
            $uniquecolumn = false;
            $column = '';
            
            if (!empty($val['Key_name']) && 'PRIMARY' === $val['Key_name']) {
                $pri = true;
            } else {
                $uniquecolumn = true;                
            }           
            if (isset($val['Column_name'])) {
                $column = $val['Column_name'];
            }
            if ($column && $pri) {               
                $keys[] = "`${column}`";
                
            } elseif ($column && $uniquecolumn) {
                $uniquecol[] = "`${column}`";
            }
        }
       
        if (empty($keys)) {
            $primary_keys[$table] = ['primary_keys' => $uniquecol];
        } else {
            $primary_keys[$table] = ['primary_keys' => $keys];
        }
        
        return $primary_keys;
    }
    
    /**
     * Initialize get maybe randomized db prefix
     * Hooked to `prime_mover_before_mysqldump_php` ACTION executed before PHP-MySQL dump process to set reusable object properties
     * @param array $ret
     * @return array
     */
    public function initializeGetMaybeRandomizeDbPrefix($ret = [])
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized()) {
            return $ret;
        }
        $this->getmayberandomizedbprefix = $this->getSystemInitialization()->getMaybeRandomizeDbPrefix($ret);
        return $ret;        
    }
 
    /**
     * Initialize get db prefix of site
     * Hooked to `prime_mover_before_mysqldump_php` ACTION executed before PHP-MySQL dump process to set reusable object properties
     * @param array $ret
     * @return array
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverDatabaseUtilities::itInitializeGetDbPrefixOfSite()
     */
    public function initializeGetDbPrefixOfSite($ret = [])
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized()) {
            return $ret;
        }
        
        $export_blog_id = $this->getSystemInitialization()->getExportBlogID();        
        $this->dbprefix_of_site = $this->getSystemFunctions()->getDbPrefixOfSite($export_blog_id);        
        return $ret;
    }
    
    /**
     * Initialize random prefix
     * @param array $ret
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverDatabaseUtilities::itInitializeRandomPrefix()
     * Hooked to `prime_mover_before_mysqldump_php` ACTION executed before PHP-MySQL dump process to set reusable object properties
     * @return array
     */
    public function initializeRandomPrefix($ret = [])
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized()) {
            return $ret;
        }
        $this->random_prefix = $this->getSystemInitialization()->getRandomPrefix($ret);        
        return $ret;
    }
    
    /**
     * Initialize enc key
     * @param array $ret
     * Hooked to `prime_mover_before_mysqldump_php` ACTION executed before PHP-MySQL dump process to set reusable object properties
     * @return array
     */
    public function initializeEncKey($ret = [])
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized()) {
            return $ret;
        }
        $this->db_encryption_key = $this->getSystemInitialization()->getDbEncryptionKey();        
        return $ret;
    }
    
    /**
     * 
     * Initialize maybe enc data
     * @param array $ret
     * Hooked to `prime_mover_before_mysqldump_php` ACTION executed before PHP-MySQL dump process to set reusable object properties
     * @return array
     */
    public function initializeMaybeEnc($ret = [])
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized()) {
            return $$ret;
        }
        
        $this->maybe_enc = $this->getSystemInitialization()->getMaybeEncryptExportData($ret);        
        return $ret;
    }
 
    /**
     * Randomize dB prefix on export
     * @param string $data
     * @return string
     * Hooked to `prime_mover_filter_export_db_data` filter
     */
    public function randomizeDbPrefix($data = '')
    {
        if (!$this->getMaybeRandomizedDbPrefix()) {
            return $data;
        }
        
        $current_prefix = $this->getDbPrefixOfSite();
        $updated_prefix = $this->getRandomPrefix();
        $random_prefix = "`$updated_prefix";
        
        return str_replace("`$current_prefix", $random_prefix, $data);
    }

    /**
     * Update user role to random prefix
     * @param string $data
     * @return string
     * @updated 1.0.6
     * Hooked to `prime_mover_filter_export_db_data` filter
     */
    public function updateUserRoleToRandomPrefix($data = '')
    {
        if (!$this->getMaybeRandomizedDbPrefix()) {
            return $data;
        }
        
        $current_prefix = $this->getDbPrefixOfSite();
        $updated_prefix = $this->getRandomPrefix();
        
        $current_role_option = $current_prefix . 'user_roles';
        $updated_role_option = $updated_prefix . 'user_roles';
        
        $search = "'$current_role_option',";
        $replace = "'$updated_role_option',";
        
        return str_replace($search, $replace, $data);
    }
    
    /**
     * Encrypt data
     * @param string $data
     * @return string
     * Hooked to `prime_mover_filter_export_db_data` filter
     */
    public function encryptData($data = '')
    {
        return $this->getOpenSSLUtilities()->encryptData($data, $this->getDbEncKey(), $this->getMaybeEncData());
    }
    
    /**
     * Get system functions
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMoverSystemFunctions
     */
    public function getSystemFunctions()
    {
        return $this->getPrimeMover()->getSystemFunctions();
    }
    
    /**
     * Get maybe enc data
     * @return boolean
     */
    public function getMaybeEncData()
    {
        return $this->maybe_enc;
    }
    
    /**
     * Get db enc key
     * @return string
     */
    public function getDbEncKey()
    {
        return $this->db_encryption_key;
    }
    
    /**
     * Get db prefix of current site
     * @return string
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverDatabaseUtilities::itInitializeGetDbPrefixOfSite()
     */
    public function getDbPrefixOfSite()
    {
        return $this->dbprefix_of_site;
    }
    
    /**
     * Get random prefix
     * @return string
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverDatabaseUtilities::itInitializeRandomPrefix()
     */
    public function getRandomPrefix()
    {
        return $this->random_prefix;
    }
    
    /**
     * Get system initialization
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMoverSystemInitialization
     */
    public function getSystemInitialization()
    {
        return $this->getPrimeMover()->getSystemInitialization();
    }
    
    /**
     * Get maybe randomized db prefix
     * @return boolean
     */
    public function getMaybeRandomizedDbPrefix()
    {
        return $this->getmayberandomizedbprefix;
    }    
}