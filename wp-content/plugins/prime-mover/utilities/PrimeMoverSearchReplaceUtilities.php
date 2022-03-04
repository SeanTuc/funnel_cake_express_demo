<?php
namespace Codexonics\PrimeMoverFramework\utilities;

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
 * Prime Mover Search and Replace
 * Handles edge cases in search and replace functionality
 *
 */
class PrimeMoverSearchReplaceUtilities
{
    private $prime_mover;    
    private $slashed_replaceables;
    private $missing_backlash;
    private $generics;
    private $url_translators;
    
    /**
     * Constructor
     * @param PrimeMover $prime_mover
     */
    public function __construct(PrimeMover $prime_mover)
    {
        $this->prime_mover = $prime_mover;
        $this->slashed_replaceables = [
            'wpupload_path',
            'wpupload_url',
            'generic_alternative_upload_scheme',
            'generic_upload_scheme',
            'alternative_wpupload_url',
            'alternative_edge_wpupload_url',
            'wpupload_url_alternative', 
            'wproot_slash_appended',
            'wpcontent_urls',
            'generic_content_scheme',
            'alt_wpcontent_urls',
            'wpcontent_dirs',
            'removed_trailing_slash_wproot',
            'scheme_replace',
            'scheme_replace_domain_current_site',
            'legacybase_url',
            'httpblogsdir_url',            
            'httpfiles_url',
            'generic_legacy_upload_scheme',
            'wpupload_url_compat',
            'wpupload_url_mixed_content',
            'wpupload_url_alt_mixed_content',
            'domain_replace_uri_http',
            'domain_replace_uri_https',
            'generic_domain_scheme'            
        ];
        
        $this->missing_backlash = ['alternative_wpupload_url', 'alternative_edge_wpupload_url', 'wpupload_url'];
        $this->generics = [
            'generic_legacy_upload_scheme' => 'generic_legacy_upload_scheme_slashedvar',
            'generic_alternative_upload_scheme' => 'generic_alternative_upload_scheme_slashedvar',
            'generic_upload_scheme' => 'generic_upload_scheme_slashedvar',
            'generic_content_scheme' => 'generic_content_scheme_slashedvar'            
        ];
        
        $this->url_translators = [
            'generic_legacy_upload_scheme' => [
                'legacybase_url' => 'legacybase_url_slashedvar',
                'httpfiles_url' => 'httpfiles_url_slashedvar'
            ],  
            'generic_alternative_upload_scheme' => [
                'alternative_wpupload_url' => 'alternative_wpupload_url_slashedvar',
                'wpupload_url_alt_mixed_content' => 'wpupload_url_alt_mixed_content_slashedvar'
            ],  
            'generic_upload_scheme' => [
                'wpupload_url' => 'wpupload_url_slashedvar',
                'legacybase_url]' => 'legacybase_url_slashedvar',
                'httpblogsdir_url' => 'httpblogsdir_url_slashedvar',
                'httpfiles_url' => 'httpfiles_url_slashedvar',
                'wpupload_url_mixed_content' => 'wpupload_url_mixed_content_slashedvar',
                'alternative_wpupload_url' => 'alternative_wpupload_url_slashedvar',
                'wpupload_url_alt_mixed_content' => 'wpupload_url_alt_mixed_content_slashedvar'
            ],
            'generic_content_scheme' => [                
                'wpcontent_urls' => 'wpcontent_urls_slashedvar',
                'alt_wpcontent_urls' => 'alt_wpcontent_urls_slashedvar'
            ]
        ]; 
    }
    
    /**
     * Get URL translators
     * @return string[][]
     */
    public function getUrlTranslators()
    {
        return $this->url_translators;
    }
    
    /**
     * Get generic replaceables paths
     * @return string[]
     */
    public function getGenerics()
    {
        return $this->generics;
    }
    
    /**
     * Get slashed replaceables
     * @return string[]
     */
    public function getSlashedReplaceables()
    {
        return $this->slashed_replaceables;
    }
    
    /**
     * Get missing slash vars
     * @return string[]
     */
    public function getMissingSlashVars()
    {
        return $this->missing_backlash;
    }
    
    /**
     * Get Prime Mover
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
     * Get System Initialization
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMoverSystemInitialization
     */
    public function getSystemInitialization()
    {
        return $this->getPrimeMover()->getSystemInitialization();
    }
    
