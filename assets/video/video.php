<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_Video_Asset' ) ) {

    /**
     * Class SWC_Video_Asset show youtube video
     */
    class SWC_Video_Asset extends SWC_Shortcode
    {
        protected $shortcode_name = 'swifty_video';

        /**
         * Get html content that will be inserted for this asset shortcode
         *
         * @return string
         */
        public function get_shortcode_html( $atts )
        {
            wp_enqueue_script( 'swcreator_fitvids_js' );
            wp_enqueue_script( 'video_view_js' );

            $protocol = is_ssl() ? 'https' : 'http';

            $autoplay = apply_filters( 'swifty_lib_is_swifty_hiddenview', false ) ? '' : "&autoplay={$atts['auto_play']}";

            return <<<HTML
<style>
.cke_widget_wrapper .swc_asset_cntnt .swc_video_wrapper .frameOverlay { height: 100%; width: 100%; background: rgba(255, 255, 255, 0.1); position: absolute; top: 0; left: 0; z-index: 1 }
.cke_widget_wrapper .cke_widget_drag_handler_container { z-index: 2 }
.swc_asset_swifty_video .swc_asset_resizer_icon_wrapper { z-index: 2 }
</style>
<div class="swc_video_wrapper">
    <iframe
        src="{$protocol}://www.youtube.com/embed/{$atts['video_id']}?rel=0&autohide=1&modestbranding=1&showinfo=0{$autoplay}"
        frameborder="0"
        width="{$atts['o_width']}"
        height="{$atts['o_height']}"
        allowfullscreen
    >
    </iframe>
</div>
HTML;
        }

        /*
        {{#with info_data}}
            {{#if typ}}
                <div class="swc_form_column">
                    <div class="swifty_form_input_wrapper" style="white-space: nowrap;">
                        <div class="swifty_form_label">
                            {{__ 'Info'}}:
                        </div>
                        <table>
                            {{#if typ}}
                                <tr>
                                    <td>{{__ 'Type'}}: &nbsp; </td>
                                    <td>{{typ}}</td>
                                </tr>
                            {{/if}}
                            {{#if title}}
                                <tr>
                                    <td>{{__ 'Title'}}: &nbsp; </td>
                                    <td>{{title}}</td>
                                </tr>
                            {{/if}}
                            {{#if author}}
                                <tr>
                                    <td>{{__ 'Author'}}: &nbsp; </td>
                                    <td>
                                        {{#if author_url}}
                                            <a href="{{author_url}}" target="_blank">{{author}}</a>
                                        {{else}}
                                            {{author}}
                                        {{/if}}
                                    </td>
                                </tr>
                            {{/if}}
                        </table>
                    </div>
                </div>
            {{/if}}
        {{/with}}
        */


        /**
         * constructor, make sure scripts are loaded
         */
        public function __construct()
        {
            parent::__construct();
            add_action( 'wp_enqueue_scripts', array( $this, 'hook_wp_enqueue_scripts' ) );
            add_filter( 'swifty_asset_check_atts_' . $this->shortcode_name, array( $this, 'hook_swifty_asset_check_atts' ), 10, 2 );
        }

        /**
         * register scripts
         */
        function hook_wp_enqueue_scripts()
        {
            global $scc_build_use;

            $fitvids_url = plugin_dir_url( __FILE__ ) . '../../js/libs/';
            $fitvids_url .= 'jquery.fitvids.' . ( ( $scc_build_use == 'build' ) ? 'min.' : '' ) . 'js';

            wp_register_script(
                'swcreator_fitvids_js',
                $fitvids_url,
                array( 'jquery' ),
                '1.0',
                true
            );

            wp_register_script(
                'video_view_js',
                plugin_dir_url( __FILE__ ) . 'video_view.js',
                array( 'jquery' ),
                false,
                true
            );
        }

        /**
         * Check attributes after edit
         */
        public function hook_swifty_asset_check_atts( $atts, $default_atts )
        {
            return $this->_set_video_atts( $atts );
        }

        /**
         * Make sure attribute auto_play is '1' or '0' value
         */
        private function _set_video_atts( $atts )
        {
            if( isset( $atts[ 'auto_play' ] ) ) {
                $atts[ 'auto_play' ] = $atts[ 'auto_play' ] === '1' ? '1' : '0';
            }
            return $atts;
        }
    }

    new SWC_Video_Asset();
}

add_action('swifty_register_shortcodes', function() {
    /**
     * add this asset as shortcode
     */
    do_action( 'swifty_register_shortcode', array(
        'shortcode' => 'swifty_video',
        'name' => __( 'Video', 'swifty-content-creator' ),
        'type' => 'block',
        'category' => 'visuals',
        'icon' => '&#xe01a;',
        'order' => '20',
        'width' => '50',
        'vars' => array(
            'url' => array(
                'default' => '',
                'label' => __( 'Youtube-link', 'swifty-content-creator' ),
                'tooltip' => __( 'Go to the Youtube.com website, find a video and copy it\'s url. Then paste it here. (The Youtube url\'s come in different types, but all are allowed here.)', 'swifty-content-creator' ),
                'column' => 0,
                'row' => 0,
                'width' => 400,
            ),
            'auto_play' => array(
                'default' => '0',
                'type' => 'checkbox',
                'label' => __( 'Start playing automatically', 'swifty-content-creator' ),
                'text' => __( 'autoplay', 'swifty-content-creator' ),
                'column' => 0,
                'row' => 1
            ),
            'is_youtube' => array(
                'default' => false,
                'type' => 'hide',
            ),
            'video_id' => array(
                'default' => '',
                'type' => 'hide',
            ),
            'o_width' => array(
                'default' => '',
                'type' => 'hide',
            ),
            'o_height' => array(
                'default' => '',
                'type' => 'hide',
            )
        )
    ) );
} );
