<?php
namespace Codexonics\PrimeMoverFramework\compatibility;

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
use SplFixedArray;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Prime Mover WooCommerce Compatibility Class
 * Helper class for interacting with WooCommerce plugin
 *
 */
class PrimeMoverWooCommerceCompat
{     
    private $prime_mover;
    private $woocommerce_plugin;
    private $wc_order_posttype;
    
    /**
     * Construct
     * @param PrimeMover $prime_mover
     * @param array $utilities
     */
    public function __construct(PrimeMover $prime_mover, $utilities = [])
    {
        $this->prime_mover = $prime_mover;
        $this->woocommerce_plugin = 'woocommerce/woocommerce.php';
        $this->wc_order_posttype = 'shop_order';
    }
        
    /**
     * Get order post type
     * @return string
     */
    public function getOrderPostType()
    {
        return $this->wc_order_posttype;
    }
    
    /**
     * Get WooCommerce plugin
     * @return string
     */
    public function getWooCommercePlugin()
    {
        return $this->woocommerce_plugin;
    }
    
    /**
     * Get Prime Mover instance
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMover
     */
    public function getPrimeMover()
    {
        return $this->prime_mover;
    }
    
    /**
     * Get hooked methods
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMoverHookedMethods
     */
    public function getPrimeMoverHookedMethods()
    {
        return $this->getPrimeMover()->getHookedMethods();
    }
    
