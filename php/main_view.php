<?php
// Exit if accessed directly
defined( 'ABSPATH' ) or exit;

// Asset base classes
require_once( dirname( __FILE__ ) . '/../assets/asset.php' );
require_once( dirname( __FILE__ ) . '/../assets/shortcode.php' );

//require_once( dirname( __FILE__ ) . '/../assets/box/box.php' );
require_once( dirname( __FILE__ ) . '/../assets/contact_form/contact_form.php' );
require_once( dirname( __FILE__ ) . '/../assets/html/html.php' );
//require_once( dirname( __FILE__ ) . '/../assets/search_box/search_box.php' );
require_once( dirname( __FILE__ ) . '/../assets/title/title.php' );
require_once( dirname( __FILE__ ) . '/../assets/video/video.php' );
require_once( dirname( __FILE__ ) . '/../assets/button/button.php' );
//require_once( dirname( __FILE__ ) . '/../assets/sitemap/sitemap.php' );
require_once( dirname( __FILE__ ) . '/../assets/bloglist/bloglist.php' );
//require_once( dirname( __FILE__ ) . '/../assets/gallery_flex/gallery_flex.php' );
//require_once( dirname( __FILE__ ) . '/../assets/gallery_grid/gallery_grid.php' );
//require_once( dirname( __FILE__ ) . '/../assets/slideshow/slideshow.php' );
//require_once( dirname( __FILE__ ) . '/../assets/slider/slider.php' );
//require_once( dirname( __FILE__ ) . '/../assets/maps/maps.php' );
require_once( dirname( __FILE__ ) . '/../assets/image/image.php' );
require_once( dirname( __FILE__ ) . '/../assets/icon/icon.php' );
require_once( dirname( __FILE__ ) . '/../assets/text/text.php' );

require_once( dirname( __FILE__ ) . '/../assets/quote/quote.php' );
require_once( dirname( __FILE__ ) . '/../assets/divider/divider.php' );

// external shortcodes
require_once( dirname( __FILE__ ) . '/../assets/shortcode/shortcode.php' );
require_once( dirname( __FILE__ ) . '/../assets/contact_form_7/contact_form_7.php' );
require_once( dirname( __FILE__ ) . '/../assets/slider_revolution/slider_revolution.php' );

require_once plugin_dir_path( __FILE__ ) . 'class-swifty-disable-embed.php';

global $swifty_autop_comments;
global $swifty_autop_index;
$swifty_autop_comments = array();
$swifty_autop_index = 0;

/**
 * Replace [swifty...] and [/swifty...] shortcodes with replace strings.
 * This prevent the addition of incorrect <p> and <br> tags.
 * swiftyautop_after() will restore the shortcodes.
 *
 * @param $pee
 * @return mixed|string
 */
function swiftyautop_before( $pee ) {
    global $swifty_autop_comments;
    global $swifty_autop_index;

    $pee = preg_replace_callback(
        '|(\[\/?swifty(([\s\S])*?)])|',
        function( $matches ) use ( &$swifty_autop_index, &$swifty_autop_comments ) {
            $name = '==SwIftrEplace-' . $swifty_autop_index++ . '==';
            $swifty_autop_comments[ $name ] = $matches[ 0 ];

            // For block assets make sure the replacer is separate from any html that will follow.
            // For instance you could get ==SwIftrEplace-1==<h3>text</h3>
            // This would be converted by wpautop to <p>==SwIftrEplace-1==<h3>text</h3></p>
            // causing a p tag to appear around the h3. Which is not valid and causes an empty p above the h3 in CKeditor.
            // By adding the line-ends we make sure wpauto will handle the ==SwIftrEplace-1== and the h3 as separate things.
            return $name . "\n\n";
        },
        $pee
    );

    return $pee;
}

/**
 * Restore the shortcodes that were replaced in swiftyautop_before()
 *
 * @param $pee
 * @return mixed|string
 */
function swiftyautop_after( $pee ) {
    global $swifty_autop_comments;

    // By wpautop the replacers, like ==SwIftrEplace-1==, are replaced by <p>==SwIftrEplace-1==</p>\n.
    // So we will restore them here.
    $pee = preg_replace( '|<p>((==SwIftrEplace-\d+==)+)</p>\\n|', '$1', $pee );

    // For block assets the replace above will not work.
    // That is because the p tag (that is inserted bu wpauto) will wrap BOTH the replacer and what follows.
    // For instance TEXT==SwIftrEplace-1==
    // will become <p>TEXT==SwIftrEplace-1==</p>\n
    // Here we will make sure that is converted into <p>TEXT</p>\n==SwIftrEplace-1==
    // so the block asset will not be inside the p tag.
    // When the block assets would be in the p tag, the html is invalid, and CKeditor will change the html
    // so the asset will be forced out of the p tag, causing an extra tect line to apear incorrectly.
    $pee = preg_replace( '|((==SwIftrEplace-\d+==)+)</p>\\n|', '</p>'  . "\n" . '$1', $pee );

    // Replace placeholder <swifty> comments with their original content.
    if( ! empty( $swifty_autop_comments ) ) {
        $pee = str_replace( array_keys( $swifty_autop_comments ), array_values( $swifty_autop_comments ), $pee );
    }

    return $pee;
}

/**
 * Run wpautop and swiftyautop_before and swiftyautop_after if the wpautopfilter is active.
 *
 * @param $pee
 * @param bool|true $br
 * @return mixed|string
 */
function swiftyautop( $pee )
{
    if( has_filter( 'the_content', 'wpautop' ) ) {
        $pee = swiftyautop_before( $pee );
        $pee = wpautop( $pee );
        $pee = swiftyautop_after( $pee );
    }

    return $pee;
}

/**
 * Run wpautop on non SCC post types (page / blog and swifty area).
 * 
 * @param $pee
 * @return string
 */
function swifty_default_wpautop( $pee )
{
    // wpautop is disabled, but it should run by default on other post types, so filter non swifty posttypes and run it
    if( ! in_array( get_post_type(), SwiftyContentCreatorView::$edit_post_types ) ) {
        $pee = wpautop( $pee );
    }

    return $pee;
}

// SS_DOC_ARTICLE
// id_sol: 6953
// id_fd: 11000022105
// id_parent_sol: 6687 // Actions and filters in Swifty Content Creator
// title: Action: swifty_register_shortcodes
// tags: Swifty Content Creator,action
// Plugins can provide SCC with information how their shortcodes can be inserted and edited.<br>
// <br>
// This action is called by SCC for plugins to register their shortcodes with the action 'swifty_register_shortcode'.
// <br>
// <br>
// Example:<br>
// <pre lang="php"><nobr>
//add_action('swifty_register_shortcodes', function() {
//    /**
//     * add this asset as shortcode
//     */
//    do_action( 'swifty_register_shortcode', array( ..... ) );
// }
// </pre lang="php">
// SS_DOC_END