    /**
     * Get system check utilities
     * @return \Codexonics\PrimeMoverFramework\utilities\PrimeMoverSystemCheckUtilities
     */
    public function getSystemCheckUtilities()
    {
        return $this->getPrimeMover()->getSystemChecks()->getSystemCheckUtilities();    
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
     * Init hooks
     * @compatible 5.6
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverSearchReplaceUtilities::itAddsInitHooks() 
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverSearchReplaceUtilities::itChecksIfHooksAreOutdated() 
     */
    public function initHooks()
    {
        add_filter('prime_mover_filter_replaceables', [$this, 'adjustLegacySSLReplace'], 999, 2);
        add_filter('prime_mover_skip_search_replace', [$this, 'skipSearchReplaceWhenSiteParametersSame'], 9999, 4);
        add_filter('prime_mover_filter_upload_phrase', [$this, 'customWpContentCompatibility'], 9999, 3);
        
        add_filter('prime_mover_filter_export_footprint', [$this, 'addAlternativeUploadInfoToSystemFootprint'], 1000, 1);
        add_filter('prime_mover_filter_upload_phrase', [$this, 'maybeAdjustForMixedContent'], 1, 4);
        
        add_filter('prime_mover_filter_upload_phrase', [$this, 'adjustLegacyRootSearchReplace'], 999, 5);
        add_filter('prime_mover_filter_final_replaceables', [$this, 'pageBuildersCompatibility'], 10, 4);
        add_filter('prime_mover_filter_export_footprint', [$this, 'addWpContentdInfoToExportFootprint'], 2000, 3);
        
        add_filter('prime_mover_append_edge_builder_replaceables', [$this, 'handleMissingBackSlash'], 10, 4);
        add_filter('prime_mover_input_footprint_package_array', [$this, 'normalizeUploadInformationPath'], 10, 1);
        add_filter('prime_mover_filter_export_footprint', [$this, 'addEdgeCanonicalUploadInfoToFootprint'], 1500, 1);
        
        add_filter('prime_mover_filter_replaceables', [$this, 'maybeAddDomainReplaceUri'], 11, 2);
        add_filter('prime_mover_filter_final_replaceables', [$this, 'maybeRemoveBroadDomainReplaceProtocol'], 9999, 4);
        add_filter('prime_mover_filter_upload_phrase', [$this, 'genericUploadProtocolCompat'], 9998, 3);
        
        add_filter('prime_mover_filter_final_replaceables', [$this, 'useOnlyGenericVersions'], 10000, 4);
    }
 
    /**
     * Analyze replacebles and return only what is necessary
     * Designed to be quick and easy
     * @param array $replaceables
     * @return array
     */
    protected function maybeUseGenericVersions($replaceables = [])
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized()) {
            return $replaceables;
        }
        
        $generics = array_keys($this->getGenerics());
        $url_translators = $this->getUrlTranslators();
        foreach ($generics as $generic_key) {
            if (empty($replaceables[$generic_key]['search']) || empty($replaceables[$generic_key]['replace'])) {
                continue;
            }
            
            $generic_srch = $this->getSystemFunctions()->removeSchemeFromUrl($replaceables[$generic_key]['search']);
            if (!isset($url_translators[$generic_key]))  {
                continue;
            }
            
            $translators = $url_translators[$generic_key];
            foreach ($translators as $translator_key => $translator_slashed_var) {
                if (!isset($replaceables[$translator_key]) || !isset($replaceables[$translator_slashed_var])) {
                    continue;
                }
                if (!isset($replaceables[$translator_key]['search']) || !isset($replaceables[$translator_key]['replace'])) {
                    continue;
                }
                
                $translator_srch = $this->getSystemFunctions()->removeSchemeFromUrl($replaceables[$translator_key]['search']);                
                if ($translator_srch === $generic_srch) {
                    unset($replaceables[$translator_key]);
                    unset($replaceables[$translator_slashed_var]);
                }
            }            
        }
     
