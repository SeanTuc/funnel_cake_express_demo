<?php

/****************************************************
 * PRIME MOVE GLOBAL DEPENDENCIES
 * Gets the requisite check instance
 * **************************************************
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class PrimeMoverGlobalDependencies
{
    /**
     * Get Prime Mover requisites check instance
     * @return PrimeMoverRequirementsCheck
     */
    public function primeMoverGetRequisiteCheck()
    {
        $phprequirement = '5.6';
        $phpverdependency = new PrimeMoverPHPVersionDependencies( $phprequirement );
        $wpcoredependency = new PrimeMoverWPCoreDependencies( '4.9.8' );
        $phpfuncdependency = new PrimeMoverPHPCoreFunctionDependencies();
        $foldernamedependency = new PrimeMoverPluginSlugDependencies( array( PRIME_MOVER_DEFAULT_FREE_BASENAME, PRIME_MOVER_DEFAULT_PRO_BASENAME ) );
        $coresaltdependency = new PrimeMoverCoreSaltDependencies();
        $required_paths = array(
            PRIME_MOVER_PLUGIN_CORE_PATH,
            PRIME_MOVER_PLUGIN_PATH,
            PRIME_MOVER_THEME_CORE_PATH,
            get_stylesheet_directory(),
            primeMoverGetConfigurationPath()
        );
        $wp_upload_dir = primeMoverGetUploadsDirectoryInfo();
        if ( !empty($wp_upload_dir['basedir']) ) {
            $required_paths[] = $wp_upload_dir['basedir'];
        }
        if ( !empty($wp_upload_dir['path']) ) {
            $required_paths[] = $wp_upload_dir['path'];
        }
        $filesystem_dependency = new PrimeMoverFileSystemDependencies( $required_paths );
        return new PrimeMoverRequirementsCheck(
            $phpverdependency,
            $wpcoredependency,
            $phpfuncdependency,
            $filesystem_dependency,
            $foldernamedependency,
            $coresaltdependency
        );
    }

}