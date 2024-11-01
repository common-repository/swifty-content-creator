<?php
/*
Plugin Name: Swifty Content Creator
Description: Easily create content on your pages.
Author: SwiftyOnline
Version: 3.1.7
Author URI: http://swifty.online/plugins/
Plugin URI: http://swifty.online/plugins/swifty-content-creator
*/
if ( ! defined( 'ABSPATH' ) ) exit;

global $scc_build_use;
$scc_build_use = 'build';

global $swifty_build_use;
$swifty_build_use = 'build';

global $scc_version;
$scc_version = '3.1.7';

// load translations, fill array with texts used in js files
require_once plugin_dir_path( __FILE__ ) . 'php/lang.php';

// Load active plugins first. This is needed so other plugins can overwrite functions in pluggables.php before we load it
foreach( wp_get_active_and_valid_plugins() as $plugin ) {
    include_once( $plugin );
}
unset( $plugin );
// now we can load the pluggable.php file, other plugins had a change to set their own pluggables.
// pluggables defines the "current_user_can" function we need to check for edit rights
require_once ABSPATH . WPINC . '/pluggable.php';
require_once ABSPATH . WPINC . '/capabilities.php';

// when this constant is defined then the plugin is active
define( 'SWIFTY_CONTENT_CREATOR_PLUGIN_FILE', __FILE__ );

$main = '';

if( defined( 'DOING_AJAX' ) ) {
    // when an ajax is done then is_admin() is always true, but we only need this for ajax calls implemented by our own
    // main.php. For ajax call from other plugins we only want to load the main_view.php file
    // Test if this is our own ajax call
    $main_ajax_actions = array( 'swcreator_get_page_list', 'swcreator_get_fa_icon_list', 'swcreator_get_published_data',
        'swcreator_publish', 'swcreator_delete_area', 'swcreator_get_published_versions', 'swcreator_get_revision',
        'swcreator_save_area_settings', 'swcreator_get_area_settings', 'swcreator_save_area_for_page', 'insert_attachment_from_url',
        'swcreator_get_raw_autosave_content', 'swcreator_determine_image_sizes', 'swcreator_determine_image_set_size',
        'swcreator_get_determine_image_sizes', 'spm_publish_page', 'set_editor_visibility', 
        'set_wpautop_and_ptag_bottom_margin_and_wp_embed', 'get_set_swifty_allow_external', 'get_attachment_id_from_url',
        // Those wp ajax calls need the image size information.
        'upload-attachment', 'query-attachments' );

    if( in_array( $_REQUEST[ 'action' ], $main_ajax_actions ) ) {
        $main = 'admin';
    }
} else if( is_admin() ) {
    $main = 'admin';
} else if( isset( $_GET[ 'swcreator_edit' ] ) ) {
    $main = 'edit';
} else if( isset( $_GET[ 'swcreator_iframe' ] ) ) {
    $main = 'edit';
} else if( isset( $_GET[ 'swcreator_ajax' ] ) ) {
    $main = 'ajax';
}

if( $main !== '' ) {
    add_action( 'init', 'can_user_edit_pages' );
}

function can_user_edit_pages() {
    // current_user_can is used safe when 'init' action is triggered. Using this earlier can cause compatibility
    // issues with other plugins, like BBPress.
    if( ! current_user_can( 'edit_pages' ) ) {
        auth_redirect();
    }
}

if( $main == '' ) {
    require_once plugin_dir_path( __FILE__ ) . 'lib/swifty_plugin/php/autoload.php';
    if( is_null( LibSwiftyPluginView::get_instance() ) ) {
        new LibSwiftyPluginView();
    }

    require_once plugin_dir_path( __FILE__ ) . 'php/main_view.php';
    new SwiftyContentCreatorView();
} elseif( $main !== 'api' ) {
    require_once plugin_dir_path( __FILE__ ) . 'lib/swifty_plugin/php/autoload.php';
    if( is_null( LibSwiftyPlugin::get_instance() ) ) {
        new LibSwiftyPlugin();
    }

    require_once plugin_dir_path( __FILE__ ) . 'php/main.php';
    new SwiftyContentCreator();
}