        return $replaceables;        
    }
        
    /**
     * Use only generic versions to prevent replacement issues and improve performance
     * @param array $replaceables
     * @param array $ret
     * @param boolean $retries
     * @param number $blogid_to_import
     */
    public function useOnlyGenericVersions($replaceables = [], $ret = [], $retries = false, $blogid_to_import = 0)
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized()) {
            return $replaceables;
        }
        
        $replaceables = $this->maybeUseGenericVersions($replaceables);                
       
        if (isset($replaceables['domain_replace_uri_http'])) {
            unset($replaceables['domain_replace_uri_http']);
        }
        
        if (isset($replaceables['domain_replace_uri_http_slashedvar'])) {
            unset($replaceables['domain_replace_uri_http_slashedvar']);
        }
        
        if (isset($replaceables['domain_replace_uri_https'])) {
            unset($replaceables['domain_replace_uri_https']);
        }
 
        if (isset($replaceables['domain_replace_uri_https_slashedvar'])) {
            unset($replaceables['domain_replace_uri_https_slashedvar']);
        }
        
        return $this->maybeUnsetRedundantGenerics($replaceables);
    }
    
    /**
     * Check and unset redundant generics due to same domain migration.
     * @param array $replaceables
     * @return array
     */
    protected function maybeUnsetRedundantGenerics($replaceables = [])
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized()) {
            return $replaceables;
        }
        if (empty($replaceables['generic_domain_scheme']['search']) || empty($replaceables['generic_domain_scheme']['replace'])) {
            return $replaceables;
        }
        
        $generic_srch = $replaceables['generic_domain_scheme']['search'];
        $generic_rplc = $replaceables['generic_domain_scheme']['replace'];
        
        foreach ($this->getGenerics() as $generic_key => $slashed_var) {
            if (isset($replaceables[$generic_key]) &&
                !empty($replaceables[$generic_key]['search']) &&
                !empty($replaceables[$generic_key]['replace']) &&
                str_replace($generic_srch, "", $replaceables[$generic_key]['search']) === str_replace($generic_rplc, "", $replaceables[$generic_key]['replace']))
            {             
                if (isset($replaceables[$generic_key])) {
                    unset($replaceables[$generic_key]);
                }                
                if (isset($replaceables[$slashed_var])) {
                    unset($replaceables[$slashed_var]);
                }                
            }
        }
        
        return $this->maybeUnsetAltUploadReplaceables($replaceables);
    }
    
    /**
     * Maybe unset alt upload replaceables
     * @param array $replaceables
     * @return array
     */
    protected function maybeUnsetAltUploadReplaceables($replaceables = [])
    {
        $alt_unset = false;
        if (isset($replaceables['generic_alternative_upload_scheme']) && isset($replaceables['alternative_wpupload_url']) && isset($replaceables['wpupload_url_alt_mixed_content'])) {
            unset($replaceables['alternative_wpupload_url']);
            unset($replaceables['wpupload_url_alt_mixed_content']);
            $alt_unset = true;
        }
        
        if ($alt_unset && isset($replaceables['alternative_wpupload_url_slashedvar']) && isset($replaceables['wpupload_url_alt_mixed_content_slashedvar'])) {
            unset($replaceables['alternative_wpupload_url_slashedvar']);
            unset($replaceables['wpupload_url_alt_mixed_content_slashedvar']);
        }     
        
        $legacy_unset = false;
        if (isset($replaceables['generic_legacy_upload_scheme']) && isset($replaceables['legacybase_url']) && isset($replaceables['httpfiles_url'])) {
            unset($replaceables['legacybase_url']);
            unset($replaceables['httpfiles_url']);
            $legacy_unset = true;
        }
        
        if ($legacy_unset && isset($replaceables['legacybase_url_slashedvar']) && isset($replaceables['httpfiles_url_slashedvar'])) {
            unset($replaceables['legacybase_url_slashedvar']);
            unset($replaceables['httpfiles_url_slashedvar']);
        }          
        
        return $replaceables;
    }
    
    /**
     * Generic upload protocol compatibility search and replace protocol
     * @param array $upload_phrase
     * @param array $ret
     * @param array $replaceables
     * @return array
     */
    public function genericUploadProtocolCompat($upload_phrase = [], $ret = [], $replaceables = [])
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized() || empty($upload_phrase)) {
            return $upload_phrase;
        }
        
        if (empty($upload_phrase['wpupload_url'])) {
            return $upload_phrase;
        }
        
        if (empty($upload_phrase['wpupload_url']['search']) || empty($upload_phrase['wpupload_url']['replace'])) {
            return $upload_phrase;
        }
        
        $search = $this->getSystemFunctions()->removeSchemeFromUrl($upload_phrase['wpupload_url']['search']);
        $replace = $this->getSystemFunctions()->removeSchemeFromUrl($upload_phrase['wpupload_url']['replace']);
        
        $compute_alternative = true;                 
        if (empty($upload_phrase['alternative_wpupload_url'])) {
            $compute_alternative = false;
        }
        
        if (!isset($upload_phrase['alternative_wpupload_url']['search']) || !isset($upload_phrase['alternative_wpupload_url']['replace'])) {
            $compute_alternative = false;
        }  

        if ($compute_alternative) {
            $alt_wpupload_srch = $this->getSystemFunctions()->removeSchemeFromUrl($upload_phrase['alternative_wpupload_url']['search']);
            $alt_wpupload_rplc = $this->getSystemFunctions()->removeSchemeFromUrl($upload_phrase['alternative_wpupload_url']['replace']);
        }        
        
        if ($compute_alternative && $replace !== $alt_wpupload_rplc) {
            $compute_alternative = false;
        }
        
        if ($compute_alternative && $alt_wpupload_srch !== $search) {
            $upload_phrase['generic_alternative_upload_scheme'] = [
                'search' => "//" . $alt_wpupload_srch,
                'replace' => "//" . $replace
            ];
        }
        
        $upload_phrase['generic_upload_scheme'] = [
            'search' => "//" . $search,
            'replace' => "//" . $replace
        ]; 
        
        return $upload_phrase;
    }
    
    /**
     * Maybe remove broad domain replace protocol
     * @param array $replaceables
     * @param array $ret
     * @param boolean $retries
     * @param number $blogid_to_import
     * @return array
     */
    public function maybeRemoveBroadDomainReplaceProtocol($replaceables = [], $ret = [], $retries = false, $blogid_to_import = 0)
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized() || empty($replaceables) || !isset($replaceables['domain_replace'])) {
            return $replaceables;
        }
        if (defined('PRIME_MOVER_FORCE_DOMAIN_REPLACE') && PRIME_MOVER_FORCE_DOMAIN_REPLACE) {
            return $replaceables;
        }
        
        unset($replaceables['domain_replace']);
        return $replaceables;
    }
    
    /**
     * Maybe add domain replace URI in search - replace protocol
     * @param array $replaceables
     * @param array $ret
     * @return array
     */
    public function maybeAddDomainReplaceUri($replaceables = [], $ret = [])
    {
        if (defined('PRIME_MOVER_FORCE_DOMAIN_REPLACE') && PRIME_MOVER_FORCE_DOMAIN_REPLACE) {
            return $replaceables;
        }
        
        $target_key = 'domain_replace';        
        if (!$this->getSystemAuthorization()->isUserAuthorized() || empty($replaceables) || empty($replaceables[$target_key]) || empty($ret['imported_package_footprint']['scheme'])) {
            return $replaceables;
        }
        
        $source_scheme = $ret['imported_package_footprint']['scheme'];
        $source_site = $replaceables['domain_replace']['search'];
        $target_site = $replaceables['domain_replace']['replace'];        
        
        $source_site_http = PRIME_MOVER_NON_SECURE_PROTOCOL . $source_site;
        $source_site_https = PRIME_MOVER_SECURE_PROTOCOL . $source_site;

        if (is_ssl()) {
            $target_site_scheme = PRIME_MOVER_SECURE_PROTOCOL . $target_site;
            
        } else {
            $target_site_scheme = PRIME_MOVER_NON_SECURE_PROTOCOL . $target_site;
        }
        
        $source_http = ['domain_replace_uri_http' => [
            'search' => $source_site_http,
            'replace' => $target_site_scheme
        ]];
        
        $source_https = [];
        if (PRIME_MOVER_SECURE_PROTOCOL === $source_scheme) {
            $source_https = ['domain_replace_uri_https' => [
                'search' => $source_site_https,
                'replace' => $target_site_scheme
            ]];            
        }       
        
        $offset = array_search($target_key, array_keys($replaceables)) + 1;   
        $first_block = array_slice($replaceables, 0, $offset, true);
        $second_block = $source_http;
        $third_block = $source_https;
        $fourth_block = array_slice($replaceables, $offset, null, true);
        
        $replaceables = $first_block;
        $replaceables = $replaceables + $second_block;
        if (PRIME_MOVER_SECURE_PROTOCOL === $source_scheme) {
            $replaceables = $replaceables + $third_block;
        }        
 
        $generic_domain_srch = $this->getSystemFunctions()->removeSchemeFromUrl($replaceables['domain_replace_uri_http']['search']);
        $generic_domain_rplc = $this->getSystemFunctions()->removeSchemeFromUrl($replaceables['domain_replace_uri_http']['replace']);
        
        $generic_domain_phrase = ['generic_domain_scheme' => [
            'search' => "//" . $generic_domain_srch,
            'replace' => "//" . $generic_domain_rplc
        ]];         
        
        $replaceables = $replaceables + $generic_domain_phrase + $fourth_block;       
        return $replaceables;
    }
    
    /**
     * Custom wp-content compatibility search and replace protocol
     * This is used on restore end.
     * @param array $upload_phrase
     * @param array $ret
     * @param array $replaceables
     * @return array
     */
    public function customWpContentCompatibility($upload_phrase = [], $ret = [], $replaceables = [])
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized() || empty($upload_phrase)) {
            return $upload_phrase;
        }
        
        if (empty($ret['imported_package_footprint']['source_content_url']) || empty($ret['imported_package_footprint']['source_content_dir'])) {
            return $upload_phrase;
        }
        
        list($target_content_url, $target_content_dir) = $this->getSystemFunctions()->getWpContentInfo();
        $protocol = $this->getSystemFunctions()->getUrlSchemeOfThisSite();
        $target_content_url = $protocol . $target_content_url;
               
        $source_content_url = $ret['imported_package_footprint']['source_content_url'];
        $source_content_dir = $ret['imported_package_footprint']['source_content_dir'];
        
        $upload_phrase['wpcontent_urls'] = [
            'search' => $source_content_url,
            'replace' => $target_content_url            
            ];        
        
        if (!empty($ret['imported_package_footprint']['source_alt_content_url'])) {
            $upload_phrase['alt_wpcontent_urls'] = [
                'search' => $ret['imported_package_footprint']['source_alt_content_url'],
                'replace' => $target_content_url 
            ];
        }      

        $generic_srch = $this->getSystemFunctions()->removeSchemeFromUrl($upload_phrase['wpcontent_urls']['search']);
        $generic_rplc = $this->getSystemFunctions()->removeSchemeFromUrl($upload_phrase['wpcontent_urls']['replace']);        
        
        $upload_phrase['generic_content_scheme'] = [
            'search' => "//" . $generic_srch,
            'replace' => "//" . $generic_rplc
        ];       
        
        $upload_phrase['wpcontent_dirs'] = [
            'search' => $source_content_dir,
            'replace' => $target_content_dir
        ];
        
        return $upload_phrase;
    }
    
    /**
     * Add wp-content info to export footprint
     * @param array $footprint
     * @param array $ret
     * @param number $blog_id
     * @return array
     */
    public function addWpContentdInfoToExportFootprint($footprint = [], $ret = [], $blog_id = 0)
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized() || !$blog_id) {
            return $footprint;
        }
        if (!is_array($footprint)) {
            return $footprint;
        }
        if (empty($footprint['site_url']) || empty($footprint['scheme'])) {
            return $footprint;
        }
        list($content_url, $content_dir) = $this->getSystemFunctions()->getWpContentInfo();
        if (empty($content_url) || empty($content_dir)) {
            return $footprint;
        }
        
        $scheme = $footprint['scheme'];
        $footprint['source_content_url'] = $scheme . $content_url; 
        $footprint['source_content_dir'] = $content_dir;
        
        if (!is_multisite()) {
            return $footprint;
        }

        $main_site_url = $this->getSystemFunctions()->removeSchemeFromUrl(get_option('siteurl')); 
        $exported_site_url = $footprint['site_url'];
        if ($main_site_url === $exported_site_url) {
            return $footprint;
        }
        
        $alt_content_url = str_ireplace($main_site_url, $exported_site_url, $content_url);
        if ($alt_content_url !== $content_url) {
            $footprint['source_alt_content_url'] = $scheme . $alt_content_url; 
        }
        
        return $footprint;
    }
    
    /**
     * Add "edge" canonical upload info to system footprint
     * @param array $footprint
     * @return array
     */
    public function addEdgeCanonicalUploadInfoToFootprint($footprint = [])
    {        
        if (!is_multisite()) {
            return $footprint;
        }
        
        if (empty($footprint['alternative_upload_information_url'])) {
            return $footprint;
        }      
        
        $home = set_url_scheme(get_option('home'), 'http');
        $siteurl = set_url_scheme(get_option( 'siteurl' ), 'http');        
        $compare = strcasecmp($home, $siteurl);       
        
        if (0 === $compare) {
            return $footprint;
        }
        
        if ($compare > 0) {
            return $footprint;
        }
        
        $wp_path_rel_to_home = str_ireplace( $home, '', $siteurl ); 
        $alternative_url = $footprint['alternative_upload_information_url'];
        $edge_canonical = str_ireplace($wp_path_rel_to_home, '', $alternative_url); 
        if ($alternative_url === $edge_canonical) {
            return $footprint;
        }
        
        $footprint['edge_canonical_upload_information_url'] = $edge_canonical;         
        return $footprint;
    }
    
    /**
     * Normalize upload information path
     * @param array $system_footprint_package_array
     * @return array
     */
    public function normalizeUploadInformationPath($system_footprint_package_array = [])
    {
        if (empty($system_footprint_package_array['upload_information_path'])) {
            return $system_footprint_package_array;
        }
        
        $path = wp_normalize_path($system_footprint_package_array['upload_information_path']);        
        $system_footprint_package_array['upload_information_path'] = $path;
        
        return $system_footprint_package_array;        
    }
    
    /**
     * Missing back slash workaround
     * @param array $updated
     * @param string $key
     * @param array $replaceables
     * @param number $counter
     * @return array
     */
    public function handleMissingBackSlash($updated = [], $key = '', $replaceables = [], $counter = 0)
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized() || empty($updated) || !$key) {
            return $updated;
        }
        
        if (!in_array($key, $this->getMissingSlashVars(), true)) {            
            return $updated;
        }
        $slashed_varkey = $key . '_slashedvar';
        if (empty($replaceables['domain_replace']['search'])) {
            return $updated;
        }
        if (empty($updated[$slashed_varkey])) {
            return $updated;
        }
        if (empty($updated[$slashed_varkey]['search']) || empty($updated[$slashed_varkey]['replace'])) {
            return $updated;
        }
                
        $source_site = $replaceables['domain_replace']['search'];        
        $dummy = 'http://' . $source_site;
        $dummy = untrailingslashit(wp_normalize_path($dummy));
        
        $parsed = parse_url($dummy);
        if (empty($parsed['path'])) {
            return $updated;
        }
        
        $target_srch = str_replace('/', '\/', $source_site);
        $slashedvar = $updated[$slashed_varkey]['search'];        
        $res = str_replace($target_srch, $source_site, $slashedvar);
        if ($res === $slashedvar) {
            return $updated;
        }
        $rplc_slashed_edged = $updated[$slashed_varkey]['replace'];        
        $protocol = ['search' => $res, 'replace' => $rplc_slashed_edged];
        $new_key = $key . '_' . $counter;
        
        $updated[$new_key] = $protocol;  
        
        return $updated;
    }
    
    /**
     * General search replace page builder compatibility
     * @param array $replaceables
     * @param array $ret
     * @param boolean $retries
     * @param number $blogid_to_import
     * @return array
     */
    public function pageBuildersCompatibility($replaceables = [], $ret = [], $retries = false, $blogid_to_import = 0)
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized() || !$blogid_to_import || empty($replaceables)) {
            return $replaceables;
        }
        
        $updated = [];
        $slashed_vars = apply_filters('prime_mover_filter_slashed_replaceables', $this->getSlashedReplaceables(), $ret, $blogid_to_import);
        $counter = 0;
        foreach ($replaceables as $key => $protocol) {
            $updated[$key] = $protocol;   
            
            if (!in_array($key, $slashed_vars)) {                 
                continue;
            }
            if (!isset($protocol['search']) || !isset($protocol['replace'])) {
                continue;
            }
            
            $search = $protocol['search'];
            $replace = $protocol['replace'];
            
            $srch_slashed = str_replace('/', '\/', $search);
            $rplc_slashed = str_replace('/', '\/', $replace);
            
            $protocol = ['search' => $srch_slashed, 'replace' => $rplc_slashed]; 
            $new_key = $key . '_slashedvar';            
            $updated[$new_key] = $protocol;              
            
            $updated = apply_filters('prime_mover_append_edge_builder_replaceables', $updated, $key, $replaceables, $counter);
            $counter++;
        }
        
        if (!empty($updated)) {
            return $updated;
        }
        
        return $replaceables;
    }    
    
    /**
     * Adjust legacy root URLs search and replace edge case
     * @param array $upload_phrase
     * @param array $ret
     * @param array $replaceables
     * @param array $basic_parameters
     * @param number $blog_id
     * @return array
     */
    public function adjustLegacyRootSearchReplace($upload_phrase = [], $ret = [], $replaceables = [], $basic_parameters = [], $blog_id = 0)
    {
        if (! $this->getSystemAuthorization()->isUserAuthorized() || !$blog_id ) {
            return $upload_phrase;
        }
        
        if (!is_multisite()) {
            return $upload_phrase;            
        }
       
        if (!$this->getSystemCheckUtilities()->isLegacyMultisiteBaseURL($blog_id)) { 
            return $upload_phrase; 
        }
        
        if ($this->getSystemFunctions()->isMultisiteMainSite($blog_id)) {
            return $upload_phrase;
        }     
       
        if (empty($upload_phrase['wpupload_url']['search']) || empty($upload_phrase['wpupload_url']['replace']) ) {
            return $upload_phrase;
        }
       
        if (parse_url($upload_phrase['wpupload_url']['search'], PHP_URL_HOST) !== parse_url($upload_phrase['wpupload_url']['replace'], PHP_URL_HOST)) {
            return $upload_phrase;            
        }
       
        if (empty($ret['origin_site_url']) || empty($ret['target_site_url'])) {
            return $upload_phrase;
        }
        
        $search = $ret['origin_site_url'];
        $replace = $ret['target_site_url'];
        $subject = $replace;        
        $test = str_replace($search, $replace, $subject);
        if ($test === $replace) {
            return $upload_phrase;
        }
        
        $prev_one = $upload_phrase['wpupload_url']['replace'];
        $upload_phrase['wpupload_url']['replace'] = str_replace($subject, $search, $prev_one);         
        
        $prev_two = '';
        if (!empty($upload_phrase['wpupload_url_mixed_content']['replace'])) {
            $prev_two = $upload_phrase['wpupload_url_mixed_content']['replace'];
            $upload_phrase['wpupload_url_mixed_content']['replace'] = str_replace($subject, $search, $prev_two);
        }
        
        return $upload_phrase;
    }
    
 
    /**
     * Adjust for mixed content URLs
     * @param array $upload_phrase
     * @param array $ret
     * @param array $replaceables
     * @param array $basic_parameters
     * @return array
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverSearchReplaceUtilities::itAdjustsForMixedContent()
     */
    public function maybeAdjustForMixedContent($upload_phrase = [], $ret = [], $replaceables = [], $basic_parameters = [])
    {
        if (! $this->getSystemAuthorization()->isUserAuthorized()) {
            return $upload_phrase;
        }
        if (empty($ret['imported_package_footprint']['upload_information_url'])) {
            return $upload_phrase;
        }
        if (empty($basic_parameters)) {
            return $upload_phrase;
        }
        
        /** @var Type $target_site_upload_path Target site upload path*/
        /** @var Type $source_site_upload_path Source site upload path*/
        list($target_site_upload_url, $target_site_upload_path, $source_site_upload_url, $source_site_upload_path) = $basic_parameters;
        
        $origin_scheme = '';
        if ( ! empty( $ret['imported_package_footprint']['scheme'] ) ) {
            $origin_scheme = $ret['imported_package_footprint']['scheme'];
        }
        
        $scheme_search = parse_url($source_site_upload_url, PHP_URL_SCHEME);       
        $compat_search = '';
        if ( 'http' === $scheme_search && 'https://' === $origin_scheme) {
            $compat_search = str_replace('http://', 'https://', $source_site_upload_url);
            $upload_phrase['wpupload_url_compat']['search'] = $compat_search;
            $upload_phrase['wpupload_url_compat']['replace'] = $target_site_upload_url;
        }
        
        $mixed_search = '';
        if ('https://' === $origin_scheme) {
            $mixed_search = str_replace('https://', 'http://', $source_site_upload_url);
            $upload_phrase['wpupload_url_mixed_content']['search'] = $mixed_search;
            $upload_phrase['wpupload_url_mixed_content']['replace'] = $target_site_upload_url;            
        }
        
        $alternative_mixed_search = '';
        if ($mixed_search && !empty($upload_phrase['alternative_wpupload_url'])) {
            $alternative_mixed_search = str_replace('https://', 'http://', $upload_phrase['alternative_wpupload_url']['search']);
            $upload_phrase['wpupload_url_alt_mixed_content']['search'] = $alternative_mixed_search;
            $upload_phrase['wpupload_url_alt_mixed_content']['replace'] = $target_site_upload_url;  
        }
        
        return $upload_phrase;
    }
    
    /**
     * Add alternative upload url info to footprint for multisites only
     * @param array $footprint
     * @return array
     * @since 1.0.6
     */
    public function addAlternativeUploadInfoToSystemFootprint($footprint = [])
    {
        if (!is_multisite() ) {
            return $footprint;
        }        
        
        if (empty($footprint['site_url']) || empty($footprint['upload_information_url']) || empty($footprint['scheme'])) {
            return $footprint;
        }
        
        $subsite_url = $footprint['site_url'];
        $upload_information_url = $footprint['upload_information_url'];
        $main_site_url = network_site_url();
        
        $scheme = $footprint['scheme'];
        $source_site_url = trailingslashit($scheme . $subsite_url);
        
        $pos = strpos($upload_information_url, $subsite_url);
        $search = $main_site_url;
        $replace = $source_site_url;
        
        if (false !== $pos) {
            $search = $source_site_url;
            $replace = $main_site_url;
        } 
        
        $alternative_uploads_url = str_replace($search, $replace, $upload_information_url);
        if ($alternative_uploads_url === $upload_information_url) {
            return $footprint;
        }
        $footprint['alternative_upload_information_url'] = $alternative_uploads_url;        
        return $footprint;
    }
    
    /**
     * Skip search replace when site parameters the same
     * @param boolean $return
     * @param array $ret
     * @param number $blogid_to_import
     * @param array $replaceables
     * @since 1.0.6
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverSearchReplaceUtilities::itSkipsSearchReplaceWhenSiteParametersTheSame()
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverSearchReplaceUtilities::itDoesNotSkipSearchReplaceParametersUnequal()
     */
    public function skipSearchReplaceWhenSiteParametersSame($return = false, $ret = [], $blogid_to_import = 0, $replaceables = [])
    {
        if (true === $return) {
            return $return;
        }
        
        if ( ! isset($replaceables['wpupload_path']['search']) ||  ! isset($replaceables['wpupload_path']['replace'] ) ) {
            return $return;
        }
        
        if ( ! isset($replaceables['wpupload_url']['search']) ||  ! isset($replaceables['wpupload_url']['replace'] ) ) {
            return $return;
        }
  
        if ( ! isset($replaceables['domain_replace']['search']) ||  ! isset($replaceables['domain_replace']['replace'] ) ) {
            return $return;
        }
        
        return ($replaceables['wpupload_path']['search'] === $replaceables['wpupload_path']['replace'] && $replaceables['wpupload_url']['search'] === $replaceables['wpupload_url']['replace'] && 
            $replaceables['domain_replace']['search'] === $replaceables['domain_replace']['replace']);
    }
    
    /**
     * Validate search and replace data before filtering
     * @param array $replaceables
     * @param array $ret
     * @return boolean
     */
    private function validateData($replaceables = [], $ret = []) 
    {        
        if (empty($ret['imported_package_footprint']['legacy_upload_information_url'])) {
            return false;
        }        
        if (empty($ret['imported_package_footprint']['scheme'])) {
            return false;
        }        
        if ('https://' !== $ret['imported_package_footprint']['scheme'] ) {
            return false;
        }        
        if (empty($replaceables['wpupload_url']['search']) || empty($replaceables['legacybase_url']['search'])) {
            return false;
        }        
        return true;
    }
    
    /**
     * Get raw HTTP version of URL
     * @param string $url
     * @return mixed
     */
    private function getRawHttpVersion($url = '')
    {
        return str_replace( 'https://', 'http://', $url);
    }
    
    /**
     * Adjust https URLs in search and replace for legacy multisites
     * @param array $replaceables
     * @param array $ret
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverSearchReplaceUtilities::itDoesNotAdjustSearchParamsNonHttps()
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverSearchReplaceUtilities::itAdjustLegacyHttpsUrls() 
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverSearchReplaceUtilities::itDoesNotAdjustSearchParamsIfNotLegacy()
     */
    public function adjustLegacySSLReplace($replaceables = [], $ret = [])
    {
        if ( ! $this->validateData($replaceables, $ret) ) {
            return $replaceables;
        }
        
        $https_blogsdir = $replaceables['wpupload_url']['search'];
        $http_blogsdir = $this->getRawHttpVersion($https_blogsdir);
        
        $https_files = $replaceables['legacybase_url']['search'];
        $http_files = $this->getRawHttpVersion($https_files);
        
        $reference_index = array_search("legacybase_url",array_keys($replaceables));
        
        $offset = $reference_index + 1;
        $httpblogsdir_url = [
            'httpblogsdir_url' => [
                'search' => $http_blogsdir,
                'replace' => $replaceables['wpupload_url']['replace']
            ]
        ];
        
        $httpfiles_url = [
            'httpfiles_url' => [
                'search' => $http_files,
                'replace' => $replaceables['legacybase_url']['replace']
            ]
        ];
        
        $slice_start = array_slice($replaceables, 0, $offset, true);        
        $slice_end = array_slice($replaceables, $offset, NULL, true);
        $end_part = $this->computeLegacyGenerics($replaceables, $slice_end, $httpfiles_url);
        
        $replaceables = $slice_start + $httpblogsdir_url + $httpfiles_url + $end_part;        
        
        return $replaceables;
    }
    
    /**
     * Compute legacy generics if applicable
     * @param array $replaceables
     * @param array $slice_end
     * @param array $httpfiles_url
     * @return array
     */
    protected function computeLegacyGenerics($replaceables = [], $slice_end = [], $httpfiles_url = [])
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized()) {
            return $slice_end;
        }        
        
        if (!isset($replaceables['legacybase_url']['search']) || !isset($replaceables['legacybase_url']['replace'])) {
            return $slice_end;
        }
        
        if (!isset($httpfiles_url['httpfiles_url']['search']) || !isset($httpfiles_url['httpfiles_url']['replace'])) {
            return $slice_end;
        }
        
        if (!isset($replaceables['wpupload_url']['search']) || !isset($replaceables['wpupload_url']['replace'])) {
            return $slice_end;
        }        
  
        $legacybaseurl_search = $this->getSystemFunctions()->removeSchemeFromUrl($replaceables['legacybase_url']['search']);
        $legacybaseurl_replace = $this->getSystemFunctions()->removeSchemeFromUrl($replaceables['legacybase_url']['replace']);
        
        $httpfiles_url_search = $this->getSystemFunctions()->removeSchemeFromUrl($httpfiles_url['httpfiles_url']['search']);
        $httpfiles_url_replace = $this->getSystemFunctions()->removeSchemeFromUrl($httpfiles_url['httpfiles_url']['replace']);
        
        $wpupload_url_search = $this->getSystemFunctions()->removeSchemeFromUrl($replaceables['wpupload_url']['search']);
        $wpupload_url_replace = $this->getSystemFunctions()->removeSchemeFromUrl($replaceables['wpupload_url']['replace']);
        
        if ($legacybaseurl_search !== $httpfiles_url_search) {
            return $slice_end;
        }
        
        if ($wpupload_url_search === $legacybaseurl_search) {
            return $slice_end;
        }
        
        if ($wpupload_url_replace !== $legacybaseurl_replace || $wpupload_url_replace !== $httpfiles_url_replace) {
            return $slice_end;
        }
        
        $generic_legacy = [
            'generic_legacy_upload_scheme' => [
                'search' => "//" . $legacybaseurl_search,
                'replace' => "//" . $legacybaseurl_replace
            ]
        ];
        
        $end_part = $generic_legacy + $slice_end;
        return $end_part;
        
    }
}