// SS_DOC_ARTICLE
// id_sol: 6954
// id_fd: 11000022106
// id_parent_sol: 6687 // Actions and filters in Swifty Content Creator
// title: Action: swifty_register_shortcode
// tags: Swifty Content Creator,action
// Plugins can provide SCC with information how their shortcodes can be inserted and edited.<br>
// <br>
// By using this action you can provide the information needed to show the edit panel with attributes. The proper moment to
// use this action is in the swifty_register_shortcodes action which is called by SCC after loading the plugins.<br>
// There is 1 argument which is a array which can contain the following keys:<br>
// <i>shortcode</i>: required, shortcode for which the edit panel will be created<br>
// <i>name</i>: required, name used in the edit panel<br>
// <i>type</i>: optional, [block,inline] default block, shorcode generated html is blocking or inline<br>
// <i>category</i>: [clipboard,interactive,textual,layout,others,presets,visuals,thirdparty]<br>
// <i>icon</i>: Unicode character or font-awesome name to show as icon on the insertion button<br>
// <i>order</i>: order nr of shortcodes in this category<br>
// <i>width</i>: default 100, width of generated html in percent<br>
// <i>onchange</i>: ['checkimages'] optional, see description below<br>
// <i>vars</i>: array with attributes of the shortcode and how they can be edited<br>
// <br>
// <b>onchange options</b> These are functions called when a attribute is changed in the edit panel, any function without
// parameters can be used.<br>
// <i>'checkimages'</i>: shortcode creates img tags which uses the swifty responsive images system<br>
// <b>vars attribute</b><br>
// <i>default</i>: default values, can be a string or integer<br>
// <i>type</i>: [swifty_form_input,colorpicker,radiobutton,textarea,color_default,hide,iconpicker,checkbox,link,icon_button,select,page] default swifty_form_input<br>
// <i>values</i> for radiobutton,select: string with items to show, | separates items, item= value^name<br>
// <i>default_var</i> for color_default: attribute to use as default color value<br>
// <i>action</i> for icon_button: ['select_image'] action to perform when clicked<br>
// <i>button_size</i> for icon_button: ['small']<br>
// <i>label</i>: text with the label to show for this attribute<br>
// <i>text</i>: text on control for this attribute<br>
// <i>tooltip</i>: tooltip for this attribute<br>
// <i>column</i>: [0,-] location in edit panel<br>
// <i>row</i>: [0,1] location in edit panel<br>
// <i>direction</i>: ['vertical']<br>
// <i>width</i>: width for this edit field<br>
// <i>content_removep</i>: ['0','1'] default '0', when '1' call removeautop, wpautop on content when setting / getting depending on the autop setting in the scc options<br>
// <i>use_edit_placeholder</i>: ['0','1'] default '0', when '1' show placeholder instead of html when editing<br>
// <br>
// Example:<br>
// <pre lang="php"><nobr>
//add_action('swifty_register_shortcodes', function() {
//    /**
//     * add this asset as shortcode
//     */
//    do_action( 'swifty_register_shortcode', array(
//        'shortcode' => 'swifty_divider',
//        'name' => __( 'Divider', 'swifty-content-creator' ),
//        'type' => 'block',
//        'category' => 'layout',
//        'icon' => 'fa-minus',
//        'order' => '10',
//        'width' => '100',
//        'vars' => array(
//            'divider_bg_color' => array(
//                'default' => '#404040',
//                'type' => 'colorpicker',
//                'label' => __( 'Background color', 'swifty-content-creator' ),
//                'column' => 0,
//                'row' => 0//,
//            ),
//            'divider_height' => array(
//                'default' => '1',
//                'label' => __( 'Height', 'swifty-content-creator' ),
//                'column' => 0,
//                'row' => 1,
//                'width' => 100
//            ),
//        )
//    ) );
//} );
// </pre lang="php">
// SS_DOC_END


/**
 * Class SwiftyContentCreatorView This class contains all bare minimal code that is needed to show a page (to webmaster
 * and visitors) without being in edit mode
 *
 */
class SwiftyContentCreatorView
{
    protected $registered_assets = array();
    protected $asset_instances = array();

    protected $plugin_file;
    protected $plugin_dir;
    protected $plugin_basename;
    protected $plugin_dir_url;
    protected $plugin_version = '3.1.7';

    protected $keep_swifty_start = '';//'<!--keep_swifty_start-->';
    protected $keep_swifty_end = '';//'<!--keep_swifty_end-->';
    protected $force_include_data_asset_data = false;

    // use SCC on those post types
    public static $edit_post_types = array( 'page', 'post', 'swifty_area' );

    private $_in_asset_shortcode = 0;

    private $_styles_included_external = false;

    /**
     * constructor set some members that are specific for the plugin file location and name
     */
    public function __construct()
    {
        $this->init();

        $this->plugin_file     = SWIFTY_CONTENT_CREATOR_PLUGIN_FILE;
        $this->plugin_dir      = dirname( $this->plugin_file );
        $this->plugin_basename = basename( $this->plugin_dir );
        $this->plugin_dir_url  = plugins_url( rawurlencode( basename( $this->plugin_dir ) ) );

        do_action( 'swifty_lib_init_script_hooks' );
    }

    /**
     * Called from constructor, can be inherited toinclude more initialization. Add filters and actions to translate SCC
     * content to a view
     */
    public function init()
    {
        global $swc_creator_plugin_url;
        // The base url of this plugin
        $swc_creator_plugin_url = $this->this_plugin_url = plugin_dir_url( __FILE__ ) . '../';

        // introduce filter to get active Swifty plugins
        add_filter( 'swifty_active_plugins', array( $this, 'hook_swifty_active_plugins' ) );
        add_filter( 'swifty_active_plugin_versions', array( $this, 'hook_swifty_active_plugin_versions' ) );

        // Shortcode registration by our own or 3rd parties must be done as early as possible
        add_action( 'swifty_register_shortcode', array( &$this, 'hook_swifty_register_shortcode' ) );
        add_action( 'init', array( $this, 'hook_init_swifty_register_shortcodes' ) );

//        add_action( 'init', array( $this, 'hook_init_add_image_size' ) );

        add_filter( 'swifty_wrap_shortcode', array( $this, 'hook_wrap_shortcode' ), 10, 4 );
        add_filter( 'swifty_get_default_atts_of_shortcode', array( $this, 'hook_swifty_get_default_atts_of_shortcode' ), 10, 2 );

        if( ! apply_filters('swifty_lib_is_swifty_contentview', false ) ) {
            // replace content by autosave version
            add_filter( 'the_content', array( &$this, 'hook_the_content_check_for_newer_autosave' ), 1 );
        }

        add_filter( 'swifty_is_ajax_call', array( &$this, 'hook_swifty_is_ajax_call' ), 10, 0 );
        add_filter( 'swifty_is_editing', array( &$this, 'hook_swifty_is_editing' ), 10, 0 );

        // Replace the normale page content by content generated from asset data
        add_filter( 'the_content', array( &$this, 'hook_the_content_replace_normal_content_by_asset_content' ), 9999 );
        add_action( 'wp_head', array( &$this, 'hook_wp_head' ) );
        add_action( 'wp_footer', array( &$this, 'hook_wp_footer' ) );

        // handle 'asset_content' calls after everything is loaded
        if( $this->is_ajax_call() ) {
            add_action( 'wp_loaded', array( &$this, 'hook_wp_loaded_handle_swcreator_ajax_calls' ) );
        }

        add_action( 'wp_loaded', array( &$this, 'hook_wp_loaded' ) );
        add_filter( 'swifty_plugin_not_active', array( &$this, 'hook_swifty_plugin_not_active' ), 10, 1 );
        add_action( 'admin_enqueue_scripts', array( &$this, 'hook_admin_enqueue_scripts_thickbox' ) );
        add_action( 'wp_loaded', array( &$this, 'hook_wp_loaded_override_shortcodes' ), 1);
        add_filter( 'get_edit_post_link', array( &$this, 'hook_get_edit_post_link' ), 10, 3 );

        // hide notices when in swifty mode
        add_filter( 'nf_admin_notices', array( $this, 'hook_nf_admin_notices' ), 11 );
        add_action( 'admin_head', array( $this, 'hook_admin_head_hide_notices' ) );

        add_action( 'admin_bar_menu', array( &$this, 'hook_admin_bar_menu' ), 999 );

        add_filter( 'swifty_scc_get_styles', array( $this, 'hook_swifty_scc_get_styles' ), 10, 1 );
        add_filter( 'swifty_scc_get_version', array( $this, 'hook_swifty_scc_get_version' ) );
        
        add_filter( 'get_option_scc_wp_embed', array( $this, 'get_option_scc_wp_embed' ) );

        add_shortcode( 'swifty_grid_row', array( $this, 'hook_swifty_grid_row' ) );
        add_shortcode( 'swifty_grid_column', array( $this, 'hook_swifty_grid_column' ) );

        do_action( 'swifty_init_packs' );

        // Turn wpautop on if our setting demands it and it is not yet active.
        if( $this->get_option_wpautop() === 'on' ) {
            if( ! has_filter( 'the_content', 'wpautop' ) ) {
                add_filter( 'the_content', 'wpautop' );
            }
        }
        // Turn wpautop off if our setting demands it.
        if( $this->get_option_wpautop() === 'off' ) {
            if( has_filter( 'the_content', 'wpautop' ) ) {
                add_filter( 'the_content', 'swifty_default_wpautop' );
            }
            remove_filter( 'the_content', 'wpautop' );
        }
        // Extend wpautop with our before and after actions, so our shortcodes are not messed up by wpautop.
        if( has_filter( 'the_content', 'wpautop' ) ) {
            add_filter( 'the_content', 'swiftyautop_before', 9 );
            add_filter( 'the_content', 'swiftyautop_after' );
        }
    }

