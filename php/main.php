<?php
// Exit if accessed directly
defined( 'ABSPATH' ) or exit;

require_once plugin_dir_path( __FILE__ ) . 'main_view.php';
require_once plugin_dir_path( __FILE__ ) . 'faicons.php';
require_once plugin_dir_path( __FILE__ ) . 'class-swifty-pack-endpoints.php';

global $shortcode_tags;
global $scc_shortcode_tags;

/**
 * Class SwiftyContentCreator
 * This class contains all code needed (on top of all view code) for admin mode and edit mode
 *
 */
class SwiftyContentCreator extends SwiftyContentCreatorView
{
    protected $swifty_admin_page = 'swifty_content_creator_admin';

    protected $scc_page_render_styles_added_by_plugins;

    /**
     * Initialize filters and actions
     */
    public function init()
    {
        parent::init();

        // Ajax commands
        add_action( 'wp_ajax_swcreator_get_page_list', array( &$this, 'ajax_get_page_list' ) );
        add_action( 'wp_ajax_swcreator_get_fa_icon_list', array( &$this, 'ajax_get_fa_icon_list' ) );
        add_action( 'wp_ajax_swcreator_get_published_data', array( &$this, 'ajax_get_published_data' ) );
        add_action( 'wp_ajax_swcreator_get_raw_autosave_content', array( &$this, 'ajax_get_raw_autosave_content' ) );
        add_action( 'wp_ajax_swcreator_publish', array( &$this, 'ajax_publish' ) );
        add_action( 'wp_ajax_swcreator_delete_area', array( &$this, 'ajax_delete_area' ) );
        add_action( 'wp_ajax_swcreator_get_area_settings', array( &$this, 'ajax_get_area_settings' ) );
        add_action( 'wp_ajax_swcreator_save_area_settings', array( &$this, 'ajax_save_area_settings' ) );
        add_action( 'wp_ajax_swcreator_save_area_for_page', array( &$this, 'ajax_save_area_for_page' ) );
        add_action( 'wp_ajax_swcreator_get_published_versions', array( &$this, 'ajax_get_published_versions' ) );
        add_action( 'wp_ajax_swcreator_get_revision', array( &$this, 'ajax_get_revision' ) );
        add_action( 'wp_ajax_insert_attachment_from_url', array( $this, 'ajax_insert_attachment_from_url' ) );
        add_action( 'wp_ajax_swcreator_determine_image_sizes', array( &$this, 'ajax_determine_image_sizes' ) );
        add_action( 'wp_ajax_swcreator_determine_image_set_size', array( &$this, 'ajax_determine_image_set_size' ) );
        add_action( 'wp_ajax_swcreator_get_determine_image_sizes', array( &$this, 'ajax_get_determine_image_sizes' ) );
        add_action( 'wp_ajax_set_editor_visibility', array( $this, 'ajax_set_editor_visibility' ) );
        add_action( 'wp_ajax_set_wpautop_and_ptag_bottom_margin_and_wp_embed', array( $this, 'ajax_set_wpautop_and_ptag_bottom_margin_and_wp_embed' ) );
        add_action( 'wp_ajax_get_set_swifty_allow_external', array( $this, 'ajax_get_set_swifty_allow_external' ) );
        add_action( 'wp_ajax_get_attachment_id_from_url', array( $this, 'ajax_get_attachment_id_from_url' ) );

        // add swcreator ajax call
        add_action( 'swcreator_ajax_get_asset_content_by_data', array( &$this, 'swcreator_ajax_get_asset_content_by_data' ) );
        add_action( 'swcreator_ajax_get_asset_edit_stache', array( &$this, 'swcreator_ajax_get_asset_edit_stache' ) );
        add_action( 'swcreator_ajax_convert_html_with_shortcodes', array( &$this, 'swcreator_ajax_convert_html_with_shortcodes' ) );

        add_action( 'swcreator_ajax_get_content_clipboard', array( &$this, 'swcreator_ajax_get_content_clipboard' ) );
        add_action( 'swcreator_ajax_set_content_clipboard', array( &$this, 'swcreator_ajax_set_content_clipboard' ) );

        add_action( 'swcreator_ajax_get_area_template_style', array( &$this, 'swcreator_ajax_get_area_template_style' ) );
        add_action( 'swcreator_ajax_set_area_template_style', array( &$this, 'swcreator_ajax_set_area_template_style' ) );
        add_action( 'swcreator_ajax_get_post_style', array( &$this, 'swcreator_ajax_get_post_style' ) );
        add_action( 'swcreator_ajax_set_post_style', array( &$this, 'swcreator_ajax_set_post_style' ) );

        add_action( 'swcreator_ajax_get_message_states', array( &$this, 'swcreator_ajax_get_message_states' ) );
        add_action( 'swcreator_ajax_set_message_states', array( &$this, 'swcreator_ajax_set_message_states' ) );
        add_action( 'swcreator_ajax_set_gui_mode', array( &$this, 'swcreator_ajax_set_gui_mode' ) );
        add_action( 'swcreator_ajax_set_welcome_state', array( &$this, 'swcreator_ajax_set_welcome_state' ) );
        add_action( 'swcreator_ajax_set_scc_enable_lock_options', array( &$this, 'swcreator_ajax_set_scc_enable_lock_options' ) );
        add_action( 'swcreator_ajax_get_default_messages', array( &$this, 'swcreator_ajax_get_default_messages' ) );
        add_action( 'swcreator_ajax_get_swifty_plugin_versions', array( &$this, 'swcreator_ajax_get_swifty_plugin_versions' ) );
        add_action( 'swcreator_ajax_upgrade_ssd', array( &$this, 'swcreator_ajax_upgrade_ssd' ) );

        add_filter( 'swifty_get_edit_area', array( &$this, 'hook_swifty_get_edit_area' ), 10, 0 );
        add_filter( 'swifty_get_edit_area_template', array( &$this, 'hook_swifty_get_edit_area_template' ), 10, 0 );

        // add upload endpoint for ckeditor, this is only called when logged in
        add_action( 'admin_post_swifty_upload', array( &$this, 'hook_admin_post_swifty_upload' ) );

        // Change the default edit page screen
        add_action( 'dbx_post_sidebar', array( &$this, 'change_default_edit_page' ) );

        if( $this->use_ptag0() ) {
            add_filter( 'mce_css', array( $this, 'hook_mce_css_add_editor_style' ) );
        }

        add_action( 'init', array( &$this, 'hook_init' ) );
        add_filter( 'template_include', array( &$this, 'hook_template_include' ) );
        add_filter( 'swifty_enqueue_scripts', array( &$this, 'hook_swifty_enqueue_scripts' ) );
        do_action( 'swifty_lib_enqueue_script_bowser' );

        // Add the admin page menu for Swifty
        add_action( 'admin_menu', array( &$this, 'hook_admin_menu_add_swifty_menu' ) );
        add_action( 'admin_menu', array( &$this, 'hook_admin_menu_add_swifty_menu_plugins' ), 10000 );
        add_filter( 'admin_add_swifty_menu', array( &$this, 'hook_admin_add_swifty_menu' ), 1, 4 );
        add_filter( 'admin_add_swifty_admin', array( &$this, 'hook_admin_add_swifty_admin' ), 1, 8 );

        // Add plugin check for required plugins
        add_action( 'stgmpa_register', array( &$this, 'hook_stgmpa_register' ) );

        add_filter( 'swifty_plugin_not_active', array( &$this, 'hook_swifty_plugin_not_active' ), 10, 1 );
        add_filter( 'swifty_get_gui_mode', array( &$this, 'hook_swifty_get_gui_mode' ), 10, 1 );

        add_action( 'admin_init', array( &$this, 'hook_admin_init' ) );

        // after all other scripts
        add_action( 'wp_footer', array( $this, 'hook_wp_footer_wp_autosave_js' ), 10000 );
        add_action( 'swifty_print_footer', array( $this, 'hook_wp_footer_wp_autosave_js' ) );

        add_action( 'all_admin_notices', array( &$this, 'hook_all_admin_notices' ) );

        add_filter( 'run_wptexturize', array( $this, 'hook_run_wptexturize' ) );
        add_filter( 'intermediate_image_sizes_advanced', array( $this, 'hook_intermediate_image_sizes_advanced' ) );
        add_filter( 'image_size_names_choose', array( $this, 'hook_image_size_names_choose' ) );
        add_action( 'wp_restore_post_revision', array( $this, 'hook_wp_restore_post_revision' ) );
        add_action( 'swifty_page_manager_view_page_tree', array( $this, 'hook_swifty_page_manager_view_page_tree' ) );
        add_action( 'swifty_page_manager_publish', array( $this, 'hook_wp_restore_post_revision' ) );
        add_filter( 'swifty_page_manager_publish_ajax_succes', array( $this, 'hook_swifty_page_manager_publish_ajax_succes' ), 10, 2 );

        add_filter( 'swifty_get_post_style', array( $this, 'swifty_get_post_style' ), 10, 2 );
        add_action( 'swifty_set_post_style', array( $this, 'swifty_set_post_style' ), 10, 2 );

        // disable ninja forms meta box. it causes havoc in ssd, so we remove it. Use the asset instead
        remove_action( 'add_meta_boxes', 'ninja_forms_add_custom_box' );
        remove_action( 'wp_head', 'ninja_forms_page_append_check' );

        // disable skype toolbar
        add_action( 'wp_head', array( &$this, 'hook_wp_head_prevend_skype_toolbar' ) );

        // disable emoji script while editing page
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );

        add_filter( 'media_view_strings', array( $this, 'hook_media_view_strings') );
        