    /**
     * Get progess handlers
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMoverProgressHandlers
     */
    public function getProgressHandlers()
    {
        return $this->getPrimeMoverHookedMethods()->getProgressHandlers();
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
     * Get system authorization
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMoverSystemAuthorization
     */
    public function getSystemAuthorization()
    {
        return $this->getPrimeMover()->getSystemAuthorization();
    }
 
    /**
     * Get user queries
     * @return \Codexonics\PrimeMoverFramework\users\PrimeMoverUserQueries
     */
    public function getUserQueries()
    {
        return $this->getPrimeMover()->getImporter()->getUsersObject()->getUserUtilities()->getUserFunctions()->getUserQueries();
    }
    
    /**
     * Initialize hooks
     */
    public function initHooks()
    {
        add_action('prime_mover_before_post_author_update', [$this, 'maybeReconnectWcOrdersWithMigratedUsers'], 10, 6);
        add_filter('prime_mover_do_process_thirdparty_data', [$this, 'maybeAdjustUserIdsCustomerLookup'], 10, 3);         
        add_filter('prime_mover_do_process_thirdparty_data', [$this, 'maybeAdjustUserIdsDownloadPermissions'], 11, 3); 
        
        add_filter('prime_mover_do_process_thirdparty_data', [$this, 'maybeAdjustUserIdsDownloadLog'], 12, 3);        
        add_filter('prime_mover_do_process_thirdparty_data', [$this, 'maybeAdjustUserIdsApiKeys'], 13, 3);
        add_filter('prime_mover_do_process_thirdparty_data', [$this, 'maybeAdjustUserIdsWebHooks'], 14, 3);
        
        add_filter('prime_mover_do_process_thirdparty_data', [$this, 'maybeAdjustUserIdsPaymentTokens'], 15, 3);
    } 

    /**
     * Adjust customer Ids in payment tokens table
     * Hooked to `prime_mover_do_process_thirdparty_data` filter - priority 15
     * @param array $ret
     * @param number $blogid_to_import
     * @param number $start_time
     * @return array
     */
    public function maybeAdjustUserIdsPaymentTokens($ret = [], $blogid_to_import = 0, $start_time = 0)
    {
        $validation_error = $this->getUserQueries()->maybeRequisitesNotMeetForAdjustment($ret, $blogid_to_import, $this->getWooCommercePlugin());
        if (is_array($validation_error)) {
            return $validation_error;
        }
        
        if (!empty($ret['3rdparty_current_function']) && __FUNCTION__ !== $ret['3rdparty_current_function']) {
            return $ret;
        }
        
        $ret['3rdparty_current_function'] = __FUNCTION__;
        $table = 'woocommerce_payment_tokens';
        $leftoff_identifier = '3rdparty_paymenttokens_leftoff';
        
        $primary_index = 'token_id';
        $column_strings = 'token_id, user_id';
        $update_variable = '3rdparty_paymenttokens_log_updated';
        
        $progress_identifier = 'payment tokens table';
        $last_processor = true;
        
        return $this->getUserQueries()->dBCustomerUserIdsHelper($ret, $table, $blogid_to_import, $leftoff_identifier, $primary_index, $column_strings,
            $update_variable, $progress_identifier, $start_time, $last_processor);
    }
    
    /**
     * Adjust customer Ids in WC webhooks table
     * Hooked to `prime_mover_do_process_thirdparty_data` filter - priority 14
     * @param array $ret
     * @param number $blogid_to_import
     * @param number $start_time
     * @return array
     */
    public function maybeAdjustUserIdsWebHooks($ret = [], $blogid_to_import = 0, $start_time = 0)
    {
        $validation_error = $this->getUserQueries()->maybeRequisitesNotMeetForAdjustment($ret, $blogid_to_import, $this->getWooCommercePlugin());
        if (is_array($validation_error)) {
            return $validation_error;
        }
        
        if (!empty($ret['3rdparty_current_function']) && __FUNCTION__ !== $ret['3rdparty_current_function']) {
            return $ret;
        }
        
        $ret['3rdparty_current_function'] = __FUNCTION__;
        $table = 'wc_webhooks';
        $leftoff_identifier = '3rdparty_webhooks_leftoff';
        
        $primary_index = 'webhook_id';
        $column_strings = 'webhook_id, user_id';
        $update_variable = '3rdparty_webhooks_log_updated';
        
        $progress_identifier = 'web hooks table';
        $last_processor = false;
        
        return $this->getUserQueries()->dBCustomerUserIdsHelper($ret, $table, $blogid_to_import, $leftoff_identifier, $primary_index, $column_strings,
            $update_variable, $progress_identifier, $start_time, $last_processor);
    }
    
    /**
     * Adjust customer Ids in WC API keys table
     * Hooked to `prime_mover_do_process_thirdparty_data` filter - priority 13
     * @param array $ret
     * @param number $blogid_to_import
     * @param number $start_time
     * @return array
     */
    public function maybeAdjustUserIdsApiKeys($ret = [], $blogid_to_import = 0, $start_time = 0)
    {
        $validation_error = $this->getUserQueries()->maybeRequisitesNotMeetForAdjustment($ret, $blogid_to_import, $this->getWooCommercePlugin());
        if (is_array($validation_error)) {
            return $validation_error;
        }
        
        if (!empty($ret['3rdparty_current_function']) && __FUNCTION__ !== $ret['3rdparty_current_function']) {
            return $ret;
        }
        
        $ret['3rdparty_current_function'] = __FUNCTION__;
        $table = 'woocommerce_api_keys';
        $leftoff_identifier = '3rdparty_apikeys_leftoff';
        
        $primary_index = 'key_id';
        $column_strings = 'key_id, user_id';
        $update_variable = '3rdparty_apikeys_log_updated';
        
        $progress_identifier = 'api keys table';
        $last_processor = false;
        
        return $this->getUserQueries()->dBCustomerUserIdsHelper($ret, $table, $blogid_to_import, $leftoff_identifier, $primary_index, $column_strings,
            $update_variable, $progress_identifier, $start_time, $last_processor);
    }
    
    /**
     * Adjust customer Ids in WC download log table
     * Hooked to `prime_mover_do_process_thirdparty_data` filter - priority 12
     * @param array $ret
     * @param number $blogid_to_import
     * @param number $start_time
     * @return array
     */
    public function maybeAdjustUserIdsDownloadLog($ret = [], $blogid_to_import = 0, $start_time = 0)
    {
        $validation_error = $this->getUserQueries()->maybeRequisitesNotMeetForAdjustment($ret, $blogid_to_import, $this->getWooCommercePlugin());
        if (is_array($validation_error)) {
            return $validation_error;
        }
        
        if (!empty($ret['3rdparty_current_function']) && __FUNCTION__ !== $ret['3rdparty_current_function']) {
            return $ret;
        }
        
        $ret['3rdparty_current_function'] = __FUNCTION__;
        $table = 'wc_download_log';
        $leftoff_identifier = '3rdparty_dl_log_leftoff';
        
        $primary_index = 'download_log_id';
        $column_strings = 'download_log_id, user_id';
        $update_variable = '3rdparty_dl_log_updated';
        
        $progress_identifier = 'download log table';
        $last_processor = false;
        
        return $this->getUserQueries()->dBCustomerUserIdsHelper($ret, $table, $blogid_to_import, $leftoff_identifier, $primary_index, $column_strings,
            $update_variable, $progress_identifier, $start_time, $last_processor);
    }
    
    /**
     * Adjust customer Ids in WC download permissions table
     * Hooked to `prime_mover_do_process_thirdparty_data` filter - priority 11
     * @param array $ret
     * @param number $blogid_to_import
     * @param number $start_time
     * @return array
     */
    public function maybeAdjustUserIdsDownloadPermissions($ret = [], $blogid_to_import = 0, $start_time = 0)
    {
        $validation_error = $this->getUserQueries()->maybeRequisitesNotMeetForAdjustment($ret, $blogid_to_import, $this->getWooCommercePlugin());
        if (is_array($validation_error)) {
            return $validation_error;
        }
        
        if (!empty($ret['3rdparty_current_function']) && __FUNCTION__ !== $ret['3rdparty_current_function']) {
            return $ret;
        }
        
        $ret['3rdparty_current_function'] = __FUNCTION__;
        $table = 'woocommerce_downloadable_product_permissions';
        $leftoff_identifier = '3rdparty_cust_dl_leftoff';
        
        $primary_index = 'permission_id';
        $column_strings = 'permission_id, user_id';
        $update_variable = '3rdparty_cust_dl_updated';
        
        $progress_identifier = 'download permissions table';
        $last_processor = false;
        
        return $this->getUserQueries()->dBCustomerUserIdsHelper($ret, $table, $blogid_to_import, $leftoff_identifier, $primary_index, $column_strings,
            $update_variable, $progress_identifier, $start_time, $last_processor);
    }
    
    /**
     * Adjust customer Ids in WC customer lookup table
     * Hooked to `prime_mover_do_process_thirdparty_data` filter - priority 10
     * @param array $ret
     * @param number $blogid_to_import
     * @param number $start_time
     * @return array
     */
    public function maybeAdjustUserIdsCustomerLookup($ret = [], $blogid_to_import = 0, $start_time = 0)
    {        
        $validation_error = $this->getUserQueries()->maybeRequisitesNotMeetForAdjustment($ret, $blogid_to_import, $this->getWooCommercePlugin());
        if (is_array($validation_error)) {
            return $validation_error;
        }
        
        if (!empty($ret['3rdparty_current_function']) && __FUNCTION__ !== $ret['3rdparty_current_function']) {
            return $ret;
        }
        
        $ret['3rdparty_current_function'] = __FUNCTION__; 
        $table = 'wc_customer_lookup';
        $leftoff_identifier = '3rdparty_customers_leftoff';
        
        $primary_index = 'customer_id';
        $column_strings = 'customer_id, user_id';
        $update_variable = '3rdparty_customers_updated';
        
        $progress_identifier = 'customers lookup table';
        $last_processor = false;       
        $handle_unique_constraint = 'user_id';
        
        return $this->getUserQueries()->dBCustomerUserIdsHelper($ret, $table, $blogid_to_import, $leftoff_identifier, $primary_index, $column_strings,
            $update_variable, $progress_identifier, $start_time, $last_processor, $handle_unique_constraint);
    }
            
    /**
     * Maybe reconnect WC Orders with migrated users
     * Hooked to `prime_mover_before_post_author_update` action.
     * @param number $post_id
     * @param number $post_author
     * @param number $new_author
     * @param array $ret
     * @param SplFixedArray $user_equivalence
     * @param string $post_type
     */
    public function maybeReconnectWcOrdersWithMigratedUsers($post_id = 0, $post_author = 0, $new_author = 0, $ret = [], SplFixedArray $user_equivalence, $post_type = '')
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized()) {
            return;
        }
        
        if (!isset($ret['imported_package_footprint']['plugins'][$this->getWooCommercePlugin()])) {
            return;
        }
           
        if (!$post_type || $this->getOrderPostType() !== $post_type) {
            return;
        }
        
        $unadjusted_customerid = get_post_meta($post_id, '_customer_user', true);
        $unadjusted_customerid = (int)$unadjusted_customerid;
        if (!$unadjusted_customerid) {
            return;
        }
        
        if (!isset($user_equivalence[$unadjusted_customerid])) {
       
            return;
        }
        
        $migrated_customer_id = (int)$user_equivalence[$unadjusted_customerid];
        if (!$migrated_customer_id) {
            return;
        }
        
        if ($unadjusted_customerid === $migrated_customer_id) {
            return;
        }
                      
        update_post_meta($post_id, '_customer_user', $migrated_customer_id);        
    }
}