    /**
     * Get a cleaned up shortcode render (improved do_shortcode)
     * Remove corrupt p tag that WP leaves at the end of shortcode content
     * dorh This whole function is not longer needed and may be removed
     */
    function get_clean_do_shortcode( $content ) {
        $content = do_shortcode( $content );
// This is no longer needed, I think, because incorrect p tags are no longer inserted
// because of this line above:
// $pee = preg_replace( '|<p>((<!--SwIftrEplace-\d+-->)+)|', '$1<p>', $pee );
//        $content_temp = rtrim( $content );
//        // Remove corrupt p tag that WP leaves at the end of shortcode content
//        if( substr( $content_temp, -3 ) === '<p>' ) {
//            $content = substr( $content_temp, 0, strlen( $content_temp ) - 3 );
//        }
        return $content;
    }

    /**
     * Renders a grid row
     */
    function hook_swifty_grid_row( $atts, $content = null ) {
        $content = $this->get_clean_do_shortcode( $content );

        $json_atts = json_encode( $atts );
        if( $json_atts && $json_atts !== '""' ) {
            $json_atts = htmlspecialchars( json_encode( $atts ) );
        } else {
            $json_atts = '{}';
        }

        $add_class = '';
        if( isset( $atts[ 'swc_breakout' ] ) && $atts[ 'swc_breakout' ] === 'breakout' ) {
            $add_class .= ' swc_breakout';
        }
        if( isset( $atts[ 'swc_content_breakout' ] ) && $atts[ 'swc_content_breakout' ] === 'breakout' ) {
            $add_class .= ' swc_breakout swc_content_breakout';
        }
        if( $this->is_editing() || $this->is_ajax_call() || $this->force_include_data_asset_data ) {
            if( isset( $atts[ 'swc_locked' ] ) ) {
                $add_class .= ' swc_locked';
            }
        }

        $prepend_html = '';
        $add_attr = '';
        $add_style = '';
        if( isset( $atts[ 'swc_bg_color' ] ) ) {
            $add_style .= 'background-color: ' . $atts[ 'swc_bg_color' ] . ';';
        }
        if( isset( $atts[ 'swc_bg_img' ] ) && $atts[ 'swc_bg_img' ] !== '' ) {
            $add_style .= ' background-image: url(\'' . $atts[ 'swc_bg_img' ] . '\');';
            $add_style .= ' background-size: cover;';
            $add_style .= ' background-position: ' .
                ( isset( $atts[ 'swc_bg_position_h' ] ) ? 100.0 * floatval( $atts[ 'swc_bg_position_h' ] ) : 50 ) . '%' .
                '' .
                ( isset( $atts[ 'swc_bg_position_v' ] ) ? 100.0 * floatval( $atts[ 'swc_bg_position_v' ] ) : 50 ) . '%' .
                ';';
            $add_style .= ' background-repeat: ' . ( isset( $atts[ 'swc_bg_repeat' ] ) ? $atts[ 'swc_bg_repeat' ] : 'no-repeat' ) . ';';
            if( ! isset( $atts[ 'swc_bg_size' ] ) || ( isset( $atts[ 'swc_bg_size' ] ) && ( $atts[ 'swc_bg_size' ] === 'cover' || $atts[ 'swc_bg_size' ] === 'contain' ) ) ) {
                $add_style .= ' background-size: ' . ( isset( $atts[ 'swc_bg_size' ] ) ? $atts[ 'swc_bg_size' ] : 'cover' ) . ';';
            } else {
                $add_style .= ' background-size: ' . ( isset( $atts[ 'swc_bg_size_perc' ] ) ? $atts[ 'swc_bg_size_perc' ] . '%' : '100%' ) . ';';
            }
        }
        if( isset( $atts[ 'swc_bg_overlay_trans' ] ) && floatval( $atts[ 'swc_bg_overlay_trans' ] ) < 100 ) {
            $swc_bg_overlay_color = isset( $atts[ 'swc_bg_overlay_color' ] ) ? $atts[ 'swc_bg_overlay_color' ] : '#000000';
            $prepend_html .= '<div class="swc_rw_in_effect" style="opacity:' . ( 1 - floatval( $atts[ 'swc_bg_overlay_trans' ] ) / 100.0 ) . '; background-color:' . $swc_bg_overlay_color . ';"></div>';
        }

        if( isset( $atts[ 'swc_margin_top' ] ) ) {
            $add_style .= ' margin-top: ' . $atts[ 'swc_margin_top' ] . 'px;';
        }
        $margin = 20;
        if( isset( $atts[ 'swc_margin_bottom' ] ) ) {
            $margin = intval( $atts[ 'swc_margin_bottom' ] );
            if( ! $margin > 0 ) {
                $margin = 0;
            }
        }
        $add_style .= 'margin-bottom: ' . $margin . 'px;';

        if( isset( $atts[ 'swc_padding_top' ] ) ) {
            $add_style .= ' padding-top: ' . $atts[ 'swc_padding_top' ] . 'px;';
        }
        if( isset( $atts[ 'swc_padding_bottom' ] ) ) {
            $add_style .= ' padding-bottom: ' . $atts[ 'swc_padding_bottom' ] . 'px;';
        }
        if( isset( $atts[ 'swc_padding_left' ] ) ) {
            $add_style .= ' padding-left: ' . $atts[ 'swc_padding_left' ] . 'px;';
            $add_attr .= 'data-swc_padding_left="' . $atts[ 'swc_padding_left' ] . '"';
        }
        if( isset( $atts[ 'swc_padding_right' ] ) ) {
            $add_style .= ' padding-right: ' . $atts[ 'swc_padding_right' ] . 'px;';
            $add_attr .= 'data-swc_padding_right="' . $atts[ 'swc_padding_right' ] . '"';
        }

        if( isset( $atts[ 'swc_min_height' ] ) && intval( $atts[ 'swc_min_height' ] ) > 0 ) {
            $add_style .= ' min-height: ' . $atts[ 'swc_min_height' ] . 'px;';
        }
        if( isset( $atts[ 'swc_max_height' ] ) && intval( $atts[ 'swc_max_height' ] ) > 0 ) {
            $add_style .= ' max-height: ' . $atts[ 'swc_max_height' ] . 'px;';
        }

        if( isset( $atts[ 'swc_overflow' ] ) && $atts[ 'swc_overflow' ] === 'hidden' ) {
            $add_style .= ' overflow-y: hidden;';
            $add_style .= ' overflow-x: hidden;';
        } else {
            $add_style .= ' overflow-y: visible;';
            if( isset( $atts[ 'swc_max_height' ] ) && intval( $atts[ 'swc_max_height' ] ) > 0 ) {
                $add_style .= ' overflow-x: hidden;';
            }
        }

        if( isset( $atts[ 'swc_scrolleffect' ] ) ) {
            if( $atts[ 'swc_scrolleffect' ] === 'parallax1' || $atts[ 'swc_scrolleffect' ] === 'parallax0' ) {
                $add_attr .= ' data-swc_scrolleffect=\'{' .
                    '"effect":"' . $atts[ 'swc_scrolleffect' ] . '"' .
                    ',"factor":' . ( array_key_exists( 'swc_se_factor', $atts ) ? $atts[ 'swc_se_factor' ] : '1' ) .
                    ',"offset":' . ( array_key_exists( 'swc_se_offset', $atts ) ? $atts[ 'swc_se_offset' ] : '0' ) .
                    '}\'';
            }
        }

        $html = $this->keep_swifty_start
          . '<div class="swc_grid_row ' . $add_class . '" style="' . $add_style . '" data-grid_data="' . $json_atts . '" ' . $add_attr;
        if( isset( $atts[ 'cssid' ] ) ) {
            $html .= ' id="c' . $atts[ 'cssid' ] . '"';
        }
        $html .= '>' . $prepend_html . $this->keep_swifty_end . $content . $this->keep_swifty_start /*. '</div>'*/;

        if( isset( $atts[ 'swc_custom_css' ] ) ) {
            $sel = '#c' . $atts[ 'cssid' ];
            $accolines = 0;
            $css = $sel . ' {' . "\n";
            $lines = explode( "\n", $atts[ 'swc_custom_css' ] );
            foreach( $lines as $line ) {
                if( strpos( $line, '{' ) !== false ) { # !== false compare is deliberate
                    if( $accolines === 0 ) {
                        $css .= '}' . "\n";
                    }
                    $css .= $sel . ' ' . $line . "\n";
                    $accolines++;
                } else {
                    $css .= $line . "\n";
                }
            };
            if( $accolines === 0 ) {
                $css .= '}' . "\n";
            }
            $css = preg_replace( '/\n/', ' ', $css );
            $html .= '<style id="css_' . $atts[ 'cssid' ] . '" class="swc_custom_css">' . $css . '</style><div id="cssclose_' . $atts[ 'cssid' ] . '" class="swc_custom_cssclose"></div>';
        }

        $html .= '</div>' . $this->keep_swifty_end;

        return $html;
    }