        add_action( 'wp_head', array( &$this, 'hook_wp_head2' ) );
    }

    function hook_wp_head2() {
        // Always load swcreator.css when editing pages.
        LibSwiftyPluginView::lazy_load_css( 'swcreator_swcreator_css' );
    }
    
    /**
     * add meta information to disable the skype toolbar
     */
    function hook_wp_head_prevend_skype_toolbar()
    {
        echo '<meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />' . "\n";
    }

    /**
     * Change the message for deleting attachments, add a warning about images also being deleted from existing pages
     *
     * @param $strings
     * @return mixed
     */
    function hook_media_view_strings( $strings ) {
        $strings[ 'warnDelete' ] = __( "You are about to permanently delete this item.\nWARNING! This image will also be removed from all pages of this site.\n\n'Cancel' to stop, 'OK' to delete." );

        return $strings;
    }

    /**
     * show autosave explanation when editing a page in wp-mode
     */
    function hook_all_admin_notices()
    {
        $current_screen = get_current_screen();

        if( $current_screen->parent_base === 'edit' &&
            $current_screen->base === 'post' &&
            $current_screen->id === 'page' &&
            $current_screen->post_type === 'page'
        ) {
            $extra_html = '<p id="has-newer-autosave"></p>' .
                '<p style="text-align: center;font-size: 28pt;color: red;">' .
                __( 'WAIT A MINUTE!', 'swifty-content-creator' ) .
                '</p><br />' .
                __( 'There is a more recent auto-saved version of this page!', 'swifty-content-creator' ) .
                '<br /><br />' .
                __( 'You will LOOSE ALL changes that you made to this page, unless you restore that autosave version.', 'swifty-content-creator' ) .
                '<br /><br />' .
                '<b><a href="__AUTOSAVELINK__">' .
                __( 'Click here to view the autosave and optionally restore it.', 'swifty-content-creator' ) .
                '</a></b><br /><br />' .
                '<p style="font-style: italic">' .
                __( '(Please note that Swifty Content Creator uses autosaves to save draft content of pages that are already ' .
                'published, so the draft content will not be visible to the public immediately. Not restoring now would ' .
                'destroy all draft content.)', 'swifty-content-creator' ) .
                '</p>';
            ?>
            <script>
                ( function( $ ) {
                    $( function() {
                        var $autoSave = $( '#has-newer-autosave' );
                        var $autoSaveLink = null;
                        var extraHTML = <?php echo json_encode( $extra_html ); ?>;

                        if( $autoSave.length ) {
                            $autoSaveLink = $autoSave.find( 'a' );
                            var autoSaveUrl = $autoSaveLink[0].href;
                            extraHTML = extraHTML.replace( '__AUTOSAVELINK__', autoSaveUrl );

                            $autoSave.html( extraHTML );
                            $autoSave.parent().css( {
                                'border-left-width': '8px',
                                'padding': '1px 8px'
                            } );
                        }
                    } );
                }( jQuery ) );
            </script>
            <?php
        }
    }

    /**
     * Disable wptexturize while editing
     *
     * @return bool
     */
    function hook_run_wptexturize() {
        return false;
    }


    /**
     * Split content on shortcode(s) and wrap this splitted content. The argument arrays should have the same number of
     * items and only the first item in each array is used and the last part of the array is used for the split and wrap
     * of the splitted content
     *
     * @param $content
     * @param $shortcodesArray
     * @param $wrapperStartArray
     * @param $wrapperEndArray
     * @return string
     */
    function split_and_wrap_text( $content, $shortcodesArray, $wrapperStartArray, $wrapperEndArray ) {

        if( ( count( $shortcodesArray ) > 0 ) && ( count( $wrapperStartArray ) > 0 ) && ( count( $wrapperEndArray ) > 0 ) ) {
            $shortcodes = array_shift( $shortcodesArray );
            $wrapperStart = array_shift( $wrapperStartArray );
            $wrapperEnd = array_shift( $wrapperEndArray );

            if( $content ) {
                // split on shortcodes
                $pattern = get_shortcode_regex( $shortcodes );
                $matches = preg_split( '/' . $pattern . '/s', $content, -1, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_OFFSET_CAPTURE );

                // all text between shortcodes is put into text assets
                $new_content = '';
                $offset = 0;
                foreach( $matches as $match ) {
                    $new_content .= substr( $content, $offset, $match[ 1 ] - $offset );
                    $text = $match[ 0 ];
                    $text = $this->split_and_wrap_text( $text, $shortcodesArray, $wrapperStartArray, $wrapperEndArray );
                    $new_content .= $wrapperStart . $text . $wrapperEnd;
                    $offset = $match[ 1 ] + strlen( $match[ 0 ] );
                }
                $content = $new_content . substr( $content, $offset );
            }
        }

        return $content;
    }

    function unsplit_and_wrap_text( $content, $shortcodesArray ) {

        while ( count( $shortcodesArray ) > 0 ) {
            $shortcodes = array_shift( $shortcodesArray );

            // split on shortcodes
            $pattern = get_shortcode_regex( $shortcodes );
            $matches = preg_split( '/' . $pattern . '/s', $content, -1, PREG_SPLIT_OFFSET_CAPTURE );

            // all text between shortcodes is put into text assets
            $new_content = '';
            $offset = 0;
            foreach( $matches as $match ) {
                $pre_match = substr( $content, $offset, $match[ 1 ] - $offset );

                // remove shortcode when temp="1"
                if( preg_match_all( '/' . $pattern . '/s', $pre_match, $pre_matches ) ) {
                    if( isset( $pre_matches[ 2 ] ) &&( $pre_matches[ 2 ][0] !== '' ) ) {
                        $attributes = shortcode_parse_atts( $pre_matches[ 3 ][0] );
                        if( isset( $attributes[ 'swc_temporary' ] ) ) {
                            $pre_match = $pre_matches[ 5 ][0];
                        }
                    }
                }

                $new_content .= $pre_match;
                $text = $match[ 0 ];

                $new_content .= $text;
                $offset = $match[ 1 ] + strlen( $match[ 0 ] );
            }
            $content = $new_content . substr( $content, $offset );
        }
        return $content;
    }

    // override when editing in scc
    function hook_the_content_check_for_newer_autosave( $content ) {
        // first get the correct content
        $content = parent::hook_the_content_check_for_newer_autosave( $content );

        if( ( $content === '' ) && ( 'main' !== $this->hook_swifty_get_edit_area() ) ) {
            $content = '&nbsp;';
        }
        // now split the content on rows and then text assets
        if( $content ) {

            $content = $this->split_and_wrap_text( $content,
                array(
                    array( 'swifty_grid_row' => 'swifty_grid_row' ),
                    array( 'swifty_text' => 'swifty_text' ),
                ),
                array( '[swifty_grid_row swc_temporary="1"][swifty_grid_column swc_temporary="1"]', '[swifty_text swc_width="100" swc_temporary="1" swc_margin_top="0" swc_margin_bottom="0" swc_margin_left="0"]' ),
                array( '[/swifty_grid_column][/swifty_grid_row]', '[/swifty_text]' )
            );
        }
        return $content;
    }

    function remove_temporary_shortcodes_from_content( $content ) {
        if( $content ) {
            return $this->unsplit_and_wrap_text( $content,
                array(
                    array( 'swifty_text' => 'swifty_text' ),
                    array( 'swifty_grid_column' => 'swifty_grid_column' ),
                    array( 'swifty_grid_row' => 'swifty_grid_row' ),
                )
            );
        } else {
            return $content;
        }
    }

    /**
     * Add shortcode asset to list of registered assets
     * Already registered asset values will be over written
     *
     * @param $def
     */
    public function hook_swifty_register_shortcode( $def )
    {
        if( isset( $def[ 'plugin' ] ) && ( get_option( 'ss2_hosting_name' ) !== 'AMH' ) ) {
            if( apply_filters( 'swifty_has_license_' . $def[ 'plugin' ], 'D' ) !== 'A' ) {
                $def[ 'expired' ] = $def[ 'plugin' ];
            }
        }

        parent::hook_swifty_register_shortcode( $def );
    }

    /**
     * Look for text outside assets and put it in text asset. Render shortcode with parent function.
     *
     * @param $atts
     * @param null $content
     * @return null|string
     */
    function hook_swifty_grid_column( $atts, $content = null ) {
        if( $content ) {
            // split on shortcodes
            $pattern = get_shortcode_regex();
            $matches = preg_split( '/'. $pattern .'/s', $content, -1, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_OFFSET_CAPTURE );

            // all text between shortcodes is put into text assets
            $new_content = '';
            $offset = 0;
            foreach( $matches as $match ) {
                $new_content .= substr($content, $offset, $match[1] - $offset );
                $text = $match[0];
                // skip empty p tags, but ensure there is nothing else in this string
                if( ! preg_match( "/^<p[^>]*>[\s|&nbsp;|<br>]*<\/p>$/", trim( $text ) ) ) {
                    $new_content .= '[swifty_text swc_width="100"]' . $text . '[/swifty_text]';
                }
                $offset = $match[1] + strlen($match[0]);
            }
            $content = $new_content . substr($content, $offset );
        }

        // now we render the column as always
        return parent::hook_swifty_grid_column( $atts, $content );
    }

    /**
     * the sizes that we want to create for new image attachments
     *
     * @param array $sizes
     * @return array
     */
    public function hook_intermediate_image_sizes_advanced( $sizes = array() ) {

        // detect whether we are inserting media from an asset
        // this seems to be always the case when this class is loaded (only loaded when swifty content creator is used)

        global $swifty_lib_dir;
        if( isset( $swifty_lib_dir ) ) {
            require_once $swifty_lib_dir . '/php/lib/swifty-image-functions.php';
        }

        return SwiftyImageFunctions::intermediate_image_sizes_advanced( $sizes, $this->get_option_attachmentsizes() );
    }

    /**
     * Add the description of our own sizes as optional sizes in the wp media selector
     *
     * @param $sizes
     * @return array
     */
    public function hook_image_size_names_choose( $sizes ) {
        global $swifty_lib_dir;
        if( isset( $swifty_lib_dir ) ) {
            require_once $swifty_lib_dir . '/php/lib/swifty-image-functions.php';
        }

        return SwiftyImageFunctions::image_size_names_choose( $sizes );
    }

    /**
     * We will need to determine the images sizes when a revision (or autosave) has been restored. Or when the page
     * manager publishes a post
     *
     * @param $id_post
     */
    public function hook_wp_restore_post_revision( $id_post ) {

        update_post_meta( $id_post, 'swifty_determine_image_sizes', 'needed' );
    }

    /**
     * After publishing in SPM we want to determine the image sizes
     *
     * @param $script
     * @param $id_post
     * @return string
     */
    public function hook_swifty_page_manager_publish_ajax_succes( $script, $id_post ) {

        $nonce = wp_create_nonce( $this->get_nonce_string( $id_post ) );
        $view_url = wp_json_encode( get_permalink( $id_post ) );

        $result = '';
        $result .= "scc_data.ajax_nonce = '{$nonce}';";
        $result .= "scc_data.page_id = {$id_post};";
        $result .= "window.swiftyStartDetermineImageSizes( {$id_post}, {$view_url}, function() { {$script} } );";
        return $result;
    }

    /**
     * Filter: "swifty_get_post_style" - look for post style
     *
     * @param $style
     * @param $id_post
     * @return string
     */
    public function swifty_get_post_style( $style, $id_post ) {

        if( $style_meta = get_post_meta( $id_post, 'swifty_style', true ) ) {
            return $style_meta;
        } else {
            return $style;
        }
    }

    /**
     * Action: "swifty_set_post_style" - store post style
     *
     * @param $style
     * @param $id_post
     */
    public function swifty_set_post_style( $style, $id_post ) {

        update_post_meta( $id_post, 'swifty_style', $style );
    }

    /**
     * Include the js script in spm tree view for determing the image  sizes
     */
    public function hook_swifty_page_manager_view_page_tree( ) {

        global $scc_version;
        $bust_add = '?swcv=scc_' . $scc_version;

        global $scc_build_use;
        if( $scc_build_use == 'build' ) {
            wp_enqueue_script( 'determine_image_sizes_js', $this->this_plugin_url . 'js/determine_image_sizes.min.js' . $bust_add, array( 'jquery' ), $scc_version, true );
        } else {
            wp_enqueue_script( 'determine_image_sizes_js', $this->this_plugin_url . 'js/diverse/determine_image_sizes.js' . $bust_add, array( 'jquery' ), $scc_version, true );
        }

        wp_localize_script( 'determine_image_sizes_js', 'scc_data', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'ss2_hosting_name' => get_option( 'ss2_hosting_name' )
        ) );
    }

    /**
     * return the name of the settings page depending on using swifty site or not
     *
     * @return string|void
     */
    function get_admin_page_title()
    {
        $swifty_SS2_hosting_name = apply_filters( 'swifty_SS2_hosting_name', false );
        if( $swifty_SS2_hosting_name ) {
            $admin_page_title = __( 'SwiftySite Content', 'swifty-content-creator' );
        } else {
            $admin_page_title = 'Swifty Content Creator';
        }
        return $admin_page_title;
    }

    /**
     * add Swifty menu links to dashboard
     */
    function hook_admin_menu_add_swifty_menu()
    {
        $admin_page_title = $this->get_admin_page_title();

        add_filter( 'swifty_admin_page_links_' . $this->swifty_admin_page, array( $this, 'hook_swifty_admin_page_links' ) );

        // Add the admin settings menu for this plugin
        LibSwiftyPlugin::get_instance()->admin_add_swifty_admin();
        LibSwiftyPlugin::get_instance()->admin_add_swifty_menu( $admin_page_title, __( 'Content', 'swifty-content-creator' ), $this->swifty_admin_page, array( &$this, 'admin_scc_menu_page' ), true );

        if( get_option( 'ss2_hosting_name' ) !== 'AMH' ) {
            do_action( 'swifty_setup_plugin_action_links', $this->plugin_basename, 'https://www.swifty.online/?rss3=wpaplgpg', __( 'More Swifty Plugins', 'swifty-content-creator' ) );
        }
    }

    /**
     * add swifty page with required plugins
     * this menu item might be already added by the swifty-menu plugin earlier, the lib will prevent double additions
     */
    function hook_admin_menu_add_swifty_menu_plugins()
    {
        if( Swifty_TGM_Plugin_Activation::get_instance()->is_admin_menu_needed() ) {
            LibSwiftyPlugin::get_instance()->admin_add_swifty_menu( __( 'Required plugins', 'swifty-content-creator' ), __( 'Required plugins', 'swifty-content-creator' ), 'swifty_required_plugins', array( Swifty_TGM_Plugin_Activation::get_instance(), 'install_plugins_page' ), false );
        }
    }

    function hook_admin_add_swifty_menu( $page, $name, $key, $func )
    {
        if( ! $page ) {
            $page = add_submenu_page( 'swifty_admin', $name, $name, 'manage_options', $key, $func );
        }
        return $page;
    }

    function hook_admin_add_swifty_admin( $done, $v1, $v2, $v3, $v4, $v5, $v6, $v7 )
    {
        if( ! $done ) {
            add_menu_page( $v1, $v2, $v3, $v4, $v5, $v6, $v7 );
        }
        return true;
    }

    /**
     * Our plugin admin menu page
     */
    function admin_scc_menu_page()
    {
        LibSwiftyPlugin::get_instance()->admin_options_menu_page( $this->swifty_admin_page );
    }

    /**
     * Add a general tab to the swifty settings
     *
     * @param $settings_links
     * @return mixed
     */
    public function hook_swifty_admin_page_links( $settings_links )
    {
        $link_general_title = __( 'General', 'swifty-content-creator' );
        $link_general_method = array( $this, 'scc_tab_options_content' );

        $settings_links[ 'general' ] = array( 'title' => $link_general_title, 'method' => $link_general_method );

        return $settings_links;
    }

    /**
     * display content of scc general tab
     */
    function scc_tab_options_content()
    {
        settings_fields( 'scc_plugin_options' );
        do_settings_sections( 'scc_plugin_options_page' );
        submit_button();

        $this->display_version();
    }

    /**
     * echo plugin name and version
     */
    protected function display_version()
    {
        echo '<p>' . 'Swifty Content Creator ' . $this->plugin_version . '</p>';
    }

    /**
     * Disable the WP admin bar on Swifty edit pages
     */
    function hook_init()
    {
        if( $this->is_editing() ) {
            add_filter( 'show_admin_bar', '__return_false' );
        }
    }

    /**
     * When we start the editor we show an empty page on which the js will show the editor and load the
     * page in a iframe
     *
     * @param $template
     * @return string
     */
    function hook_template_include( $template ) {
        if( ( is_page() || is_single() ) && $this->is_swc_editor() ) {
            return dirname(__FILE__) . '/page_creator_template.php';
        }
        return $template;
    }

    /**
     * We only add scripts we really need, this also includes the scripts for the wp media selector, swifty lib css and
     * swifty probe module.
     */
    function hook_swifty_enqueue_scripts() {

        // editor and media js
        $this->hook_wp_head();

        // swifty lib js
        swifty_lib_view_enqueue_styles();

        // Always load swcreator.css when editing pages.
        LibSwiftyPluginView::lazy_load_css( 'swcreator_swcreator_css' );
    }

    /**
     * add the scc options and settings and bind them to the correct setting section
     */
    function hook_admin_init()
    {
        // setting group name, name of option
        register_setting( 'scc_plugin_options', 'scc_plugin_options' );
        register_setting( 'scc_plugin_options', 'swifty_allow_external' );
        register_setting( 'scc_plugin_options', 'swifty_gui_mode', array( $this, 'callback_swifty_gui_mode' ) );
        register_setting( 'scc_plugin_options', 'scc_enable_lock_options' );
        register_setting( 'scc_plugin_options', 'scc_wp_embed' );

        add_settings_section(
            'scc_plugin_options_main_id',
            '',
            array( $this, 'scc_plugin_options_main_text_callback' ),
            'scc_plugin_options_page'
        );

        add_settings_field(
            'scc_plugin_options_main_ptag_bottom_margin',
            __( 'Space between paragraphs', 'swifty-content-creator' ),
            array( $this, 'plugin_setting_ptag_bottom_margin' ),
            'scc_plugin_options_page',
            'scc_plugin_options_main_id'
        );

        add_settings_field(
            'scc_plugin_options_main_wpautop',
            __( 'On the fly convert paragraphs into p tags', 'swifty-content-creator' ),
            array( $this, 'plugin_setting_wpautop' ),
            'scc_plugin_options_page',
            'scc_plugin_options_main_id'
        );

        add_settings_field(
            'scc_plugin_options_main_attachmentsizes',
            __( 'Media image sizes', 'swifty-content-creator' ),
            array( $this, 'plugin_setting_attachmentsizes' ),
            'scc_plugin_options_page',
            'scc_plugin_options_main_id'
        );

        add_settings_field(
            'scc_plugin_options_swifty_allow_external',
            __( 'Allow contacting Swifty servers', 'swifty-content-creator' ),
            array( $this, 'plugin_setting_swifty_allow_external' ),
            'scc_plugin_options_page',
            'scc_plugin_options_main_id'
        );

        add_settings_field(
            'scc_plugin_options_swifty_gui_mode',
            __( 'Use mode', 'swifty-content-creator' ),
            array( $this, 'plugin_setting_swifty_gui_mode' ),
            'scc_plugin_options_page',
            'scc_plugin_options_main_id'
        );

        add_settings_field(
            'scc_plugin_options_scc_enable_lock_options',
            __( 'Enable lock options', 'swifty-content-creator' ),
            array( $this, 'plugin_setting_scc_enable_lock_options' ),
            'scc_plugin_options_page',
            'scc_plugin_options_main_id'
        );

        add_settings_field(
            'scc_plugin_options_scc_wp_embed',
            __( 'Disable wp-embed', 'swifty-content-creator' ),
            array( $this, 'plugin_setting_scc_wp_embed' ),
            'scc_plugin_options_page',
            'scc_plugin_options_main_id'
        );
    }

    function scc_plugin_options_main_text_callback()
    {
    }


    function plugin_setting_swifty_allow_external() {
        $swifty_allow_external = apply_filters( 'swifty_get_allow_external', '' );

        echo '<input ' .
            'type="radio" ' .
            'id="scc_plugin_options_swifty_allow_external" ' .
            'name="swifty_allow_external" ' .
            'value="allow" ' .
            ( ( $swifty_allow_external === 'allow' ) ? 'checked="checked" ' : '' ) .
            '/>' . __( 'Yes, get me the latest and greatest.', 'swifty-content-creator' ) . '<br>';
        echo '<input ' .
            'type="radio" ' .
            'id="scc_plugin_options_swifty_allow_external" ' .
            'name="swifty_allow_external" ' .
            'value="disallow" ' .
            ( ( $swifty_allow_external === 'disallow' ) ? 'checked="checked" ' : '' ) .
            '/>' . __( 'No thanks.', 'swifty-content-creator' ) . '<br>';

        echo '<label for="scc_plugin_options_swifty_allow_external">' .
            __( 'Allow contacting the Swifty servers to get the latest stuff, like presets, and to gather anonymous statistics.', 'swifty-content-creator' ) .
            '</label>';
    }

    function plugin_setting_swifty_gui_mode() {
        $swifty_gui_mode = $this->hook_swifty_get_gui_mode( 'advanced' );

        echo '<input ' .
            'type="radio" ' .
            'id="scc_plugin_options_swifty_gui_mode" ' .
            'name="swifty_gui_mode" ' .
            'value="easy" ' .
            ( ( $swifty_gui_mode === 'easy' ) ? 'checked="checked" ' : '' ) .
            '/>' . __( 'Easy.', 'swifty-content-creator' ) . '<br>';
        echo '<input ' .
            'type="radio" ' .
            'id="scc_plugin_options_swifty_gui_mode" ' .
            'name="swifty_gui_mode" ' .
            'value="advanced" ' .
            ( ( $swifty_gui_mode === 'advanced' ) ? 'checked="checked" ' : '' ) .
            '/>' . __( 'Advanced.', 'swifty-content-creator' ) . '<br>';

        echo '<label for="scc_plugin_options_swifty_allow_external">' .
            __( 'Show more User Interface options in advanced mode.', 'swifty-content-creator' ) .
            '</label>';
    }

    function plugin_setting_scc_enable_lock_options() {
        $scc_enable_lock_options = get_option( 'scc_enable_lock_options', 'disabled' );

        echo '<input ' .
            'type="checkbox" ' .
            'id="scc_plugin_options_enable_lock_options" ' .
            'name="scc_enable_lock_options" ' .
            'value="enabled" ' .
            checked( 'enabled', $scc_enable_lock_options, false ) .
            '/>';

        echo '<label for="scc_plugin_options_enable_lock_options">' .
            __( 'Enable', 'swifty-site' ) .
            '</label>';
    }

    function plugin_setting_scc_wp_embed() {
        $scc_wp_embed = $this->get_option_scc_wp_embed();

        echo '<input ' .
            'type="radio" ' .
            'id="scc_plugin_options_wp_embed" ' .
            'name="scc_wp_embed" ' .
            'value="default" ' .
            ( ( $scc_wp_embed === 'default' ) ? 'checked="checked" ' : '' ) .
            '/>' . __( 'Default.', 'swifty-content-creator' ) . '<br>';
        echo '<input ' .
            'type="radio" ' .
            'id="scc_plugin_options_wp_embed" ' .
            'name="scc_wp_embed" ' .
            'value="disable" ' .
            ( ( $scc_wp_embed === 'disable' ) ? 'checked="checked" ' : '' ) .
            '/>' . __( 'Disable wp-embed.', 'swifty-content-creator' ) . '<br>';

        echo '<label for="scc_plugin_options_wp_embed">' .
            __( 'Wp-embed was introduced in WordPress 4.4 but causes pages with images to get a worse<br>Google PageSpeed Insights score, causing you to get a lower position in Google Search.', 'swifty-content-creator' ) . '<br>' .
            __( 'We strongly advise to disable wp-embed.', 'swifty-content-creator' ) .
            '</label>';
    }

    /**
     * Implement field for p tag 0 option
     */
    function plugin_setting_ptag_bottom_margin() {
        $options = get_option( 'scc_plugin_options' );
        if( ( $options === false ) || ( ! is_array( $options ) ) ) {
            $options = array();
        }

        echo '<input ' .
            'type="checkbox" ' .
            'id="scc_plugin_options_main_ptag_bottom_margin" ' .
            'name="scc_plugin_options[ptag_bottom_margin]" ' .
            'value="1" ' .
            checked( 1, isset( $options[ 'ptag_bottom_margin' ] ) && $options[ 'ptag_bottom_margin' ], false ) .
            '/>';

        echo '<label for="scc_plugin_options_main_ptag_bottom_margin">' .
            __( 'Hide space between paragraphs on ALL pages (remove bottom margin for p tags)', 'swifty-content-creator' ) .
            '</label>';
    }

    /**
     * Implement field for p tag 0 option
     */
    function plugin_setting_wpautop() {
        $options = get_option( 'scc_plugin_options' );
        if( ( $options === false ) || ( ! is_array( $options ) ) ) {
            $options = array();
        }
        if( ! array_key_exists( 'wpautop', $options ) ) {
            $options[ 'wpautop' ] = apply_filters( 'scc_plugin_options_default_wpautop', 'default' );
        }

        echo '<input ' .
            'type="radio" ' .
            'id="scc_plugin_options_main_wpautop" ' .
            'name="scc_plugin_options[wpautop]" ' .
            'value="off" ' .
            ( isset( $options[ 'wpautop' ] ) && ( $options[ 'wpautop' ] === 'off' ) ? 'checked="checked" ' : '' ) .
            '/>' . __( 'Off', 'swifty-content-creator' ) . '<br>';
        echo '<input ' .
            'type="radio" ' .
            'id="scc_plugin_options_main_wpautop" ' .
            'name="scc_plugin_options[wpautop]" ' .
            'value="on" ' .
            ( isset( $options[ 'wpautop' ] ) && ( $options[ 'wpautop' ] === 'on' ) ? 'checked="checked" ' : '' ) .
            '/>' . __( 'On', 'swifty-content-creator' ) . '<br>';
        echo '<input ' .
            'type="radio" ' .
            'id="scc_plugin_options_main_wpautop" ' .
            'name="scc_plugin_options[wpautop]" ' .
            'value="default" ' .
            ( isset( $options[ 'wpautop' ] ) && ( $options[ 'wpautop' ] === 'default' ) ? 'checked="checked" ' : '' ) .
            '/>' . __( 'Default', 'swifty-content-creator' ) . '<br>';

        echo __( '<b>Warning!</b> On sites that already have important content, do not change this setting unless absolutely sure!<br>On new sites it is advised to set this setting to "off".<br>This setting will toggle the Wordpress functionality "wpautop" on or off.', 'swifty-content-creator' ) . '<br>';
    }

    /**
     * Implement field for p tag 0 option
     */
    function plugin_setting_attachmentsizes() {
        $options = get_option( 'scc_plugin_options' );
        if( ( $options === false ) || ( ! is_array( $options ) ) ) {
            $options = array();
        }
        if( ! array_key_exists( 'attachmentsizes', $options ) ) {
            $options[ 'attachmentsizes' ] = apply_filters( 'scc_plugin_options_default_attachmentsizes', 'swifty' );
        }

        echo '<input ' .
            'type="radio" ' .
            'id="scc_plugin_options_main_attachmentsizes" ' .
            'name="scc_plugin_options[attachmentsizes]" ' .
            'value="wp" ' .
            ( isset( $options[ 'attachmentsizes' ] ) && ( $options[ 'attachmentsizes' ] === 'wp' ) ? 'checked="checked" ' : '' ) .
            '/>' . __( 'Use Wordpress sizes', 'swifty-content-creator' ) . '<br>';
        echo '<input ' .
            'type="radio" ' .
            'id="scc_plugin_options_main_attachmentsizes" ' .
            'name="scc_plugin_options[attachmentsizes]" ' .
            'value="swifty" ' .
            ( isset( $options[ 'attachmentsizes' ] ) && ( $options[ 'attachmentsizes' ] === 'swifty' ) ? 'checked="checked" ' : '' ) .
            '/>' . __( 'Use Swifty sizes', 'swifty-content-creator' ) . '<br>';
        echo '<input ' .
            'type="radio" ' .
            'id="scc_plugin_options_main_attachmentsizes" ' .
            'name="scc_plugin_options[attachmentsizes]" ' .
            'value="all" ' .
            ( isset( $options[ 'attachmentsizes' ] ) && ( $options[ 'attachmentsizes' ] === 'all' ) ? 'checked="checked" ' : '' ) .
            '/>' . __( 'Use Wordpress and Swifty sizes', 'swifty-content-creator' ) . '<br>';

        echo __( 'Auto generate image sizes after upload from within Swifty Content Creator.', 'swifty-content-creator' ) . '<br>';
    }

    /**
     * look for $find_first and after that for $return_part. Insert $insertion right after $return_part
     *
     * @param $script
     * @param $find_first
     * @param $return_part
     * @param $insertion
     * @return mixed
     */
    private function find_and_insert_return_var($script, $find_first, $return_part, $insertion)
    {
        $string_pos_autosaveServer = strpos( $script, $find_first );
        if( $string_pos_autosaveServer ) {
            $string_pos_return_part = strpos( $script, $return_part, $string_pos_autosaveServer );
            if( $string_pos_return_part ) {
                $script = substr_replace( $script, $insertion, $string_pos_return_part + strlen( $return_part ), 0 );
            }
        }
        return $script;
    }

    /**
     * we need to replace some wp autosave js code, to do so we load the script, change it and write it in the footer
     */
    public function hook_wp_footer_wp_autosave_js()
    {
        $script = file_get_contents( ABSPATH . WPINC . '/js/autosave.js' );

        // override save() method in autosaveServer() function
        $script = $this->find_and_insert_return_var( $script, 'autosaveServer()', 'resume: resume', ', __swifty_override: function( a ) { save = a; }' );

        // override getPostData() ,method in autosave() function
        $script = $this->find_and_insert_return_var( $script, 'local: autosaveLocal()', 'server: autosaveServer()', ', __swifty_override: function( a ) { getPostData = a; }' );

        ?>
        <script id="scc_autosave_js" type="text/javascript">
            <?php echo $script; ?>
        </script>
        <?php
    }

    /**
     * return the area that we are currently editing
     *
     * @return bool|mixed false when 'swcreator_edit' is not set
     */
    public function hook_swifty_get_edit_area() {
        $area = false;
        if( isset( $_GET[ 'swcreator_edit' ] ) ) {
            $area = $_GET[ 'swcreator_edit' ];
        } else if( isset( $_GET[ 'swcreator_iframe' ] ) ) {
            $area = $_GET[ 'swcreator_iframe' ];
        }
        if( $area ) {
            // make sure the input is not tampered with
            $area = preg_replace( '/[^0-9a-zA-Z_]/', '', $area );
        }
        return $area;
    }

    /**
     * return the area template that we are currently editing
     * Accept # as default area template
     * Accept @ to create a new area template
     *
     * @return bool|mixed null when 'swcreator_area_template' is not set
     */
    public function hook_swifty_get_edit_area_template() {
        $area_template = null;
        if( isset( $_GET[ 'swcreator_area_template' ] ) ) {
            $area_template = $_GET[ 'swcreator_area_template' ];
        }
        if( $area_template ) {
            // make sure the input is not tampered with
            $area_template = preg_replace( '/[^0-9a-zA-Z_@#]/', '', $area_template );
            if( ( $area_template == '' ) || ( $area_template == '#' ) ) {
                $area_template = null;
            }
        }
        return $area_template;
    }

    /**
     * handle the upload of 1 image from CKEditor
     */
    public function hook_admin_post_swifty_upload() {
        status_header( 200 );

        $uploaded = 0;
        $attach_id = 0;
        $name = '';
        $url = '';
        $result = '';
        $CKEditorFuncNum = -1;
        if( ! empty( $_FILES ) ) {
            $num_files = count( $_FILES[ 'upload' ][ 'tmp_name' ] );
            if( $num_files > 0 ) {
                $CKEditorFuncNum = isset( $_GET[ 'CKEditorFuncNum' ] ) ? intval( $_GET[ 'CKEditorFuncNum' ] ) : -1;
                $name = preg_replace( '/\.(.+?)$/i', '', basename( $_FILES[ 'upload' ][ 'name' ] ) ); // name without extension
                $attach_id = media_handle_upload( 'upload', 0 );
            }
        }
        if( $attach_id ) {
            $img = wp_prepare_attachment_for_js( $attach_id );
            $name = $img[ 'filename' ];
            $url = $img[ 'url' ];
            $uploaded = 1;
            $uploaded_msg = sprintf( __( '%s successfully uploaded', 'swifty-content-creator' ), $name );
            $result = "window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url')";
        } else {
            $uploaded_msg = sprintf( __( 'upload of %s failed', 'swifty-content-creator' ), $name );
        }

        if( isset( $_REQUEST[ 'json' ] ) && ( $_REQUEST[ 'json' ] === '1' ) ) {

            // create swifty image asset for this
            $this->force_include_data_asset_data = true;

            $html = '[swifty_image url="' . $url . '" swc_swifty_on="1"]';
            $html = $this->get_clean_do_shortcode( $this->get_clean_do_shortcode( $html ) );

            header( 'Content-type: application/json' );
            echo json_encode( array(
                'uploaded' => $uploaded,
                'fileName' => $name,
                'url' => $url,
                'error' => array( 'message' => $uploaded_msg ),
                'asset_html' => $html ) );
        } else {
            header( 'Content-type: text/html; charset=utf-8' );
            echo '<script>' . $result . ';</script>';
        }

        die();
    }

    /**
     * Load scrips / css and js localizations
     */
    function hook_wp_head()
    {
        parent::hook_wp_head();
        if( $this->is_editing() ) {
            $pid = get_the_ID();

            $edit_area = $this->hook_swifty_get_edit_area();
            $edit_area_template = $this->hook_swifty_get_edit_area_template();
            $edit_area_new_template = false;
            if( $edit_area_template === '@' ) {
                // create new, find unique area name
                $edit_area_template = apply_filters( 'swifty_new_area_template_name', '', $edit_area );
                // update the global _GET so this will be used when the page is rendered
                $_GET[ 'swcreator_area_template' ] = $edit_area_template;
                $edit_area_new_template = true;
            }
            $edit_page_id = apply_filters( 'swifty_postid_from_area', $pid, $edit_area, $edit_area_template );

            global $scc_build_use;
            global $scc_locale;
            global $scc_version;
            $bust_add = '?swcv=scc_' . $scc_version;

            // Add default WP media manager js/css etc
            wp_enqueue_media();

            $wpSuppliedJsFiles = array(
                'jquery',
                'jquery-ui-draggable',
                'jquery-ui-sortable',
                'jquery-ui-position'
            );

            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery-ui-draggable' );
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'jquery-ui-position' );
            wp_enqueue_script( 'jquery-ui-slider' );

            $view_js_url = '';

            if( $scc_build_use == 'build' ) {
                // split 'swifty-content-' . 'creator' to prevent being found when looking for translations
                wp_enqueue_script( 'determine_image_sizes_js', $this->this_plugin_url . 'js/determine_image_sizes.min.js' . $bust_add, array( 'jquery' ), $scc_version, true );
                wp_enqueue_script( 'swcreator_swcreator_js', $this->this_plugin_url . 'js/swifty-content-' . 'creator.min.js' . $bust_add, $wpSuppliedJsFiles, $scc_version, true );
                wp_enqueue_style( 'swcreator_swcreator_ui_css', $this->this_plugin_url . 'css/swifty-content-' . 'creator.min.css' . $bust_add, false, $scc_version );

                $view_js_url = $this->this_plugin_url . 'js/view.min.js' . $bust_add;
            } else {
                wp_enqueue_script( 'determine_image_sizes_js', $this->this_plugin_url . 'js/diverse/determine_image_sizes.js', array( 'jquery' ), '1.0', true );
                wp_enqueue_script( 'swcreator_require_js', $this->this_plugin_url . 'js/libs/require.js', array( 'jquery' ), '1.0', true );
                wp_enqueue_script( 'swcreator_require_config_js', $this->this_plugin_url . 'require_config.js', array( 'swcreator_require_js' ), '1.0', true );
                wp_enqueue_script( 'swcreator_swcreator_js', $this->this_plugin_url . 'js/swcreator.js', $wpSuppliedJsFiles, '1.0', true );

                $view_js_url = $this->this_plugin_url . 'lib/swifty_plugin/js/view.js';
            }

            // Needed by our own autosave (which is mostly a copy of WP autosave)
            wp_enqueue_script( 'heartbeat' );
            wp_localize_script( 'heartbeat', 'autosaveL10n', array(
                'autosaveInterval' => 1,
                'blog_id' => get_current_blog_id(),
                'swcreator_wp_nonce' => wp_create_nonce( 'update-post_' . $edit_page_id )
            ) );

            // include editor js for removep functionality
            wp_enqueue_script( 'editor' );

            // colorpicker styles and js
            wp_enqueue_style( 'wp-color-picker' );
            //wp_enqueue_script( 'wp-color-picker-script', plugins_url('script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
            wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), false, 1 );

            $scc_options = get_option( 'scc_plugin_options' );
            if( ( $scc_options === false ) || ( ! is_array( $scc_options ) ) ) {
                $scc_options = array();
            }

            $all_pages = get_pages( array(
                'post_type'   => 'page',
                'post_status' => get_post_stati()
            ) );

            $all_posts = get_posts( array(
                'post_type'   => 'post',
                'post_status' => get_post_stati()
            ) );

            if( $all_posts ) {
                $all_pages = array_merge( $all_pages, $all_posts );
            }

            $pages = array();

            foreach( $all_pages as $index => $page ) {
                $path = trim( str_replace( home_url(), '', get_permalink( $page->ID ) ), '/?' );
                $pages[] = 'page_id=' . $page->ID;

                if( $path && ! in_array( $path, $pages ) ) {
                    $pages[] = $path;
                }
            }

            $post_data = get_post( $pid );

            wp_localize_script( 'swcreator_swcreator_js', 'scc_data', array(
                'domain' => $_SERVER[ 'HTTP_HOST' ],
                'page_id' => $edit_page_id,
                'area' => $edit_area,
                'area_template' => $edit_area_template,
                'area_new_template' => $edit_area_new_template,
                'view_id' => $pid,
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'swifty_upload_url' => admin_url( 'admin-post.php?action=swifty_upload&json=1' ),
                'view_url' => get_permalink( $pid ),
                'is_swifty_site_designer_active' => LibSwiftyPluginView::$required_theme_active_swifty_site_designer,
                'media_image_prefix' => apply_filters( 'swifty_ssd_media_image_prefix' , false ),
                'is_swifty_mode' => LibSwiftyPluginView::is_ss_mode(),
                'ajax_nonce' => wp_create_nonce( $this->get_nonce_string( $edit_page_id ) ),
                'ajax_updates_nonce' => wp_create_nonce( 'updates' ),
                'swcreator_url' => $this->this_plugin_url,
                'exit_url' => ($edit_area === 'main' ? get_edit_post_link( $pid ) : get_permalink( $pid ) . '?swcreator_edit=main' ),
//                'swcreator_data' => $this->get_page_data( $pid ), // Get via ajax instead of pasting in html
                'asset_list' => $this->get_asset_list(),
//                'i18n' => $scc_locale,
                'page_title' => get_the_title( $edit_page_id ),
                'admin_page_title' => $this->get_admin_page_title(),
                'locale' => get_option( 'WPLANG' ) ? get_option( 'WPLANG' ) : get_locale(),
                'editor_visibility' => ( isset( $scc_options[ 'editor_visibility' ] ) && $scc_options[ 'editor_visibility' ] === '1' ) ? 1 : 0,
                'ckeditor_path' => $this->this_plugin_url . 'js/components/editor_ckeditor/',
                'pages' => json_encode( $pages ),
                'wpautop_active' => ( /*has_filter( 'the_content', 'swiftyautop' ) ||*/ has_filter( 'the_content', 'wpautop' ) ) ? 1 : 0,
                'swifty_allow_external' => apply_filters( 'swifty_get_allow_external', '' ),
                'swifty_welcome_state' => $this->get_swifty_welcome_state(),
                'swifty_gui_mode' => $this->hook_swifty_get_gui_mode( 'advanced' ),
                'swifty_edit_locked' => current_user_can( 'swifty_edit_locked' ),
                'swifty_change_lock' => current_user_can( 'swifty_change_lock' ) && ( get_option( 'scc_enable_lock_options', 'disabled' ) === 'enabled' ),
                'can_manage_options' => current_user_can( 'manage_options' ) ? '1' : '0',
                'is_content_clipboard_available' => $this->is_content_clipboard_available(),
                'ss2_hosting_name' => get_option( 'ss2_hosting_name' ),
                'active_theme' => get_option( 'template' ),
                'post_type' => $post_data && $post_data->post_type ? $post_data->post_type : null,
                'scc_version' => $scc_version,
                'is_ssm_active' => LibSwiftyPluginView::is_swifty_plugin_active( 'swifty-site' ),
                'home_url' => home_url(),
                'view_js_url' => $view_js_url,
            ) );

            wp_localize_script( 'swcreator_swcreator_js', 'swifty_data', array(
                'i18n' => $scc_locale,
            ) );

            wp_localize_script( 'swcreator_require_config_js', 'scc_data', array(
                'swcreator_url' => $this->this_plugin_url,
                'view_url' => get_permalink( $pid )
            ) );
        }
    }

    /**
     * Create list of assets
     * This will cause ALL assets to load at all times (in edit mode).
     * This need to be done so all asset can register themselves so the can be passed on to js.
     *
     * @return array
     */
    function get_asset_list()
    {
        $asset_list = array();
        foreach( $this->registered_assets as $shortcode => $asset_def ) {
            $this->require_asset( $shortcode );
        }

        foreach( $this->registered_assets as $shortcode => $asset_def ) {
            $asset_list[ $shortcode ] = array(
                'name' => $asset_def[ 'name' ],
                'category' => $asset_def[ 'category' ],
                'icon' => isset( $asset_def[ 'icon' ] ) ? $asset_def[ 'icon' ] : '',
                'order' => isset( $asset_def[ 'order' ] ) ? $asset_def[ 'order' ] : '',
                'width' => isset( $asset_def[ 'width' ] ) ? $asset_def[ 'width' ] : '50',
                'type' => isset( $asset_def[ 'type' ] ) ? $asset_def[ 'type' ] : 'block',
                'paid' => isset( $asset_def[ 'paid' ] ) ? $asset_def[ 'paid' ] : 0,
                'expired' => isset( $asset_def[ 'expired' ] ) ? $asset_def[ 'expired' ] : '',
                'coming' => isset( $asset_def[ 'coming' ] ) ? $asset_def[ 'coming' ] : 0,
                'onchange' => isset( $asset_def[ 'onchange' ] ) ? $asset_def[ 'onchange' ] : null,
                'force_close_tag' => isset( $asset_def[ 'force_close_tag' ] ) ? $asset_def[ 'force_close_tag' ] : 0
            );
        }
        return $asset_list;
    }

    /**
     * Generate WP shortcode 'object' with all options as 'parameters'
     * (used for ajax call)
     *
     * @param $data
     * @return array
     */
    function get_asset_html( $data )
    {
        $data = $this->mixin_default_attributes( $data, $data[ 'swc_shortcode' ], '' );

        $content = '';
        $htmlcode = '[' . $data[ 'swc_shortcode' ];
        foreach( $data as $key => $option ) {
            if( $key === 'content' ) {
                $content = $option;
            } else if( is_int( $key ) ) {
                $htmlcode .= ' ' . $option;
            } else if( $key !== 'swc_shortcode_text' ) {
                $htmlcode .= ' ' . $key . '="' . wptexturize( $option ) . '"';
            }
        }
        $htmlcode .= ']' . $content . '[/' . $data[ 'swc_shortcode' ] . ']';

        global $scc_asset_return_info;
        // Let the correct asset generate the actual html based on the shortcode from above
        $html = $this->get_clean_do_shortcode( $this->get_clean_do_shortcode( $htmlcode ) );

        return array(
            'html' => $html,
            'info' => $scc_asset_return_info
        );
    }

    /**
     * Add css style sheet to tinymce editor
     *
     * @param $styles
     * @return string
     */
    public function hook_mce_css_add_editor_style( $styles ) {
        if( ! empty( $styles ) ) {
            $styles .= ',';
        }
        $styles .= $this->this_plugin_url . 'css/swcreator_mce.css';

        return $styles;
    }

    /**
     * Add edit with Swifty to the page editor.
     */
    function change_default_edit_page()
    {
        global $scc_version, $scc_build_use;

        $pid = get_the_ID();
        $view_url = get_permalink( $pid );

        // does this page needs an update of the image sizes uses in swifty assets? yes, tell which page to use
        $swifty_determine_image_sizes = get_post_meta( $pid, 'swifty_determine_image_sizes', true );
        if( $swifty_determine_image_sizes !== '' ) {
            $url_determine_image_sizes = $view_url;
        } else {
            $url_determine_image_sizes = false;
        }

        $page_data = get_post( $pid );
        if( in_array( $page_data->post_type, self::$edit_post_types ) ) {
            $bust_add = '?swcv=scc_' . $scc_version;
            if( $scc_build_use == 'build' ) {
                wp_enqueue_script( 'determine_image_sizes_js', $this->this_plugin_url . 'js/determine_image_sizes.min.js' . $bust_add, array( 'jquery' ), $scc_version, true );
            } else {
                wp_enqueue_script( 'determine_image_sizes_js', $this->this_plugin_url . 'js/diverse/determine_image_sizes.js' . $bust_add, array( 'jquery' ), $scc_version, true );
            }
            wp_enqueue_script( 'swcreator_swcreator_admin_js', $this->this_plugin_url . 'js/swcreator_admin.js', array( 'jquery' ), $scc_version, true );
            wp_localize_script( 'swcreator_swcreator_admin_js', 'scc_data', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'view_url' => $view_url,
                'is_swifty_site_designer_active' => LibSwiftyPluginView::$required_theme_active_swifty_site_designer,
                'ajax_nonce' => wp_create_nonce( $this->get_nonce_string( $pid ) ),
                'wpautop_option' => $this->get_option_wpautop(),
                'swifty_allow_external' => apply_filters( 'swifty_get_allow_external', '' ),
                'swifty_welcome_state' => $this->get_swifty_welcome_state(),
                'swifty_gui_mode' => $this->hook_swifty_get_gui_mode( 'advanced' ),
                'swifty_edit_locked' => current_user_can( 'swifty_edit_locked' ),
                'swifty_change_lock' => current_user_can( 'swifty_change_lock' ) && ( get_option( 'scc_enable_lock_options', 'disabled' ) === 'enabled' ),
                'is_content_clipboard_available' => $this->is_content_clipboard_available(),
                'page_id' => $pid,
                'ss2_hosting_name' => get_option( 'ss2_hosting_name' ),
                'url_determine_image_sizes' => $url_determine_image_sizes,
                'scc_version' => $scc_version,
//                'swcreator_data' => $this->get_page_data( $pid )
//                'i18n' => array(
//                    'Edit with Swifty' => __( 'Edit with Swifty', 'swifty-content-creator' )
//                )
            ) );

            wp_localize_script( 'swcreator_swcreator_admin_js', 'swifty_data', array(
                'i18n' => array(
                    'Edit with Swifty' => __( 'Edit with Swifty', 'swifty-content-creator' )
                )
            ) );
        }
    }

    /**
     * return clicked if we do no longer want to show the wizard
     * 
     * @return mixed|string
     */
    function get_swifty_welcome_state() {
        if( apply_filters( 'swifty_SS2_hosting_name', false ) ) {
            return 'clicked';
        } else {
            return get_user_option( 'swifty_welcome_state' );
        }
    }

    /**
     * prepare recording of needed js en css files
     */
    function prepare_css_and_js_adding_hijack()
    {
        $this->scc_page_render_styles_added_by_plugins = array(
            'js' => array(),
            'js_inline' => array(),
            'css' => array()
        );

        // Catch style link tag adding by other plugins
        add_filter( 'style_loader_tag', array( $this, 'hook_style_loader_tag' ), 10, 2 );

        // Catch javascript adding by other plugins
        add_filter( 'script_loader_src', array( $this, 'hook_script_loader_src' ), 10, 2 );

        // Catch js and css lazy loading
        add_filter( 'swifty_lazy_load_js', array( &$this, 'hook_swifty_lazy_load_js' ), 10, 5 );
        add_filter( 'swifty_lazy_register_css', array( &$this, 'hook_swifty_lazy_load_css' ), 10, 5 );
        add_filter( 'swifty_lazy_load_css', array( &$this, 'hook_swifty_lazy_load_css' ), 10, 5 );

        // Trigger WP to generate the header which may trigger css and js insertion by other plugins
        // Without ob_start WP would mess with the output type (http header) causing our json output to get screwed
        ob_start();
        wp_head();
        ob_end_clean();
    }

    /**
     * record needed js and cs files that are included in the footer
     *
     * @return mixed
     */
    function do_css_and_js_adding_hijack()
    {
        // Trigger WP to generate the 'footer' which may trigger css and js insertion by other plugins
        // Without ob_start WP would mess with the output type (http header) causing our json output to get screwed
        ob_start();
        wp_footer();
        ob_end_clean();

        return $this->scc_page_render_styles_added_by_plugins;
    }

    /**
     * record needed css url
     *
     * @param $src
     * @param $handle
     */
    function hook_style_loader_tag( $src, $handle )
    {
        global $wp_styles;

        $condition = $wp_styles->get_data( $handle, 'conditional' );
        if( ! $condition ) {
            // when no condition is found then simply add it, we assume now that those conditions are only used for IE,
            // otherwise we need to start testing for 'lt IE 9' and 'lt IE 8'
            $this->scc_page_render_styles_added_by_plugins[ 'css' ][] = $src;
        }
    }

    /**
     * record needed js file
     *
     * @param $src
     * @param $handle
     */
    function hook_script_loader_src( $src, $handle )
    {
        global $wp_scripts;

        $inline_javascript = $wp_scripts->get_data( $handle, 'data' );
        if( $inline_javascript ) {
            $this->scc_page_render_styles_added_by_plugins[ 'js_inline' ][] = array( 'id' => $handle, 'inline' => $inline_javascript );
        }

        // Check if shorter version already exists in array, and if so replace it by the longer one
        $in_array = false;
        foreach( $this->scc_page_render_styles_added_by_plugins[ 'js' ] as &$js ) {
            if( strpos( $src, $js ) !== false ) {
                $in_array = true;
                $js = $src;
            }
        }

        // Add the url if it's not yet in the array.
        if( ! $in_array ) {
            $this->scc_page_render_styles_added_by_plugins[ 'js' ][] = $src;
        }
    }

    /**
     * record needed js file
     *
     * @param $handle
     * @param $src
     * @param $deps
     * @param $ver
     * @param $in_footer
     */
    function hook_swifty_lazy_load_js( $handle, $src, $deps, $ver, $in_footer )
    {
        if( $src ) {
            $this->scc_page_render_styles_added_by_plugins[ 'js' ][] = $src;
        }
    }

    /**
     * record needed css file
     *
     * @param $handle
     * @param $src
     * @param $deps
     * @param $ver
     * @param $media
     */
    function hook_swifty_lazy_load_css( $handle, $src, $deps, $ver, $media )
    {
        if( $src ) {
            $this->scc_page_render_styles_added_by_plugins[ 'css' ][] = '<link rel="stylesheet" href="' . $src . '" type="text/css" media="all">';
        }
    }

    /**
     * ajax call that returns the asset html and all needed css and js files
     * 'data' contains the asset data
     */
    function swcreator_ajax_get_asset_content_by_data() {
        $this->prepare_css_and_js_adding_hijack();

        // IMPACT_ON_SECURITY
        $data = json_decode( stripslashes( $_POST[ 'data' ] ), true );

        $html = $this->get_asset_html( $data );

        // Remove keep-comments that are meant for wpautop
        $html[ 'html' ] = preg_replace( '|<!--keep_swifty_start-->|', '', $html[ 'html' ] );
        $html[ 'html' ] = preg_replace( '|<!--keep_swifty_end-->|', '', $html[ 'html' ] );

        echo json_encode(
            array_merge_recursive(
                $html,
                array( 'js_css_additions' => $this->do_css_and_js_adding_hijack() )
            )
        );

        die();
    }

    /**
     * Get clipboard content
     *
     * @return mixed
     */
    function get_content_clipboard() {
        return get_transient( 'scc_content_clipboard' );
    }

    /**
     * Is there any clipboard content
     *
     * @return bool
     */
    function is_content_clipboard_available() {
        return $this->get_content_clipboard() ? true : false;
    }

    /**
     * Ajax call to get the clipboard content
     */
    function swcreator_ajax_get_content_clipboard() {

        $options = $this->get_content_clipboard();
        if( $options ) {
            echo json_encode(
                array_merge_recursive(
                    $options,
                    array( 'status' => true )
                )
            );
        } else {
            echo json_encode( array( 'status' => false ) );
        }

        die();
    }

    /**
     * Ajax call to set the clipboard, remembers this for a day
     */
    function swcreator_ajax_set_content_clipboard() {
//        Trello G9LnVipB: Gave issues with ModSecurity
//        $options = json_decode( stripslashes( $_POST[ 'options' ] ), true );

        $options = array(
            'content' => stripslashes( $_POST[ 'content' ] ),
            'source' => stripslashes( $_POST[ 'source' ] ),
        );

        set_transient( 'scc_content_clipboard', $options, DAY_IN_SECONDS );
        echo json_encode( array( 'status' => true ) );

        die();
    }

    /**
     * Ajax call to get a style json string for a area template
     * 'name' contains area name
     * 'template' contains area template
     */
    function swcreator_ajax_get_area_template_style() {

        $name = stripslashes( $_POST[ 'name' ] );
        $template = stripslashes( $_POST[ 'template' ] );

        echo apply_filters( 'swifty_get_area_template_style', '{}', $name, $template );

        die();
    }

    /**
     * Ajax call to set a style json string for a area template
     * 'name' contains area name
     * 'template' contains area template
     * 'style' contains area style
     */
    function swcreator_ajax_set_area_template_style() {

        $style = stripslashes( $_POST[ 'style' ] );
        $name = stripslashes( $_POST[ 'name' ] );
        $template = stripslashes( $_POST[ 'template' ] );

        do_action( 'swifty_set_area_template_style', $style, $name, $template );

        echo json_encode( array( 'status' => true ) );

        die();
    }

    /**
     * Ajax call to get a style json string for a post
     * 'id' contains post id
     */
    function swcreator_ajax_get_post_style() {

        $post_id = stripslashes( $_POST[ 'id' ] );

        echo apply_filters( 'swifty_get_post_style', '{}', $post_id );

        die();
    }

    /**
     * Ajax call to set a style json string for a post
     * 'id' contains post id
     * 'style' contains post style
     */
    function swcreator_ajax_set_post_style() {

        $post_id = stripslashes( $_POST[ 'id' ] );
        $style = stripslashes( $_POST[ 'style' ] );

        do_action( 'swifty_set_post_style', $style, $post_id );

        echo json_encode( array( 'status' => true ) );

        die();
    }

    function swcreator_ajax_set_message_states() {

        $messages = json_decode( stripslashes( $_POST[ 'message_states' ] ), true );
        if( is_array( $messages ) ) {
            update_user_option( get_current_user_id(), 'swifty_message_states', $messages );
            echo json_encode( array( 'status' => true ) );
        } else {
            echo json_encode( array( 'status' => false ) );
        }

        die();
    }

    function swcreator_ajax_set_gui_mode() {

        $gui_mode = stripslashes( $_POST[ 'gui_mode' ] );
        update_user_option( get_current_user_id(), 'swifty_gui_mode', $gui_mode );
        echo json_encode( array( 'status' => true ) );

        die();
    }

    function swcreator_ajax_set_welcome_state() {

        $welcome_state = stripslashes( $_POST[ 'welcome_state' ] );
        update_user_option( get_current_user_id(), 'swifty_welcome_state', $welcome_state );
        echo json_encode( array( 'status' => true ) );

        die();
    }

    function swcreator_ajax_set_scc_enable_lock_options() {

        $lock = stripslashes( $_POST[ 'lock' ] );
        update_option( 'scc_enable_lock_options', $lock );
        echo json_encode( array( 'status' => true ) );

        die();
    }

    function swcreator_ajax_get_message_states() {

        $messages = get_user_option( 'swifty_message_states' );
        echo json_encode( $messages );

        die();
    }

    function swcreator_ajax_get_default_messages() {
        $path = WP_PLUGIN_DIR . '/swifty-content-creator/messages';
        $messages = array();

        foreach( glob( $path . '/*.html' ) as $filename ) {
            $fn = $filename;
            $filename = str_replace( $path . '/', '', $filename );
            $filename = str_replace( '.html', '', $filename );
            $pieces = explode( '_-_', $filename );
            array_push( $messages, array(
                'id' => $pieces[ 0 ],
                'titlebar' => $pieces[ 1 ],
                'html' => file_get_contents( $fn )
            ) );
        }

        echo json_encode( $messages );

        die();
    }

    /**
     * Return json object with the swifty plugins as properties with each the following possible properties:
     * - version (always available)
     * - update_status (when update found)
     * - update_version (when update found)
     * - update_slug
     * - update_plugin
     * - update_url (when update found)
     *
     * use this with:

     wp.ajax.post( 'update-plugin', {
        _ajax_nonce:     scc_data.ajax_updates_nonce,
        plugin:          plugin,
        slug:            slug
        } )
        .done( succes method )
        .fail( fail method );

     */
    function swcreator_ajax_get_swifty_plugin_versions() {
        $swifty_plugins_versions = apply_filters( 'swifty_active_plugin_versions', array() );

        // now check for Fast Secure Contact Form
        if( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $all_plugins = get_plugins();
        foreach( $all_plugins as $file_path => $plugin ) {
            if( $file_path === 'si-contact-form/si-contact-form.php' ) {
                $swifty_plugins_versions[ 'si-contact-form' ][ 'status' ] = 'active';
                $swifty_plugins_versions[ 'si-contact-form' ][ 'version' ] = $plugin[ 'Version' ];
            }
        }

        $update_plugins = get_site_transient('update_plugins');
        if ( isset( $update_plugins->response ) ) {
            foreach( (array) $update_plugins->response as $file => $plugin ) {

                if( isset( $swifty_plugins_versions[ $plugin->slug ] ) ) {

                    $status = 'update_available';
                    $update_file = $file;
                    if( current_user_can( 'update_plugins' ) ) {
                        $url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . $update_file ), 'upgrade-plugin_' . $update_file );
                    }
                    $swifty_plugins_versions[ $plugin->slug ][ 'update_status' ] = $status;
                    $swifty_plugins_versions[ $plugin->slug ][ 'update_version' ] = $plugin->new_version;
                    $swifty_plugins_versions[ $plugin->slug ][ 'update_slug' ] = $plugin->slug;
                    $swifty_plugins_versions[ $plugin->slug ][ 'update_plugin' ] = $file;
                    $swifty_plugins_versions[ $plugin->slug ][ 'update_url' ] = $url;
                }
            }
        }

        if( current_user_can( 'update_themes' ) ) {
            $update_themes = get_site_transient( 'update_themes' );
            if( isset( $update_themes->response ) ) {
                foreach( (array) $update_themes->response as $theme_slug => $update_found ) {
                    if( isset( $swifty_plugins_versions[ $theme_slug ] ) ) {
                        $defaults = array( 'new_version' => '', 'url' => '', 'package' => '' );
                        $update_found = wp_parse_args( $update_found, $defaults );

                        $status = 'update_available';
                        $swifty_plugins_versions[ $theme_slug ][ 'update_status' ] = $status;
                        $swifty_plugins_versions[ $theme_slug ][ 'update_version' ] = $update_found[ 'new_version' ];
                        $swifty_plugins_versions[ $theme_slug ][ 'update_slug' ] = $theme_slug;
                    }
                }
            }
        }

        echo json_encode( $swifty_plugins_versions );
        die();
    }


    function swcreator_ajax_upgrade_ssd() {

        global $swifty_lib_dir;
        if( isset( $swifty_lib_dir ) ) {
            require_once $swifty_lib_dir . '/php/lib/class-swifty-theme-installer.php';
        }

        if( class_exists( 'Swifty_Theme_Installer' ) ) {
            $theme_installer = new Swifty_Theme_Installer( 'swiftyget:ssd', new Automatic_Upgrader_Skin() );
            $theme_installer->update_swifty_theme();
        }
        wp_send_json_success( array( 'current_theme' => get_stylesheet() ) );
    }

    /**
     * Convert html with shortcodes into pure html with rendered shortcodes
     * (used for ajax call)
     */
    function swcreator_ajax_convert_html_with_shortcodes()
    {
        $html = stripslashes( $_POST[ 'data' ] );
        list( $html, $shortcode, $plugin_slug ) = $this->remove_licensed_shortcodes( swiftyautop( $html ) );

        $this->prepare_css_and_js_adding_hijack();

        global $scc_asset_return_info;
        $html = $this->get_clean_do_shortcode( $this->get_clean_do_shortcode( $html ) );

        // Remove keep-comments that are meant for wpautop
        $html = preg_replace( '|<!--keep_swifty_start-->|', '', $html );
        $html = preg_replace( '|<!--keep_swifty_end-->|', '', $html );

        // IMPACT_ON_SECURITY
        echo json_encode(
            array(
                'html' => $html,
                'shortcode' => $shortcode,
                'plugin_slug' => $plugin_slug,
                'info' => $scc_asset_return_info,
                'js_css_additions' => $this->do_css_and_js_adding_hijack()
            )
        );

        die();
    }

    /**
     * ajax call that return the html code for the edit toolbar
     * 'shortcode_name' contains the shortcode name
     *
     * vars definition:
     * type - string - (optional) default value is text
     *                 other options are: textarea | colorpicker | color_default | link | icon_button | radiobutton | checkbox
     * label - string - text used as label above the input (not used for icon_button)
     * tooltip - string - (optional) add small tooltip button with provided text
     *
     * text - string - used for checkbox and icon_button
     * action - string - used for icon_button default value is select_images
     *                   other options are: select_image
     *                   remark: for the action select_image a preview image will be shown, this depends on a url vars
     * button_size - string - (optional) used for icon_button default is empty
     *                        other options are: small
     *
     * default_var - string - used for color_default contains the default value when switching to custom color
     * values - string - used for radiobutton contains the possible values in format: option1^option1description|option2^option2description
     * column - int - 0 indexed column
     * row - int - (optional) 0 indexed row default = 0
     * width - int - (optional) width in pixels
     *
     * empty vars array will show the message 'There are no settings for a sitemap.'
     */
    function swcreator_ajax_get_asset_edit_stache()
    {
        // IMPACT_ON_SECURITY
        $asset_name = $_POST[ 'shortcode_name' ];

        $asset_instance = $this->require_asset( $asset_name );

        $default_attributes = array();
        if( isset( $asset_instance ) ) {
            $default_attributes = $asset_instance->get_default_attributes();
        }

        $content_removep = '0';
        $stache = '';
        $asset_def = isset( $this->registered_assets[ $asset_name ] ) ? $this->registered_assets[ $asset_name ] : null;
        if( isset( $asset_def ) ) {

            if( isset( $asset_def[ 'content_removep' ] ) ) {
                $content_removep = $asset_def[ 'content_removep' ];
            }

            if( isset( $asset_def[ 'edit' ] ) ) {
                $stache = $asset_def[ 'edit' ][ 'template' ];
            } else {
                $columns = array();
                if( isset( $asset_def[ 'vars' ] ) ) {
                    if( count( $asset_def[ 'vars' ] ) == 0 ) {
                        $stache = <<<HTML
<div class="swc_form_column" style="width: 400px;">
<label>
<div class="swifty_form_label">
{{__ 'There are no settings.'}}
</div>
</label>
</div>
HTML;
                    } else {
                        foreach( $asset_def[ 'vars' ] as $var => $var_def ) {
                            // do not generate a input for a hidden variable
                            if( ! isset( $var_def[ 'type' ] ) || $var_def[ 'type' ] !== 'hide' ) {

                                $column = $var_def[ 'column' ];

                                if( isset( $column ) && $column >= 0 ) {
                                    if( ! isset( $columns[ $column ] ) ) {
                                        while( count( $columns ) <= $column ) {
                                            $columns[] = array();
                                        }
                                        $columns[ $column ] = array(
                                            'rows' => array(),
                                            'width' => 0
                                        );
                                    }
                                    $type = 'swifty_form_input';
                                    $default_value = '';
                                    $values = '';
                                    $text = isset( $var_def[ 'text' ] ) ? ' text="' . $var_def[ 'text' ] . '"' : '';
                                    $tooltip = isset( $var_def[ 'tooltip' ] ) ? ' tooltip="' . $var_def[ 'tooltip' ] . '"' : '';
                                    $url_selectable = '';
                                    $direction = isset( $var_def[ 'direction' ] ) ? ' direction="' . $var_def[ 'direction' ] . '"' : '';
                                    $preferred_width = isset( $var_def[ 'width' ] ) ? ' preferred_width="' . $var_def[ 'width' ] . '"' : '';
                                    
                                    if( isset( $var_def[ 'type' ] ) ) {
                                        if( $var_def[ 'type' ] === 'textarea' ) {
                                            $type = 'swifty_form_textarea';
                                        } else if( $var_def[ 'type' ] === 'colorpicker' ) {
                                            $type = 'swifty_colorpicker';
                                        } else if( $var_def[ 'type' ] === 'iconpicker' ) {
                                            $type = 'swifty_iconpicker';
                                        } else if( $var_def[ 'type' ] === 'color_default' ) {
                                            $type = 'swifty_form_color_default';
                                            if( isset( $var_def[ 'default_var' ] ) ) {
                                                $default_value = ' default="{{asset_data.' . $var_def[ 'default_var' ] . '}}"';
                                            }
                                        } else if( $var_def[ 'type' ] === 'radiobutton' ) {
                                            $type = 'swifty_form_radiobutton';
                                            if( isset( $var_def[ 'values' ] ) ) {
                                                $values = ' values="' . $var_def[ 'values' ] . '"';
                                            }
                                        } else if( $var_def[ 'type' ] === 'select' ) {
                                            $type = 'swifty_form_select';
                                            if( isset( $var_def[ 'values' ] ) ) {
                                                $values = ' values="' . $var_def[ 'values' ] . '"';
                                            }
                                        } else if( $var_def[ 'type' ] === 'checkbox' ) {
                                            $type = 'swifty_form_checkbox';
                                        } else if( $var_def[ 'type' ] === 'link' ) {
                                            $type = 'swifty_form_link';
                                            $url_selectable = ' url_selectable="1"';
                                        } else if( $var_def[ 'type' ] === 'page' ) {
                                            $type = 'swifty_form_link';
                                            $url_selectable = ' url_selectable="0"';
                                        }
                                    }

                                    $row = 0;
                                    if( isset( $var_def[ 'row' ] ) && $var_def[ 'row' ] > 0 ) {
                                        $row = $var_def[ 'row' ];
                                    }

                                    if( isset( $columns[ $column ][ 'rows' ] ) ) {
                                        while( count( $columns[ $column ][ 'rows' ] ) <= $row ) {
                                            $columns[ $column ][ 'rows' ][] = array();
                                        }
                                    }

                                    $edit_field = '';
                                    if( isset( $var_def[ 'type' ] ) && ( $var_def[ 'type' ] === 'icon_button' ) ) {
                                        $action = isset( $var_def[ 'action' ] ) ? $var_def[ 'action' ] : 'select_images';
                                        $type = isset( $var_def[ 'button_size' ] ) ? ' type="' . $var_def[ 'button_size' ] . '"' : '';
                                        $icon = isset( $var_def[ 'icon' ] ) ? ' icon="' . $var_def[ 'icon' ] . '"' : '';

                                        if( $action === 'select_image' ) {
                                            $edit_field .= '<div class="swc_image_prev_wrapper">';
                                            $edit_field .= '<img src="{{asset_data.url}}">';
                                        }
                                        $edit_field .= '<swc_icon_button class="swc_image_prev_button"'
                                            . $type
                                            . $icon
                                            . ' action = "' . $action . '"'
                                            . $text
                                            . $tooltip
                                            . $direction
                                            . $preferred_width
                                            . ' />';
                                        if( $action === 'select_image' ) {
                                            $edit_field .= '</div>';
                                        }
                                    } else {
                                        $edit_field = '<' . $type . ' name="' . $var
                                            . '" _value="asset_data.' . $var . '"'
                                            . $default_value
                                            . $values
                                            . $text
                                            . $tooltip
                                            . $direction
                                            . $preferred_width
                                            . $url_selectable
                                            . ' label="' . $var_def[ 'label' ] . '"/>';
                                    }
                                    $columns[ $column ][ 'rows' ][ $row ] = $edit_field;

//                                    if( isset( $var_def[ 'width' ] ) && isset( $columns[ $column ][ 'width' ] ) ) {
//                                        if( $var_def[ 'width' ] > $columns[ $column ][ 'width' ] ) {
//                                            $columns[ $column ][ 'width' ] = $var_def[ 'width' ];
//                                        }
//                                    }
                                }
                            }
                        }
                    }
                }
                if( sizeof( $columns ) > 0 ) {
                    foreach( $columns as $column ) {
                        $stache .= '<div class="swc_form_column"';
                        if( isset( $column[ 'width' ] ) && $column[ 'width' ] > 0 ) {
                            $stache .= ' style="width: ' . $column[ 'width' ] . 'px;"';
                        }
                        $stache .= '>';

                        if( isset( $column[ 'rows' ] ) ) {
                            foreach( $column[ 'rows' ] as $row ) {
                                $stache .= $row;
                            }
                        }

                        $stache .= '</div>';
                    }
                }
            }
        }

        if( $stache === '' ) {
            if( $asset_name === 'swifty_text' ) {
                $stache = <<<HTML
<div class="swc_form_column" style="width: 400px;">
<label>
<div class="swifty_form_label">
{{__ 'You can edit the content by clicking inside the text and start typing there.'}}
</div>
</label>
</div>
HTML;
            } else {
                $stache = <<<HTML
<div class="swc_form_column" style="width: 620px;">
<swifty_form_textarea name="swc_shortcode_text" _value="asset_data.swc_shortcode_text" preferred_width="600" label="{{__ 'Shortcode text'}}"/>
</div>
<div class="swc_form_column" style="width: 200px;">
<label>
<div class="swifty_form_label ">
{{asset_data.swc_shortcode_status}}
</div>
</label>
</div>
HTML;
            }
        }

        echo json_encode( array(
            'stache' => $stache,
            'content_removep' => $content_removep,
            'default_attributes' => $default_attributes
        ) );

        die();
    }

    /**
     * ajax call to get the content of post 'id' when published
     */
    function ajax_get_published_data()
    {
        // IMPACT_ON_SECURITY
        $post = get_post( intval( $_POST[ 'id' ] ) );
        if( $post && $this->check_ajax_nonce() ) {
            if( $post->post_status === 'publish' ) {
                echo $post->post_content;
            } else {
                echo '_-NOT-PublisheD-_';
            }

            die();
        }
    }

    /**
     * Ajax call to trigger the determination of the image sizes on a page.
     */
    function ajax_determine_image_sizes() {
        global $swifty_lib_dir;
        if( isset( $swifty_lib_dir ) ) {
            require_once $swifty_lib_dir . '/php/lib/swifty-image-functions.php';
        }

        SwiftyImageFunctions::determine_image_sizes( intval( $_POST[ 'id' ] ), $_POST[ 'ids' ] );

        $this->ajax_get_determine_image_sizes();

        die();
    }

    /**
     * Ajax call to update the determined size of an image on a page.
     */
    function ajax_determine_image_set_size() {
        global $swifty_lib_dir;
        if( isset( $swifty_lib_dir ) ) {
            require_once $swifty_lib_dir . '/php/lib/swifty-image-functions.php';
        }

        SwiftyImageFunctions::determine_image_set_size( intval( $_POST[ 'id' ] ), $_POST[ 'id_asset' ], intval( $_POST[ 'w_viewport' ] ), intval( $_POST[ 'w_asset' ] ), $_POST[ 'image_src' ] );

        die();
    }

    /**
     * Ajax call to get the needed determination of the image sizes on a page.
     */
    function ajax_get_determine_image_sizes() {
        global $swifty_lib_dir;
        if( isset( $swifty_lib_dir ) ) {
            require_once $swifty_lib_dir . '/php/lib/swifty-image-functions.php';
        }

        $sizes = SwiftyImageFunctions::get_determine_image_sizes( intval( $_POST[ 'id' ] ) );

        echo json_encode( array( 'sizes' => $sizes) );

        die();
    }

    /**
     * ajax call to update/store our swifty options
     * 'show_it' contains int 0 or 1
     */
    function ajax_set_editor_visibility()
    {
        // IMPACT_ON_SECURITY
        $show_it = $_POST[ 'show_it' ];

        if( $this->check_ajax_nonce() ) {
            if( ! current_user_can( 'edit_pages' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page. #359', 'swifty-content-creator' ) );
            }

            $options = get_option( 'scc_plugin_options' );
            if( ( $options === false ) || ( ! is_array( $options ) ) ) {
                $options = array();
            }

            $options[ 'editor_visibility' ] = apply_filters( 'scc_plugin_options_default_editor_visibility', $show_it );

            $success = update_option( 'scc_plugin_options', $options );

            echo json_encode( array(
                'success' => $success,
                'show_it' => (int) $show_it
            ), true
            );

            die();
        }
    }

    function ajax_set_wpautop_and_ptag_bottom_margin_and_wp_embed()
    {
        // IMPACT_ON_SECURITY
        $wpautop = $_POST[ 'wpautop' ];
        $ptag_bottom_margin = $_POST[ 'ptag_bottom_margin' ];
        $wp_embed = $_POST[ 'wp_embed' ];

        if( $this->check_ajax_nonce() ) {
            if( ! current_user_can( 'edit_pages' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page. #359', 'swifty-content-creator' ) );
            }

            if( $wp_embed ) {
                update_option( 'scc_wp_embed', $wp_embed );
            }
            $options = get_option( 'scc_plugin_options' );
            if( ( $options === false ) || ( ! is_array( $options ) ) ) {
                $options = array();
            }

            $options[ 'ptag_bottom_margin' ] = apply_filters( 'scc_plugin_options_default_ptag_bottom_margin', $ptag_bottom_margin );
            $options[ 'wpautop' ] = apply_filters( 'scc_plugin_options_default_wpautop', $wpautop );

            $success = update_option( 'scc_plugin_options', $options );

            echo json_encode( array(
                'success' => $success,
                'wpautop' => (int) $wpautop,
                'ptag_bottom_margin' => (int) $ptag_bottom_margin,
                'wp_embed' => $this->get_option_scc_wp_embed()
            ), true
            );

            die();
        }
    }

    /**
     *  Ajax call to update/store swifty_allow_external
     * 'swifty_allow_external' contains string '', 'unknown', 'allow' or 'disallow'
     *  an '' in 'swifty_allow_external' will return the current setting value.
     */
    function ajax_get_set_swifty_allow_external()
    {
        // IMPACT_ON_SECURITY
        $swifty_allow_external = $_POST[ 'swifty_allow_external' ];

        header( 'Content-type: application/json' );

        if( $this->check_ajax_nonce() && in_array( $swifty_allow_external, array( '', 'unknown', 'allow', 'disallow' ), true ) ) {
            $value = apply_filters( 'swifty_get_allow_external', '' );

            if( ( $swifty_allow_external !== $value ) && ( $swifty_allow_external !== '' ) ) {
                do_action( 'swifty_set_allow_external', $swifty_allow_external );
            }

            echo json_encode( array(
                'success' => true,
                'swifty_allow_external' => apply_filters( 'swifty_get_allow_external', '' )
            ), true
            );
        } else {
            echo json_encode( array(
                'success' => false
            ), true
            );
        }

        die();
    }

    /**
     * ajax call to return id from a given attachment url
     *
     * 'url' contains the attachment id
     *
     */
    function ajax_get_attachment_id_from_url()
    {
        global $swifty_lib_dir;
        if( isset( $swifty_lib_dir ) ) {
            require_once $swifty_lib_dir . '/php/lib/swifty-image-functions.php';
        }

        if( $this->check_ajax_nonce() ) {
            if( ! current_user_can( 'edit_pages' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page. #359', 'swifty-content-creator' ) );
            }

            // IMPACT_ON_SECURITY
            $attachment_url = $_POST[ 'url' ];

            // If there is no url, return.
            if( $attachment_url === '' ) {
                return;
            }

            $attachment_id = SwiftyImageFunctions::get_attachment_id_from_url( $attachment_url );

            echo $attachment_id;

            die();
        }
    }

    /**
     * Ajax call to get the content of post 'id' when published.
     */
    function ajax_get_raw_autosave_content() {
        // IMPACT_ON_SECURITY
        if( $this->check_ajax_nonce() ) {
            $newer_revision = LibSwiftyPluginView::get_instance()->get_autosave_version_if_newer( $_POST[ 'id' ] );
            if( isset( $newer_revision ) ) {
                echo $newer_revision;
            } else {
                $post = get_post( intval( $_POST[ 'id' ] ) );
                echo $post->post_content;
            }

            die();
        }
    }

    /**
     * ajax call to get all (non autosave) revisions of a post 'id'
     */
    function ajax_get_published_versions()
    {
        if( $this->check_ajax_nonce() ) {
            // IMPACT_ON_SECURITY
            $revisions = wp_get_post_revisions( intval( $_POST[ 'id' ] ) );
            $versions = array();

            foreach( $revisions as $revision ) {
                if( strpos( $revision->post_name, 'autosave' ) === false ) {
                    $versions[] = array(
                        'ID' => $revision->ID,
                        'post_date_gmt' => $revision->post_date_gmt
                    );
                }
            }

            echo json_encode( array(
                'data' => $versions,
                'date_format' => get_option( 'date_format' ),
                'time_format' => get_option( 'time_format' )
            ), true );

            die();
        }
    }

    /**
     * ajax call to get a specific version 'revision_id'
     */
    function ajax_get_revision()
    {
        if( $this->check_ajax_nonce() ) {
            // make sure the widget wrapper data is included in the content
            $this->force_include_data_asset_data = true;
            // IMPACT_ON_SECURITY, we need a variable because wp_get_post_revision parameter is by reference
            $revision_id = intval( $_POST[ 'revision_id' ] );
            $post = wp_get_post_revision( $revision_id );
            echo do_shortcode( $post->post_content );
            die();
        }
    }

    /**
     * ajax call to update post 'id' with 'content' and publish the post
     * when no 'content' was posted then use latest autosave.
     * when no latest autosave available use the old content of the post
     */
    function ajax_publish()
    {
        if( $this->check_ajax_nonce() ) {
            // Force update even if no normal WP post content has changed
            add_filter( 'wp_save_post_revision_check_for_changes', array( &$this, 'hook_save_post_revision_check_for_changes' ), 999 );

            // IMPACT_ON_SECURITY
            $post = get_post( intval( $_POST[ 'id' ] ) );

            // IMPACT_ON_SECURITY
            if( isset( $_POST[ 'content' ] ) ) {
                $new_content = $_POST[ 'content' ];
            } else {
                $new_content = LibSwiftyPlugin::get_instance()->get_autosave_version_if_newer( intval( $_POST[ 'id' ] ) );
            }

            if( ! isset( $new_content ) ) {
                $new_content = $post->post_content;
            }

            // remove temporary rows / columns / text assets
            $new_content = $this->remove_temporary_shortcodes_from_content( $new_content );

            // Update the post into the database
            // Will also trigger hook_save_post
            // IMPACT_ON_SECURITY
            $return = wp_update_post( array(
                'ID' => intval( $_POST[ 'id' ] ),
                'post_content' => $new_content,
                'post_status' => 'publish'
            ), true );
            if( is_wp_error( $return ) ) {
                echo 'swc wp error: ' . $return->get_error_message();
            } else {
                echo $new_content;

                // if this is a area edit then update the post meta data to use this area
                if( isset( $_POST[ 'area' ] ) && isset( $_POST[ 'view_id' ] ) ) {
                    $areaName = $_POST[ 'area' ];
                    if( $areaName && ( $areaName !== 'main' ) ) {
                        $page_id = get_post( intval( $_POST[ 'view_id' ] ) );
                        $area_template = $_POST[ 'area_template' ];
                        if( $page_id ) {
                            update_post_meta( $page_id->ID, 'spm_' . $areaName . '_template', $area_template ? $area_template : '' );
                        }
                    }
                }
            }

            // Restore normal update mode
            remove_filter( 'wp_save_post_revision_check_for_changes', array( &$this, 'hook_save_post_revision_check_for_changes' ) );

            die();
        }
    }

    /**
     * Delete area post, used when a new area is canceled or existing deleted
     */
    function ajax_delete_area() {
        if( $this->check_ajax_nonce() ) {

            $post = get_post( intval( $_POST[ 'id' ] ) );
            if( $post && ( $post->post_type === 'swifty_area' ) ) {
                wp_delete_post( $post->ID );
            }

            die();
        }
    }

    function ajax_get_area_settings()
    {
        if( ! current_user_can( 'edit_pages' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page. #359', 'swifty-content-creator' ) );
        }

        if( $this->check_ajax_nonce() ) {
            // IMPACT_ON_SECURITY
            $post_id = intval( $_POST[ 'id' ] );
            $post_meta = get_post_meta( $post_id );
            $areas = apply_filters( 'swifty_get_area_template_names', null );
            $area_settings = array();

            foreach ( $areas as $area_name => $area_templates ) {
                $options = array();

                foreach ( $area_templates as $key => $area_template ) {
                    $options[ $key ] = $area_template[ 'name' ];
                }

                array_multisort( $options, SORT_ASC, $area_templates );

                $v_key = 'spm_' . $area_name . '_visibility';
                $t_key = 'spm_' . $area_name . '_template';

                foreach ( array( $v_key, $t_key ) as $key ) {
                    $is_template = preg_match( '/_template$/i', $key );

                    if( isset( $post_meta[ $key ] ) ) {
                        $val = $post_meta[ $key ];
                        $area_settings[ $area_name ][
                            $is_template ? 'active_template' : 'visibility'
                        ] = ( is_array( $val ) && count( $val ) === 1 ) ? $val[ 0 ] : $val;
                    } else {
                        if ( $is_template ) {
                            $area_settings[ $area_name ][ 'active_template' ] = '';
                        } else {
                            $area_settings[ $area_name ][ 'visibility' ] = 'default';
                        }
                    }

                    if( $is_template ) {
                        # If only one template exist, this is the default.
                        $nr_of_templates = count( $area_templates );

                        $area_settings[ $area_name ][ 'templates' ] = $area_templates;
                        $area_settings[ $area_name ][ 'nr_of_templates' ] = $nr_of_templates === 1 ? 0 : $nr_of_templates;

                        if( $nr_of_templates > 1 ) {
                            $template_select_options = array();

                            foreach ( $area_templates as $area_template ) {
                                array_push(
                                    $template_select_options,
                                    $area_template[ 'name' ] . '^' . __( $area_template[ 'title' ], 'swifty-content-creator' )
                                );
                            }

                            $area_settings[ $area_name ][
                                'template_select_options'
                            ] = join( '|', $template_select_options );
                        }
                    }
                }
            }

            wp_send_json_success( $area_settings );
        }
    }

    function ajax_save_area_settings()
    {
        if( ! current_user_can( 'edit_pages' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page. #359', 'swifty-content-creator' ) );
        }

        if( $this->check_ajax_nonce() ) {
            // IMPACT_ON_SECURITY
            $post_id = intval( $_POST[ 'id' ] );
            $area_settings = $_POST[ 'area_settings' ];

            if( $post_id && $area_settings ) {
                foreach( $area_settings as $area_name => $area_settings ) {
                    do_action( 'swifty_set_area_template', $post_id, array(
                        $area_name => $area_settings[ 'active_template' ]
                    ) );

                    update_post_meta( $post_id, 'spm_' . $area_name . '_visibility', $area_settings[ 'visibility' ] );
                }

                wp_send_json_success();
            }
        }
    }

    /**
     * Ajax call 'swcreator_save_area_for_page' to look for existing area with content and create a new one when needed.
     * 'id' = page id to link this area
     * 'area_name' = area which will be linked
     * 'area_visibility' = show / hide / left / right
     * 'area_content' optional - content of area to find
     */
    function ajax_save_area_for_page() {

        if( ! current_user_can( 'edit_pages' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page. #359', 'swifty-content-creator' ) );
        }

        if( $this->check_ajax_nonce() ) {
            // IMPACT_ON_SECURITY
            $post_id = intval( $_POST[ 'id' ] );
            $area_name = $_POST[ 'area_name' ];
            $area_visibility = $_POST[ 'area_visibility' ];
            $area_content = isset( $_POST[ 'area_content' ] ) ? $_POST[ 'area_content' ] : null;
            if( $post_id && $area_name && $area_visibility ) {

                update_post_meta( $post_id, 'spm_' . $area_name . '_visibility', $area_visibility );
                if( $area_content ) {
                    // look for existing areas, create one when needed
                    $areaTemplate = apply_filters( 'swifty_get_area_template', '', $area_name, $area_content );
                    // link this page to the area
                    if( $areaTemplate !== null ) {
                        update_post_meta( $post_id, 'spm_' . $area_name . '_template', $areaTemplate );
                    }
                }

                wp_send_json_success();
            }
        }
    }

    /**
     * ajax call to import the image urls and return the ids, will re-use existing images
     * 'urls' contains array with urls
     * returns json array with
     * - url: new wordpress attachment url
     * - id: new attachment id
     * - image_url: original url
     */
    function ajax_insert_attachment_from_url()
    {
        if( $this->check_ajax_nonce() ) {
            if( ! current_user_can( 'edit_pages' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page. #359', 'swifty-content-creator' ) );
            }

            header( 'Content-type: application/json' );

            // IMPACT_ON_SECURITY
            $attachment_ids = array();
            $attachment_urls = json_decode( stripslashes( $_POST[ 'urls' ] ) );

            foreach( $attachment_urls as $attachment_url ) {
                // If there is no url, ignore.
                if( $attachment_url !== '' ) {
                    $attachment_ids[] = LibSwiftyPlugin::get_instance()->import_attachment_from_url( $attachment_url );
                }
            }
            echo json_encode( $attachment_ids );

            die();
        }
    }

    /**
     * Force update even if no normal WP post content has changed
     *
     * @return bool false
     */
    function hook_save_post_revision_check_for_changes()
    {
        return false;
    }

    /**
     * ajax call to get list of font awesome icons
     */
    public function ajax_get_fa_icon_list()
    {
        if( $this->check_ajax_nonce() ) {
            if( ! current_user_can( 'edit_pages' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page. #359', 'swifty-content-creator' ) );
            }
            if( wp_verify_nonce( $_POST[ 'ajax_nonce' ], $this->get_nonce_string( $_REQUEST[ 'id' ] ) ) ) {

                header( 'Content-type: application/json' );

                $icon_list = array(
                    'content' => FAIcons::icons()
                );

                print json_encode( $icon_list );

                die();
            }
        }
    }

    /**
     * ajax callto get tree of of pages
     */
    public function ajax_get_page_list()
    {
        if( $this->check_ajax_nonce() ) {
            if( ! current_user_can( 'edit_pages' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page. #359', 'swifty-content-creator' ) );
            }

            header( 'Content-type: application/json' );

            $pages = get_pages( array(
                'post_type' => 'page',
                'sort_column' => 'post_title',
                'sort_order' => 'ASC',
                'hierarchical' => 1,
            ) );

            print json_encode( $this->_build_page_list( $pages ) );

            die();
        }
    }

    /**
     * recursively called to build a tree of a list of pages
     *
     * @param array $pages
     * @param int $parent_id
     * @return array
     */
    protected function _build_page_list( array $pages, $parent_id = 0 )
    {
        $page_list = array();

        foreach( $pages as $page ) {

            if( $page->post_parent == $parent_id ) {

                $page_for_list = array(
                    'title' => $page->post_title,
                    'id' => $page->ID,
                    'post_parent' => $page->post_parent,
                    'value' => get_page_link( $page->ID )
                );

                $children = $this->_build_page_list( $pages, $page_for_list[ 'id' ] );

                unset( $page_for_list[ 'post_parent' ] );

                if( $children ) {
                    $page_list[] = $page_for_list;

                    $page_for_list[ 'menu' ] = $children;
                }

                if( isset( $page_for_list[ 'menu' ] ) && ! count( $page_for_list[ 'menu' ] ) ) {
                    unset( $page_for_list[ 'menu' ] );
                }

                $page_list[] = $page_for_list;
            }
        }

        return $page_list;
    }

    /**
     * Remove expired shortcodes from $html.
     * 
     * @param $html
     * @return array htm without expired shortcodes, first expired shortcode, first expired plugin slug
     */
    function remove_licensed_shortcodes( $html ) {

        if( $html ) {
            $shortcodes = array();
            foreach( $this->registered_assets as $shortcode => $asset_def ) {
                if( key_exists( 'expired', $asset_def ) ) {
                    $shortcodes[] = $shortcode;
                }
            }

            if( count( $shortcodes ) > 0 ) {
                // split on shortcodes
                $pattern = get_shortcode_regex( $shortcodes );
                $matches = preg_split( '/' . $pattern . '/s', $html, -1, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_OFFSET_CAPTURE );

                $new_content = '';
                $shortcodes_text = '';
                $offset = 0;
                foreach( $matches as $match ) {
                    $shortcodes_text .= substr( $html, $offset, $match[ 1 ] - $offset );
                    $text = $match[ 0 ];
                    $new_content .= $text;
                    $offset = $match[ 1 ] + strlen( $match[ 0 ] );
                }
                $html = $new_content . substr( $html, $offset );
                if( $shortcodes_text && preg_match('/' . $pattern . '/s', $shortcodes_text, $matches ) ) {
                    return array( $html, $matches[ 2 ] , $this->registered_assets[ $matches[ 2 ] ][ 'plugin' ] );
                }
            }
        }
        return array( $html, '', '');
    }

    /**
     * Register the required plugins for this plugin.
     *
     * In this example, we register two plugins - one included with the STGMPA library
     * and one from the .org repo.
     *
     * The variable passed to stgmpa_register_plugins() should be an array of plugin
     * arrays.
     *
     * This function is hooked into stgmpa_init, which is fired within the
     * Swifty_TGM_Plugin_Activation class constructor.
     */
    function hook_stgmpa_register()
    {

        /**
         * Array of plugin arrays. Required keys are name and slug.
         * If the source is NOT from the .org repo, then source is also required.
         */
        $plugins = array(

//            // This is an example of how to include a plugin pre-packaged with a theme.
//            array(
//                'name'               => 'TGM Example Plugin', // The plugin name.
//                'slug'               => 'tgm-example-plugin', // The plugin slug (typically the folder name).
//                'source'             => get_stylesheet_directory() . '/lib/plugins/tgm-example-plugin.zip', // The plugin source.
//                'required'           => true, // If false, the plugin is only 'recommended' instead of required.
//                'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher.
//                'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
//                'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
//                'external_url'       => '', // If set, overrides default API URL and points to an external URL.
//            ),
//
//            // This is an example of how to include a plugin from a private repo in your theme.
//            array(
//                'name'               => 'TGM New Media Plugin', // The plugin name.
//                'slug'               => 'tgm-new-media-plugin', // The plugin slug (typically the folder name).
//                'source'             => 'https://s3.amazonaws.com/tgm/tgm-new-media-plugin.zip', // The plugin source.
//                'required'           => true, // If false, the plugin is only 'recommended' instead of required.
//                'version'            => '1.0.1',
//                'external_url'       => 'https://github.com/thomasgriffin/New-Media-Image-Uploader', // If set, overrides default API URL and points to an external URL.
//            ),
//
//            // This is an example of how to include a plugin from the WordPress Plugin Repository.
//            array(
//                'name'      => 'BuddyPress',
//                'slug'      => 'buddypress',
//                'required'  => false,
//            ),
//            array(
//                'name'      => 'WP Canvas - Gallery',
//                'slug'      => 'wc-gallery',
//                'version'   => '1.24',
//                'required'  => false,
//            ),
            array(
                'name' => 'Swifty Page Manager',
                'slug' => 'swifty-page-manager',
                'version' => '1.1.0',
                'required' => false,
            ),
            array(
                'name' => 'Fast Secure Contact Form',
                'slug' => 'si-contact-form',
                'version' => '4.0.41',
                'required' => false,
            ),
        );

        /**
         * Array of configuration settings. Amend each line as needed.
         * If you want the default strings to be available under your own theme domain,
         * leave the strings uncommented.
         * Some of the strings are added into a sprintf, so see the comments at the
         * end of each line for what each argument will be.
         */
        // These strings are specific for use in Swifty
        $config = array(
            'default_path' => '',                      // Default absolute path to pre-packaged plugins.
            'menu'         => 'swifty_required_plugins', // Menu slug.
            'menu_url'     => network_admin_url( 'admin.php' ),
            'has_notices'  => true,                    // Show admin notices or not.
            'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
            'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
            'is_automatic' => false,                   // Automatically activate plugins after installation or not.
            'message'      => '',                      // Message to output right before the plugins table.
            'skip_notices_on_pages' => array( 'swifty_page_swifty_content_creator_admin' ),
            'strings'      => array(
                'page_title'                      => __( 'Install Required Plugins', 'swifty-content-creator' ),
                'menu_title'                      => __( 'Install Plugins', 'swifty-content-creator' ),
                'installing'                      => __( 'Installing Plugin: %s', 'swifty-content-creator' ), // %s = plugin name.
                'oops'                            => __( 'Something went wrong with the plugin API.', 'swifty-content-creator' ),
                'notice_can_install_required'     => _n_noop( 'Swifty plugin requires the following plugin: %1$s.', 'Swifty plugin requires the following plugins: %1$s.', 'swifty-content-creator' ), // %1$s = plugin name(s).
                'notice_can_install_recommended'  => _n_noop( 'Swifty plugin recommends the following plugin: %1$s.', 'Swifty plugin recommends the following plugins: %1$s.', 'swifty-content-creator' ), // %1$s = plugin name(s).
                'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'swifty-content-creator' ), // %1$s = plugin name(s).
                'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'swifty-content-creator' ), // %1$s = plugin name(s).
                'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'swifty-content-creator' ), // %1$s = plugin name(s).
                'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'swifty-content-creator' ), // %1$s = plugin name(s).
                'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with Swifty: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with Swifty: %1$s.', 'swifty-content-creator' ), // %1$s = plugin name(s).
                'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'swifty-content-creator' ), // %1$s = plugin name(s).
                'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'swifty-content-creator' ),
                'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins', 'swifty-content-creator' ),
                'return'                          => __( 'Return to Required Plugins Installer', 'swifty-content-creator' ),
                'plugin_activated'                => __( 'Plugin activated successfully.', 'swifty-content-creator' ),
                'complete'                        => __( 'All plugins installed and activated successfully. %s', 'swifty-content-creator' ), // %s = dashboard link.
                'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
            )
        );


        stgmpa( $plugins, $config );
        //
        //Swifty_TGM_Plugin_Activation::get_instance()->update_dismiss();
    }

    /**
     * message showed when needed plugin is not actived
     *
     * @param $plugin_name
     * @return string
     */
    function hook_swifty_plugin_not_active( $plugin_name )
    {
        return sprintf( __( 'Activate plugin "%s" to show this content', 'swifty-content-creator' ), $plugin_name );
    }

    /**
     * We hijack the validation callback to store the 'swifty_gui_mode' setting per user
     *
     * @param $input
     * @return mixed
     */
    function callback_swifty_gui_mode( $input ) {
        update_user_option( get_current_user_id(), 'swifty_gui_mode', $input );
        return $input;
    }

    /**
     * Filter 'swifty_get_gui_mode' gets the 'swifty_gui_mode' setting for the current user.
     *
     * @param $default
     * @return mixed|void
     */
    function hook_swifty_get_gui_mode( $default ) {
        $default = apply_filters( 'swifty_get_gui_mode_default' , $default );
        $value = get_user_option( 'swifty_gui_mode' );
        return $value ? $value : $default;
    }

}
