<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_Button_Asset' ) ) {

    /**
     * Class SWC_Button_Asset asset for button that opens a link or page on click
     */
    class SWC_Button_Asset extends SWC_Shortcode
    {
        protected $shortcode_name = 'swifty_button';

        /**
         * set default values
         */
        public function __construct()
        {
            parent::__construct();
            add_filter( 'swifty_asset_check_atts_' . $this->shortcode_name, array( $this, 'hook_swifty_asset_check_atts' ), 10, 2 );
        }

        /**
         * is called when attributes are changed in the scc toolbar
         */
        public function hook_swifty_asset_check_atts( $atts, $default_atts )
        {
            return $this->_set_url_atts( $atts );
        }

        /**
         * update atts 'url' with  the link to the selected page or entered url
         */
        private function _set_url_atts( $atts )
        {
            if( isset( $atts[ 'link' ] ) ) {
                $atts[ 'url' ] = '';
                $link_parts = explode( '^^^', $atts[ 'link' ] );

                if( is_array( $link_parts ) && count( $link_parts ) > 2 ) {
                    if( $link_parts[ 0 ] === 'P' ) {
                        $atts[ 'url' ] = get_permalink( $link_parts[ 1 ] );
                    } else if( $link_parts[ 0 ] === 'U' ) {
                        $atts[ 'url' ] = $link_parts[ 2 ];
                    }
                }
            }
            return $atts;
        }

        public function striposa( $haystack, $needle, $offset = 0 ) {
            if( ! is_array( $needle ) ) $needle = array( $needle );
            foreach( $needle as $query ) {
                if( stripos( $haystack, $query, $offset ) !== false ) return true; // stop on first true result
            }
            return false;
        }

        /**
         * Get html content that will be inserted for this asset shortcode
         *
         * @param $html
         * @param $atts
         * @return string
         */
        public function get_shortcode_html( $atts )
        {
            $style_width = "width: 100%;";

            $style_color = $atts[ 'color' ] ? "color:{$atts[ 'color' ]};" : '';
            $style_background = $atts[ 'background' ] ? "background:{$atts[ 'background' ]};" : '';

            $style = '';
            if( $style_width || $style_color || $style_background ) {
                $style = "style=\"$style_width $style_background $style_color\"";
            }

            $class = '';
            if( isset( $atts[ 'style_type' ] ) && $atts[ 'style_type' ] !== 'default' ) {
                $class .= 'class="' . $atts[ 'style_type' ] . '"';
            }

            $method = 'post';
            if( $this->striposa( $atts['url'],
                array( '.jpg', '.jepg', '.gif', '.png', '.bmp', '.tif', '.tiff',
                       '.webp',
                       '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx', '.pdf', '.rtf', '.txt', '.csv',
                       '.zip', '.7z', '.rar',
                       '.mp3', '.wma', '.mid', '.wav',
                       '.mp4', '.mkv', '.mov', '.wmv', '.avi', '.flv' ) ) ) {
                // Url's to files will fail when the form method is POST.
                // So for those we will do a get.
                // Disadvantage is that a '?' is added to the end of the url, which should not be a problem for files.
                // But that is the reason we do not do a get for all url's.
                $method = 'get';
            }

            return <<<HTML
<form action="{$atts['url']}" method="{$method}" target="{$atts['target']}" )>
    <input type="submit" value="{$atts['text']}" {$style} {$class}/>
</form>
HTML;
        }
    }

    new SWC_Button_Asset();
}

add_action('swifty_register_shortcodes', function() {
    /**
     * add this asset as shortcode
     */
    do_action( 'swifty_register_shortcode', array(
        'shortcode' => 'swifty_button',
        'name' => __( 'Button', 'swifty-content-creator' ),
        'type' => 'block',
        'category' => 'interactive',
        'icon' => '&#xe042;',
        'order' => '50',
        'width' => '33',
        'vars' => array(
            'text' => array(
                'default' => __( 'Change this button', 'swifty-content-creator' ),
                'label' => __( 'Button text', 'swifty-content-creator' ),
                'column' => 0,
            ),
            'target' => array(
                'default' => '_self',
                'type' => 'radiobutton',
                'values' => '_blank^' . __( 'New window', 'swifty-content-creator' ) . '|_self^' . __( 'Same window', 'swifty-content-creator' ),
                'label' => __( 'Open URL in:', 'swifty-content-creator' ),
                'column' => 0,
                'row' => 1,
                'width' => 300
            ),
            'link' => array(
                'default' => '',
                'type' => 'link',
                'label' => __( 'Link to page or URL', 'swifty-content-creator' ),
                'column' => 1,
            ),
            'style_type' => array(
                'default' => 'default',
                'type' => 'select',
                'values' =>
                    'default^' . __( 'Default', 'swifty-content-creator' ) .
                    '|swc_style_btn_1^' . __( 'Style 1', 'swifty-content-creator' ) .
                    '|swc_style_btn_2^' . __( 'Style 2', 'swifty-content-creator' ) .
                    '|swc_style_btn_3^' . __( 'Style 3', 'swifty-content-creator' ) .
                    '|swc_style_btn_4^' . __( 'Style 4', 'swifty-content-creator' ) .
                    '|swc_style_btn_5^' . __( 'Style 5', 'swifty-content-creator' ) .
                    '|swc_style_btn_6^' . __( 'Style 6', 'swifty-content-creator' ) .
//                    ( get_option( 'template' ) !== 'swifty-site-designer' ? '' :
//                        '|ssd_style_btn_1^' . __( 'Theme style 1', 'swifty-content-creator' ) .
//                        '|ssd_style_btn_2^' . __( 'Theme style 2', 'swifty-content-creator' )
//                    ) .
                '',
                'label' => __( 'Style type', 'swifty-content-creator' ),
                'column' => 2,
            ),
            'color' => array(
                'default' => '',
                'default_var' => 'default_color',
                'type' => 'color_default',
                'label' => __( 'Color', 'swifty-content-creator' ),
                'column' => 3
            ),
            'default_color' => array(
                'default' => '#FFF',
                'type' => 'hide',
            ),
            'background' => array(
                'default' => '',
                'default_var' => 'default_background',
                'type' => 'color_default',
                'label' => __( 'Background', 'swifty-content-creator' ),
                'column' => 4
            ),
            'default_background' => array(
                'default' => '#F00',
                'type' => 'hide',
            ),

        )
    ) );
} );