    /**
     * Renders a grid column
     */
    function hook_swifty_grid_column( $atts, $content = null ) {
        $content = $this->get_clean_do_shortcode( $content );

        $json_atts = json_encode( $atts );
        if( $json_atts && $json_atts !== '""' ) {
            $json_atts = htmlspecialchars( json_encode( $atts ) );
        } else {
            $json_atts = '{}';
        }

        $add_class = '';
        if( isset( $atts[ 'flex' ] ) ) {
            $add_class = 'swc_flex_' . $atts[ 'flex' ];
        }

        $prepend_html = '';
        $add_style = '';
        $add_attr = '';
        $add_style_col = '';
        $swc_between_cols = 20;
        if( isset( $atts[ 'swc_between_cols' ] ) ) {
            $swc_between_cols = intval( $atts[ 'swc_between_cols' ] );
            if( ! $swc_between_cols > 0 ) {
                $swc_between_cols = 0;
            }
        }
        $add_style .= 'margin-right: ' . $swc_between_cols . 'px;';

        if( isset( $atts[ 'swc_bg_color' ] ) ) {
            $add_style .= ' background-color: ' . $atts[ 'swc_bg_color' ] . ';';
        }
        if( isset( $atts[ 'swc_bg_img' ] ) && $atts[ 'swc_bg_img' ] !== '' ) {
            $add_style .= ' background-image: url(\'' . $atts[ 'swc_bg_img' ] . '\');';
            $add_style .= ' background-size: cover;';
            $add_style .= ' background-position: ' .
                ( isset( $atts[ 'swc_bg_position_h' ] ) ? 100.0 * floatval( $atts[ 'swc_bg_position_h' ] ) : 50 ) . '%' .
                '' .
                ( isset( $atts[ 'swc_bg_position_v' ] ) ? 100.0 * floatval( $atts[ 'swc_bg_position_v' ] ) : 50 ) . '%' .
                ';';
            $add_style .= ' background-repeat: ' . ( isset( $atts[ 'swc_bg_repeat' ] ) ? $atts[ 'swc_bg_repeat' ] : 'no-repeat' ) . ';';
            if( ! isset( $atts[ 'swc_bg_size' ] ) || ( isset( $atts[ 'swc_bg_size' ] ) && ( $atts[ 'swc_bg_size' ] === 'cover' || $atts[ 'swc_bg_size' ] === 'contain' ) ) ) {
                $add_style .= ' background-size: ' . ( isset( $atts[ 'swc_bg_size' ] ) ? $atts[ 'swc_bg_size' ] : 'cover' ) . ';';
            } else {
                $add_style .= ' background-size: ' . ( isset( $atts[ 'swc_bg_size_perc' ] ) ? $atts[ 'swc_bg_size_perc' ] . '%' : '100%' ) . ';';
            }
        }
        if( isset( $atts[ 'swc_bg_overlay_trans' ] ) && floatval( $atts[ 'swc_bg_overlay_trans' ] ) < 100 ) {
            $swc_bg_overlay_color = isset( $atts[ 'swc_bg_overlay_color' ] ) ? $atts[ 'swc_bg_overlay_color' ] : '#000000';
            $prepend_html .= '<div class="swc_rw_in_effect" style="opacity:' . ( 1 - floatval( $atts[ 'swc_bg_overlay_trans' ] ) / 100.0 ) . '; background-color:' . $swc_bg_overlay_color . ';"></div>';
        }

        if( isset( $atts[ 'swc_padding_top' ] ) ) {
            $add_style .= ' padding-top: ' . $atts[ 'swc_padding_top' ] . 'px;';
        }
        if( isset( $atts[ 'swc_padding_bottom' ] ) ) {
            $add_style .= ' padding-bottom: ' . $atts[ 'swc_padding_bottom' ] . 'px;';
        }
        if( isset( $atts[ 'swc_padding_left' ] ) ) {
            $add_style .= ' padding-left: ' . $atts[ 'swc_padding_left' ] . 'px;';
        }
        if( isset( $atts[ 'swc_padding_right' ] ) ) {
            $add_style .= ' padding-right: ' . $atts[ 'swc_padding_right' ] . 'px;';
        }

        if( isset( $atts[ 'swc_min_height' ] ) && intval( $atts[ 'swc_min_height' ] ) > 0 ) {
            $add_style .= ' min-height: ' . $atts[ 'swc_min_height' ] . 'px;';
        }
        // We decided the max height set on rows must NOT be applied to columns.
        // When we want to add max height to columns later on, we MUST use another attribute name ,because user now already have this attribute set to existing columns, but should be ignores.
//        if( isset( $atts[ 'swc_max_height' ] ) && intval( $atts[ 'swc_max_height' ] ) > 0 ) {
//            $add_style .= ' max-height: ' . $atts[ 'swc_max_height' ] . 'px;';
//        }

        if( isset( $atts[ 'swc_pos_vert' ] ) && $atts[ 'swc_pos_vert' ] === 'center' ) {
            $add_style_col .= ' display: flex;';
            $add_style_col .= ' flex-direction: column;';
            $add_style_col .= ' justify-content: center;';
        }
        if( isset( $atts[ 'swc_pos_vert' ] ) && $atts[ 'swc_pos_vert' ] === 'bottom' ) {
            $add_style_col .= ' display: flex;';
            $add_style_col .= ' flex-direction: column;';
            $add_style_col .= ' justify-content: flex-end;';
        }

        if( isset( $atts[ 'swc_scrolleffect' ] ) ) {
            if( $atts[ 'swc_scrolleffect' ] === 'parallax1' || $atts[ 'swc_scrolleffect' ] === 'parallax0' ) {
                $add_attr .= ' data-swc_scrolleffect=\'{' .
                    '"effect":"' . $atts[ 'swc_scrolleffect' ] . '"' .
                    ',"factor":' . ( array_key_exists( 'swc_se_factor', $atts ) ? $atts[ 'swc_se_factor' ] : '1' ) .
                    ',"offset":' . ( array_key_exists( 'swc_se_offset', $atts ) ? $atts[ 'swc_se_offset' ] : '0' ) .
                    '}\'';
            }
        }

        // Right now the swc_grid_colclose is only added for being able to remove the closing div with a regex.
        $content = $this->keep_swifty_start
          . '<div class="swc_grid_colwrapper ' . $add_class . '" style="' . $add_style . '" ' . $add_attr . '><div class="swc_grid_column" style="' . $add_style_col . '" data-grid_data="' . $json_atts . '">'
          . $prepend_html . $this->keep_swifty_end . $content . $this->keep_swifty_start
          . '</div><div class="swc_grid_colclose"></div></div>'
          . $this->keep_swifty_end;

        return $content;
    }

