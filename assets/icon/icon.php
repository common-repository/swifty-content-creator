<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_Icon_Asset' ) ) {

    /**
     * Class SWC_Icon_Asset
     * Icon shows fontawesome characters with size and color
     */
    class SWC_Icon_Asset extends SWC_Shortcode
    {
        protected $shortcode_name = 'swifty_icon';

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
        private function _set_url_atts( $atts ) {
            if( ! isset( $atts[ 'swc_bg_color' ] ) || ( $atts[ 'swc_bg_color' ] === '' ) ) {
                $atts[ 'swc_bg_color' ] = '#';
            }
            if( isset( $atts[ 'color_background' ] ) && ( $atts[ 'color_background' ] === '' ) ) {
                unset( $atts[ 'color_background' ] );
            }
            if( isset( $atts[ 'color' ] ) && ( $atts[ 'color' ] === '' ) ) {
                unset( $atts[ 'color' ] );
            }
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

        /**
         * Get html content that will be inserted for this asset shortcode
         *
         * @param $html
         * @param $atts
         * @return string
         */
        public function get_shortcode_html( $atts )
        {
            LibSwiftyPluginView::lazy_load_css( 'swifty-font-awesome' );

            $size = intval( $atts[ 'size' ] ) . 'px';
            $size_bg = intval( $atts[ 'size_background' ] ) . 'px';

            $size_max = $size;
            if( $atts[ 'show_background_icon' ] ) {
                $size_max = max( intval( $size ), intval( $size_bg ) ) . 'px';
            }

            $icon_html = '<div style="position: relative; font-size: ' . $size_max . '; line-height: ' . $size_max . '; text-align: center;">' .
            '<span style="visibility: hidden;">&nbsp;</span>';
            if( $atts[ 'show_background_icon' ] ) {
                $icon_html .= '<span style="font-size: ' . $size_bg . '; position: absolute; left: 0; right: 0;">' .
                '<i class="fa ' . $atts[ 'icon_background' ] . '" style="color:' . $atts[ 'color_background' ] . '"></i>' .
                '</span>';
            }
            $icon_html .= '<span style="font-size: ' . $size . '; position: absolute; left: 0; right: 0;">' .
            '<i class="fa ' . $atts[ 'icon' ] . '" style="color:' . $atts[ 'color' ] . '"></i>' .
            '</span>' .
            '</div>';

            if( $atts[ 'url' ] === '' ) {
                return $icon_html;
            } else {
                return '<a href="' . $atts[ 'url' ] . '" target="' . $atts['target'] . '">' . $icon_html . '</a>';
            }
        }
    }

    new SWC_Icon_Asset();
}

add_action('swifty_register_shortcodes', function() {
    /**
     * add this asset as shortcode
     */
    do_action( 'swifty_register_shortcode', array(
        'shortcode' => 'swifty_icon',
        'name' => __( 'Icon', 'swifty-content-creator' ),
        'type' => 'block',
        'category' => 'visuals',
        'icon' => 'fa-rocket',
        'order' => '25',
        'width' => '20',
        'vars' => array(

            'icon' => array(
                'default' => 'fa-star',
                'type' => 'iconpicker',
                'label' => __( 'Pick icon', 'swifty-content-creator' ),
                'column' => 0//,
//                'width' => 100
            ),
            'size' => array(
                'default' => '70',
                'label' => __( 'Size', 'swifty-content-creator' ),
                'column' => 0,
                'row' => 1,
                'width' => 100
            ),
            'color' => array(
                'default' => '#444',
                'type' => 'colorpicker',
                'label' => __( 'Color', 'swifty-content-creator' ),
                'column' => 1//,
//                'width' => 100
            ),
            'swc_bg_color' => array(
                'default' => '#',
                'type' => 'colorpicker',
                'label' => __( 'Background color', 'swifty-content-creator' ),
                'column' => 1,
                'row' => 1//,
//                'width' => 100
            ),
            'icon_background' => array(
                'default' => 'fa-circle',
                'type' => 'iconpicker',
                'label' => __( 'Pick background icon', 'swifty-content-creator' ),
                'column' => 2//,
//                'width' => 100
            ),
            'show_background_icon' => array(
                'default' => '0',
                'type' => 'checkbox',
                'label' => __( 'Show background icon', 'swifty-content-creator' ),
                'text' => __( 'Show', 'swifty-content-creator' ),
                'column' => 3,
                'row' => 1//,
//                'width' => 100
            ),
            'size_background' => array(
                'default' => '100',
                'label' => __( 'Size background icon', 'swifty-content-creator' ),
                'column' => 2,
                'row' => 1,
                'width' => 100
            ),
            'color_background' => array(
                'default' => '#fff',
                'type' => 'colorpicker',
                'label' => __( 'Color background icon', 'swifty-content-creator' ),
                'column' => 3//,
//                'width' => 100
            ),
            'link' => array(
                'default' => '',
                'type' => 'link',
                'label' => __( 'Link to page or URL', 'swifty-content-creator' ),
                'column' => 4,
            ),
            'target' => array(
                'default' => '_self',
                'type' => 'radiobutton',
                'values' => '_blank^' . __( 'New window', 'swifty-content-creator' ) . '|_self^' . __( 'Same window', 'swifty-content-creator' ),
                'label' => __( 'Open URL in:', 'swifty-content-creator' ),
                'column' => 5,
                'width' => 300
            ),
            'swc_padding_top' => array(
                'default' => '20',
                'type' => 'hide'
            ),
            'swc_padding_bottom' => array(
                'default' => '20',
                'type' => 'hide'
            )
        )
    ) );
} );