    /**
     * Called via Swifty filter 'swifty_active_plugins'
     *
     * Add the plugin name to the array
     */
    public function hook_swifty_active_plugins( $plugins )
    {
        // split 'swifty-content-' . 'creator' to prevent being found when looking for translations
        $plugins[] = 'swifty-content-' . 'creator';
        return $plugins;
    }

    public function hook_swifty_active_plugin_versions( $plugins )
    {
        global $scc_version;
        $plugins['swifty-content-' . 'creator'] = array( 'version' => $scc_version );

        return $plugins;
    }

    /**
     * Add shortcode asset to list of registered assets
     * Already registered asset values will be over written
     *
     * @param $def
     */
    public function hook_swifty_register_shortcode( $def )
    {
        if( ! isset( $this->registered_assets[ $def[ 'shortcode' ] ] ) ) {
            $this->registered_assets[ $def[ 'shortcode' ] ] = array();
        }

        if( isset( $def[ 'use_edit_placeholder' ] ) ) {
            // create a shortcode class to show a placeholder while editing
            if( apply_filters( 'swifty_is_editing', false ) || apply_filters( 'swifty_is_ajax_call', false ) ) {
                new SWC_Asset_Shortcode( $def[ 'shortcode' ], true );
            }
        }

        // set force_close_tag when content is used as variable
        if( isset( $def[ 'vars' ] ) ) {
            if( isset( $def[ 'vars' ][ 'content' ] ) ) {
                $def[ 'force_close_tag' ] = 1;
            }
        }

        $this->registered_assets[ $def[ 'shortcode' ] ] = array_merge( $this->registered_assets[ $def[ 'shortcode' ] ], $def );
    }

    /**
     * this hook will be called on the init of SCC
     * plugins can register an editor for SCC when the action 'swifty_register_shortcodes' is run,
     * by calling the action 'swifty_register_shortcode'
     */
    public function hook_init_swifty_register_shortcodes()
    {
        do_action('swifty_register_shortcodes');
    }

//    /**
//     * Add our own image size for image attachments, to prevend very big images to be used in the image assets
//     */
//    public function hook_init_add_image_size()
//    {
//        global $content_width;
//        add_image_size( 'swifty_content', $content_width, 9999, false );
//    }

    /**
     * Extend admin bar with options to view autosaved content, view published content and edit page
     */
    function hook_admin_bar_menu()
    {

        if( ! current_user_can( 'edit_pages' ) || is_admin() ) {
            return;
        }

        $pid = get_the_ID();
        $newer_revision = LibSwiftyPluginView::get_instance()->get_autosave_version_if_newer( $pid );

        LibSwiftyPluginView::add_swifty_to_admin_bar();

        global $wp_admin_bar;

        if( isset( $newer_revision ) && $this->is_use_published() ) {
            $args = array(
                'parent' => 'swifty',
                'id' => 'swifty-switch-autosave',
                'title' => __( 'Show autosaved version', 'swifty-content-creator' ),
                'href' => get_permalink()
            );
            $wp_admin_bar->add_node( $args );
        };
        if( isset( $newer_revision ) && ! $this->is_use_published() ) {
            $args = array(
                'parent' => 'swifty',
                'id' => 'swifty-switch-published',
                'title' => __( 'Show published version', 'swifty-content-creator' ),
                'href' => add_query_arg( 'swcreator_published', 'true', get_permalink() )
            );
            $wp_admin_bar->add_node( $args );
        }

        $args = array(
            'parent' => 'swifty',
            'id' => 'swifty-edit',
            'title' => __( 'Edit with Swifty', 'swifty-content-creator' ),
            'href' => add_query_arg( 'swcreator_edit', 'main', get_permalink() )
        );
        $wp_admin_bar->add_node( $args );

    }

    function hook_admin_enqueue_scripts_thickbox()
    {
        add_thickbox();
    }

    /**
     * Override ALL shortcodes so we can add a wrapper.
     * All shortcodes are disabled, only asset shortcodes are allowed to make sure that asset shortcodes are solved first
     *
     */
    function hook_wp_loaded_override_shortcodes()
    {
        global $shortcode_tags;
        global $scc_shortcode_tags;

        // Let's make a back-up of the shortcodes
        $scc_shortcode_tags = $shortcode_tags;

        // Add any shortcode tags that we shouldn't touch here
        $disabled_tags = array(
//            'su_highlight'
            'caption'
        );

        if( isset( $shortcode_tags ) ) {
            foreach( $shortcode_tags as $tag => $cb ) {
                if( in_array( $tag, $disabled_tags ) ) {
                    continue;
                }
                // Overwrite the callback function
                $shortcode_tags[ $tag ] = array( &$this, 'hook_wrap_shortcode' );
            }
        }

        // add shortcodes of not active plugins
        foreach( $this->registered_assets as $asset_name => $asset ) {
            $this->require_asset( $asset_name ); // needed to know 'required_plugin'
            if( ! isset( $shortcode_tags[ $asset[ 'shortcode' ] ] ) && isset( $this->registered_assets[ $asset_name ][ 'required_plugin' ] ) ) {
                $shortcode_tags[ $asset[ 'shortcode' ] ] = array( &$this, 'hook_wrap_shortcode' );
            }
        }
    }

    /**
     * replace editlink by scc editlink in swifty mode (used in search results etc)
     *
     * @param $url
     * @param $id
     * @param $context
     * @return string
     */
    function hook_get_edit_post_link( $url, $id, $context )
    {
        if( LibSwiftyPluginView::is_ss_mode() && ( $context === 'display' ) ) {
            if( ! $post = get_post( $id ) )
                return $url;

            if( 'revision' !== $post->post_type ) {
                $url = get_permalink( $id ) . '?swcreator_edit=main';
            }
        }
        return $url;
    }

    /** hide ninja forms notices when in swifty mode
     *  do this by removing all ninja notices from array
     */
    public function hook_nf_admin_notices( $notices ) {

        if( LibSwiftyPluginView::is_ss_mode() ) {
            $notices = array();
        }
        return $notices;
    }

    /**
     * hide update notices when in swifty mode
     */
    public function hook_admin_head_hide_notices() {
        if( LibSwiftyPluginView::is_ss_mode() ) {
            remove_action( 'admin_notices', 'update_nag', 3 );
        }
    }

    /**
     * Wrap the output of a shortcode.
     * The original callback is called from the $scc_shortcode_tags array when $shortcode_generated_html is not set.
     *
     * @param $attr
     * @param null $content
     * @param $tag
     * @return string
     * @throws Exception
     * @throws null
     */
    function hook_wrap_shortcode( $attr, $content = null, $tag, $shortcode_generated_html = null )
    {
        global $scc_shortcode_tags;
        $type = 'inline';
        $required_plugin_message = '';
        $style = '';
        $style_inner = '';
        $add_attr = '';

        // Text assets can have other assets inside.
        if( $tag === 'swifty_text' ) {
            $content = $this->get_clean_do_shortcode( $content );
        }

        $this->require_asset( $tag );

        if( isset( $this->registered_assets[ $tag ] ) ) {
            if( isset( $this->registered_assets[ $tag ][ 'type' ] ) ) {
                $type = $this->registered_assets[ $tag ][ 'type' ];
            }
            if( isset( $this->registered_assets[ $tag ][ 'required_plugin' ] ) ) {
                $required_plugin = $this->registered_assets[ $tag ][ 'required_plugin' ];
                if( ! LibSwiftyPluginView::is_required_plugin_active( $required_plugin ) ) {
                    $required_plugin_message = apply_filters( 'swifty_plugin_not_active', $required_plugin );
                }
            }
        }

        $is_swifty_asset = isset( $attr[ 'swc_swifty_on' ] ) || ( $tag === 'swifty_text' );

        if( ! $shortcode_generated_html ) {
            // when nesting shortcodes we will not add extra styling
            if( $is_swifty_asset ) {
                $this->_in_asset_shortcode += 1;
            }
            $stored_exc = null;   // we really want to decrease previous increment, with the lack of finally in pre PHP 5.5...
            try {
                $shortcode_generated_html = $required_plugin_message ? $required_plugin_message : call_user_func( $scc_shortcode_tags[ $tag ], $attr, $content, $tag );
            } catch( Exception $exc ) {
                $stored_exc = $exc;
                // Handle an error
            }
            if( $is_swifty_asset ) {
                $this->_in_asset_shortcode -= 1;
            }
            if( $stored_exc ) {
                throw( $stored_exc );
            }
        }

        // insert comment which help the swiftyautop to keep divs etc
        $shortcode_generated_html_pre = $this->keep_swifty_start . $shortcode_generated_html . $this->keep_swifty_end;

        if( ( ! ( $this->is_editing() || $this->is_ajax_call() ) && ! $is_swifty_asset ) || ( $this->_in_asset_shortcode > 0 ) ) {
            return $shortcode_generated_html_pre;
        }

        if( $tag === 'swifty_grid_row' || $tag === 'swifty_grid_column' ) {
            return $shortcode_generated_html;
        }

        // If a block element is inside the generated html, change the type to block automatically
        $block_elements = array( 'address', 'article', 'aside', 'audio', 'blockquote', 'center', 'dd', 'details',
            'dir', 'div', 'dl', 'dt', 'fieldset', 'figcaption', 'figure', 'footer', 'form', 'h', 'h2', 'h3', 'h4',
            'h5', 'h6', 'header', 'hgroup', 'hr', 'li', 'menu', 'nav', 'noframes', 'ol', 'p', 'pre', 'section',
            'table', 'ul', 'video'
        );
        foreach( $block_elements as $block_element ) {
            if( strpos( $shortcode_generated_html, '<' . $block_element ) !== false ) { // !== false is deliberate
                $type = 'block';
            }
        }

        $tag_type = 'span';
        if( $type === 'block' ) {
            $tag_type = 'div';
        }

        $atts = $this->check_attributes( $attr, $content, $tag, $type, $is_swifty_asset );
        $shortcode_name = $tag;

        if( ( $type === 'block' ) && $is_swifty_asset ) {
            $width = intval( $atts[ 'swc_width' ] );
            if( $width === 0 ) {
                $width = 100;
            }

            $style = 'width: ' . $width . '%; ';

            $style .= 'margin-top: ' . $atts[ 'swc_margin_top' ] . 'px; ';
            $style .= 'margin-bottom: ' . $atts[ 'swc_margin_bottom' ] . 'px; ';

            if( ( $atts[ 'swc_position' ] === 'center' ) || ( $width === 100 ) ) {
                $style_inner .= 'margin-left: auto; ';
                $style_inner .= 'margin-right: auto; ';
                $style_inner .= 'width: ' . $atts[ 'swc_width' ] . '%; ';
                $style .= 'width: 100% !important; ';
                $style .= 'clear: both; ';
            } else {
                $style .= 'margin-left: ' . $atts[ 'swc_margin_left' ] . 'px; ';
                $style .= 'margin-right: ' . $atts[ 'swc_margin_right' ] . 'px; ';
            }

            $style_inner .= 'padding: ' .
                $atts[ 'swc_padding_top' ] . 'px ' .
                $atts[ 'swc_padding_right' ] . 'px ' .
                $atts[ 'swc_padding_bottom' ] . 'px ' .
                $atts[ 'swc_padding_left' ] . 'px; ';

            if( ( $atts[ 'swc_position' ] === 'right' ) && ( $width !== 100 ) ) {
                $style .= 'float: right; ';
            }
            if( ( $atts[ 'swc_position' ] === 'left' ) && ( $width !== 100 ) ) {
                $style .= 'float: left; ';
            }

            if( '' . $atts[ 'swc_bg_color' ] !== '' ) {
                $style_inner .= 'background-color: ' . $atts[ 'swc_bg_color' ] . '; ';
            }

            if( '' . $atts[ 'swc_border_color' ] !== '' ) {
                $style_inner .= 'border-color: ' . $atts[ 'swc_border_color' ] . '; ';
                if( intval( '' . $atts[ 'swc_border_width' ] ) > 0 ) {
                    $style_inner .= 'border-width: ' . $atts[ 'swc_border_width' ] . 'px; ';
                    $style_inner .= 'border-style: solid; ';
                }
            }

            if( intval( '' . $atts[ 'swc_shadow_offset_x' ] ) != 0 || intval( '' . $atts[ 'swc_shadow_offset_y' ] ) != 0 ) {
                if( '' . $atts[ 'swc_shadow_color' ] !== '' ) {
                    $style_inner .= 'box-shadow: ' . $atts[ 'swc_shadow_offset_x' ] . 'px '
                        . $atts[ 'swc_shadow_offset_y' ] . 'px ' . $atts[ 'swc_shadow_blur' ] . 'px '
                        . $atts[ 'swc_shadow_color' ] . '; ';
                }
            }
        }

        if( isset( $atts[ 'swc_scrolleffect' ] ) ) {
            if( $atts[ 'swc_scrolleffect' ] === 'move' || $atts[ 'swc_scrolleffect' ] === 'fade' || $atts[ 'swc_scrolleffect' ] === 'movefade' ) {
                $add_attr .= ' data-swc_scrolleffect=\'{' .
                    '"effect":"' . $atts[ 'swc_scrolleffect' ] . '"' .
                    ',"factor":' . ( array_key_exists( 'swc_se_factor', $atts ) ? $atts[ 'swc_se_factor' ] : '1' ) .
                    ',"offset":' . ( array_key_exists( 'swc_se_offset', $atts ) ? $atts[ 'swc_se_offset' ] : '0' ) .
                    ',"direction":"' . ( array_key_exists( 'swc_se_move_dir', $atts ) ? $atts[ 'swc_se_move_dir' ] : 'up' ) . '"'.
                    ',"motion":"' . ( array_key_exists( 'swc_se_move_mot', $atts ) ? $atts[ 'swc_se_move_mot' ] : 'in' ) . '"'.
                    ',"reverse":"' . ( array_key_exists( 'swc_se_move_rev', $atts ) ? $atts[ 'swc_se_move_rev' ] : 'normal' ) . '"'.
                    '}\'';
            }
        }

        $swifty_on_class = '';
        if( $is_swifty_asset ) {
            $swifty_on_class = ' swc_asset_on';
        }

        $include_wrapper_classes = $this->is_editing() || $this->is_ajax_call() || $this->force_include_data_asset_data;

        if( $include_wrapper_classes && isset( $atts[ 'swc_locked' ] ) && $atts[ 'swc_locked' ] ) {
            $swifty_locked_class = ' swc_locked';
        } else {
            $swifty_locked_class = '';
        }

        if( $tag === 'swifty_text' ) {
            $classes = ' class="swc_text' . $swifty_locked_class . '"';
        } else {
            $classes = ' class="swc_asset swc_asset_' . $shortcode_name . $swifty_on_class . $swifty_locked_class . '"';
        }
        $replace_with = $this->keep_swifty_start . '<' . $tag_type
            . $classes
            . ' data-asset_type="' . $shortcode_name . '"'
            . ' style="' . $style . '"'
            . ' ' . $add_attr;
        if( isset( $atts[ 'swc_cssid' ] ) ) {
            $replace_with .= ' id="c' . $atts[ 'swc_cssid' ] . '"';
        }
        $replace_with .= ' >'
            . '<' . $tag_type
            . ' class="swc_asset_cntnt"';

        if( $include_wrapper_classes ) {

            // remove atts that have the default value
            $atts = $this->mixout_default_attributes( $atts, $tag, $type );
            if( $tag === 'swifty_text' ) {
                unset( $atts[ 'content' ] );
                unset( $atts[ 'swc_shortcode' ] );
            }
            $replace_with .= ' data-asset_data="' . base64_encode( json_encode( $atts ) ) . '"';
        }
        $replace_with .=
            ' style="' . $style_inner . '"'
            . ' >'
            . $this->keep_swifty_end . $shortcode_generated_html_pre . $this->keep_swifty_start
            . '</' . $tag_type . '>'
            . '</' . $tag_type . '>' . $this->keep_swifty_end;

        if( $type === 'none' ) {
            $replace_with = '[' . $tag;
            if( is_string( $attr ) ) {
                if( $attr !== '' ) {
                    $replace_with .= ' ' . $attr;
                }
            } elseif( isset( $attr ) ) {
                foreach( $attr as $key => $val ) {
                    $replace_with .= ' ' . $key . '="' . $val . '"';
                }
            }
            $replace_with .= ']';
            if( isset( $content ) && $content !== '' ) {
                $replace_with .= $content . '[/' . $tag . ']';
            }
        }

        return $replace_with;
    }

    /**
     * Remove swifty attributes from non swifty shortcodes
     *
     * @param $atts
     * @param $content
     * @param $tag
     * @param $type
     * @param $is_swifty_asset
     * @return array
     */
    function check_attributes( $atts, $content, $tag, $type, $is_swifty_asset )
    {
        $shortcode_name = $tag;

        $atts[ 'swc_shortcode' ] = $shortcode_name;
        if( $content ) {
            $atts[ 'content' ] = $content;
        }

        $asset_name = $shortcode_name;

        $atts = $this->mixin_default_attributes( $atts, $asset_name, $type );

        // Add a css id if it does not yet exist.
        // This is used for applying custom css to a specific asset.
        // And also used to store responsive image size for specific image assets.
        if( ! isset( $atts[ 'swc_cssid' ] ) ) {
            $atts[ 'swc_cssid' ] = time() . '_' . rand();
        }

        $asset_instance = $this->require_asset( $asset_name );
        if( isset( $asset_instance ) ) {
            $atts = $asset_instance->get_full_attributes( $atts, $content );
        }
        if( ! $is_swifty_asset || ( $type === 'inline' ) ) {
            unset( $atts[ 'swc_bg_color' ] );
            unset( $atts[ 'swc_border_color' ] );
            unset( $atts[ 'swc_border_width' ] );
            unset( $atts[ 'swc_shadow_color' ] );
            unset( $atts[ 'swc_shadow_blur' ] );
            unset( $atts[ 'swc_shadow_offset_x' ] );
            unset( $atts[ 'swc_shadow_offset_y' ] );
            unset( $atts[ 'swc_margin_bottom' ] );
            unset( $atts[ 'swc_margin_left' ] );
            unset( $atts[ 'swc_margin_right' ] );
            unset( $atts[ 'swc_margin_top' ] );
            unset( $atts[ 'swc_padding_bottom' ] );
            unset( $atts[ 'swc_padding_left' ] );
            unset( $atts[ 'swc_padding_right' ] );
            unset( $atts[ 'swc_padding_top' ] );
            unset( $atts[ 'swc_position' ] );
            unset( $atts[ 'swc_width' ] );
            unset( $atts[ 'swc_cssid' ] );
            unset( $atts[ 'swc_locked' ] );
        }

        return $atts;
    }

    /**
     * Add missing attributes and use the shortcode default values for them
     *
     * @param $atts
     * @param $shortcode
     * @param $type
     * @return array
     */
    function mixin_default_attributes( $atts, $shortcode, $type )
    {
        $asset_instance = $this->require_asset( $shortcode );

        if( $type === 'block' ) {
            if( ! isset( $asset_instance ) ) {
                $asset_instance = new SWC_Asset();
            }
        }

        if( isset( $asset_instance ) ) {
            $atts = $asset_instance->get_full_attributes( $atts, null );
        }

        return $this->hook_swifty_get_default_atts_of_shortcode( $atts, $shortcode );
    }

    /**
     * add the 'default' values in the registered_assets to the $atts and return it
     *
     * @param $atts
     * @param $shortcode
     * @return mixed
     */
    public function hook_swifty_get_default_atts_of_shortcode( $atts, $shortcode )
    {
        if( isset( $this->registered_assets[ $shortcode ] ) ) {
            if( isset( $this->registered_assets[ $shortcode ][ 'vars' ] ) ) {
                foreach( $this->registered_assets[ $shortcode ][ 'vars' ] as $key => $var ) {
                    if( ! isset( $atts[ $key ] ) ) {
                        $atts[ $key ] = $var[ 'default' ];
                    }
                }
            }
        }

        return $atts;
    }

    /**
     * Remove attributes that have a default value
     *
     * @param $atts
     * @param $shortcode
     * @param $type
     * @return array
     */
    function mixout_default_attributes( $atts, $shortcode, $type )
    {
        $asset_instance = $this->require_asset( $shortcode );

        if( $type === 'block' ) {
            if( ! isset( $asset_instance ) ) {
                $asset_instance = new SWC_Asset();
            }
        }

        if( isset( $asset_instance ) ) {
            $atts = $asset_instance->get_non_default_attributes( $atts );
        }

        return $atts;
    }

    /**
     * instanciate asset class one time, load corresponding php file
     *
     * @param $asset
     * @return asset instance
     */
    function require_asset( $asset )
    {
        if( ! isset( $this->asset_instances[ $asset ] ) ) {

            $type = 'block';
            if( isset( $this->registered_assets[ $asset ] ) ) {
                if( isset( $this->registered_assets[ $asset ][ 'type' ] ) ) {
                    $type = $this->registered_assets[ $asset ][ 'type' ];
                }
            }

            $this->asset_instances[ $asset ] = new SWC_Asset();
            $this->asset_instances[ $asset ]->set_shortcode_name( $asset, $type );
        }
        return $this->asset_instances[ $asset ];
    }

    /**
     * If there is a newer autosave version, use that instead of the pages content
     * do this whule editing and user has edit permission
     *
     * @param $content
     * @return mixed|null
     */
    function hook_the_content_check_for_newer_autosave( $content )
    {
        if( $this->is_editing() || ( current_user_can( 'edit_pages' ) && ! $this->is_use_published() ) ) {
            $pid = get_the_ID();
            $newer_revision = LibSwiftyPluginView::get_instance()->get_autosave_version_if_newer( $pid );

            if( isset( $newer_revision ) ) {
                // Newer autosave found; use that autosaved content automatically.
                // WP would show a message about this; Swifty shows no message but just uses the latest autosave automatically.
                // That is because Swifty does autosaves 'constantly' but never does an official save (update).
                return $newer_revision;
            }
        }

        // Return the normal WP content.
        return $content;
    }

    /**
     * Place swifty div around content
     *
     * @param $content
     * @return string
     */
    function hook_the_content_replace_normal_content_by_asset_content( $content )
    {
        if( $this->_in_asset_shortcode === 0 ) {
            $class_ptag = $this->use_ptag0() ? ' swc_p_0' : '';

            $class_swc_page_cntnt = '';
            $edit_area = $this->hook_swifty_get_edit_area();

            if( $edit_area ) {
                // is this the a page or post and not a swifty area?
                $post = get_post();
                if( ! empty( $post ) ) {
                    $class_swc_page_cntnt .= ( ( $edit_area === 'main' ) && ( $post->post_type !== 'swifty_area' ) ) ? ' swc_main_content_pane' : '';
                }
            }
            $class_swc_page_cntnt .= $edit_area ? ' swc_page_cntnt' : '';

            if( ! empty( $class_ptag ) || ! empty( $class_swc_page_cntnt ) ) {
                return '<div class="' . $class_swc_page_cntnt . $class_ptag . '">' . $content . '</div>';
            }
        }
        // Not a Swifty page; return the normal WP content.
        return $content;
    }

    /**
     * Check for scc settings
     * Creates a default setting for removing the bottom margin of p tags and editor visibility
     * Using filters to change the default values:
     * - scc_plugin_options_default_ptag_bottom_margin
     * - scc_plugin_options_default_editor_visibility
     */
    function hook_wp_loaded()
    {
        $options = get_option( 'scc_plugin_options' );

        // Must be explicit boolean false ( === ), because then the options don't exist yet.
        if( ( false === $options ) || ! is_array( $options ) ) {
            $options = array();

            $options[ 'ptag_bottom_margin' ] = apply_filters( 'scc_plugin_options_default_ptag_bottom_margin', '0' );
            $options[ 'editor_visibility' ] = apply_filters( 'scc_plugin_options_default_editor_visibility', '1' );
            $options[ 'wpautop' ] = apply_filters( 'scc_plugin_options_default_wpautop', 'default' );
            $options[ 'attachmentsizes' ] = apply_filters( 'scc_plugin_options_default_attachmentsizes', 'swifty' );

            update_option( 'scc_plugin_options', $options );
        }
    }

    /**
     * include swcreator css style in page
     */
    function hook_wp_head()
    {
        LibSwiftyPluginView::lazy_register_css( 'swifty-font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css' );
        
        if( ! $this->_styles_included_external ) {
            global $scc_version;
//        wp_enqueue_style( 'swcreator_swcreator_css', $this->this_plugin_url . 'css/swcreator.css', false, $scc_version );
            LibSwiftyPluginView::lazy_register_css( 'swcreator_swcreator_css', $this->this_plugin_url . 'css/swcreator.css', false, $scc_version );
        }
    }

    function hook_wp_footer()
    {
        $styleObject = apply_filters( 'swifty_get_area_template_style', '', 'page', '' );
        $styleObject = $styleObject ? $styleObject : '{}';
        echo '<script>';
        echo 'if( typeof swifty_ssd_page_styles === "undefined" ) { swifty_ssd_page_styles = {}; }; ';
        echo 'swifty_ssd_page_styles.page_ = ' . $styleObject . ';';
        echo '</script>';
    }

    /*
     * Editing a Swifty page?
     */
    function is_editing()
    {
        return isset( $_GET[ 'swcreator_edit' ] ) || isset( $_GET[ 'swcreator_iframe' ] );
    }

    /*
     * Showing SCC editor page?
     */
    function is_swc_editor()
    {
        return isset( $_GET[ 'swcreator_edit' ] );
    }

    /**
     * return the area that we are currently editing, return always false in view mode
     *
     * @return bool|mixed false when 'swcreator_edit' is not set
     */
    public function hook_swifty_get_edit_area()
    {
        return false;
    }

    /**
     * return the area template that we are currently editing
     *
     * @return bool|mixed null when 'swcreator_area_template' is not set
     */
    public function hook_swifty_get_edit_area_template()
    {
        return null;
    }

    /**
     * enforce published version?
     *
     * @return bool
     */
    function is_use_published()
    {
        return isset( $_GET[ 'swcreator_published' ] );
    }

    /**
     * is this a swifty ajax call?
     *
     * @return bool
     */
    function is_ajax_call()
    {
        return isset( $_GET[ 'swcreator_ajax' ] );
    }

    /**
     * Is the option for p tag 0 set?
     *
     * @return bool
     */
    function use_ptag0()
    {
        $options = get_option( 'scc_plugin_options' );
        return ( isset( $options ) && is_array( $options ) && isset( $options[ 'ptag_bottom_margin' ] ) && $options[ 'ptag_bottom_margin' ] === '1' );
    }

    /**
     * get the wpautop swifty option, should return 'on', 'off' or 'default'
     *
     * @return string
     */
    function get_option_wpautop() {
        $options = get_option( 'scc_plugin_options' );

        if( ( $options === false ) || ( ! is_array( $options ) ) ) {
            $options = array();
        }
        if( ! array_key_exists( 'wpautop', $options ) ) {
            $options[ 'wpautop' ] = apply_filters( 'scc_plugin_options_default_wpautop', 'default' );
        }
        return $options[ 'wpautop' ];
    }

    /**
     * get the attachmentsizes option, should return 'wp', 'swifty' or 'all'
     *
     * @return string
     */
    function get_option_attachmentsizes() {
        $options = get_option( 'scc_plugin_options' );

        if( ( $options === false ) || ( ! is_array( $options ) ) ) {
            $options = array();
        }
        if( ! array_key_exists( 'attachmentsizes', $options ) ) {
            $options[ 'attachmentsizes' ] = apply_filters( 'scc_plugin_options_default_attachmentsizes', 'swifty' );
        }
        return $options[ 'attachmentsizes' ];
    }

    /**
     * Get the scc_wp_embed option, should return 'default' or 'disable'. 
     * Use 'disable' as default value on hosting sites, otherwise 'default'.
     * 
     * @return mixed|void
     */
    function get_option_scc_wp_embed() {
        $default = ( apply_filters( 'swifty_SS2_hosting_name', '' ) === '' ? 'default' : 'disable' );
        return get_option( 'scc_wp_embed', $default );
    }

    /**
     * create the nonce string
     *
     * @param $id
     * @return string
     */
    function get_nonce_string( $id )
    {
        return 'current page id' . $id;
    }

    /**
     * Is the ajax_nonce correct?
     *
     * @return false|int
     */
    function check_ajax_nonce()
    {
        // IMPACT_ON_SECURITY
        return wp_verify_nonce( $_REQUEST[ 'ajax_nonce' ], $this->get_nonce_string( $_REQUEST[ 'id' ] ) );
    }

    /**
     * handle the ajax call, always test the nonce.
     * id, action and ajax_nonce needs to be set in the js ajax call
     * no need to check the ajax nonce again in the ajax handler
     */
    function hook_wp_loaded_handle_swcreator_ajax_calls()
    {
        if( $this->check_ajax_nonce() ) {
            do_action( 'swcreator_ajax_' . $_REQUEST[ 'action' ] );
        }

        die( '0' );
    }

    /**
     * message showed when needed plugin is not actived, render empty asset in view mode
     *
     * @param $plugin_name
     * @return string
     */
    function hook_swifty_plugin_not_active( $plugin_name )
    {
        return '';
    }

    /**
     * hook for returning is_ajax_call
     *
     * @return bool
     */
    function hook_swifty_is_ajax_call()
    {
        return $this->is_ajax_call();
    }

    /**
     * hook for returning is_editing
     *
     * @return bool
     */
    function hook_swifty_is_editing()
    {
        return $this->is_editing();
    }

    /**
     * Return minified filename, if exists; otherwise original filename
     */
    protected function _find_minified( $file_name )
    {
        $file_name_min = preg_replace( '|\.js$|', '.min.js', $file_name );

        if( file_exists( $this->plugin_dir . $file_name_min ) ) {
            $file_name = $file_name_min;
        }

        return $file_name;
    }

    /**
     * return the content of de swcreator.css file
     *
     * @return string
     */
    public function hook_swifty_scc_get_styles( $val )
    {
        if( ! ( isset( $val ) && $val === '_NOT_SET_FLAG_' ) ) {
            $this->_styles_included_external = true;
        }
        return file_get_contents( dirname( dirname( __FILE__ ) ) . '/css/swcreator.css' );
    }

    /**
     * Return the content of SCC, used for loading the external scc + ssd styles.
     * 
     * @return string
     */
    public function hook_swifty_scc_get_version() {
        return $this->plugin_version;
    }
}
