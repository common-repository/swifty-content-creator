<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_Divider_Asset' ) ) {

    /**
     * Class SWC_Divider_Asset asset for divider that opens a link or page on click
     */
    class SWC_Divider_Asset extends SWC_Shortcode
    {
        protected $shortcode_name = 'swifty_divider';

        /**
         * Get html content that will be inserted for this asset shortcode
         *
         * @param $html
         * @param $atts
         * @return string
         */
        public function get_shortcode_html( $atts )
        {
            $height = floatval( $atts[ 'divider_height' ] );
            if( $height < 1 ) {
                $height = 1;
            }
            return <<<HTML
<div style="height:{$height}px; background-color: {$atts[ 'divider_bg_color' ]}">&nbsp;</div>
HTML;
        }
    }

    new SWC_Divider_Asset();
}

add_action('swifty_register_shortcodes', function() {
    /**
     * add this asset as shortcode
     */
    do_action( 'swifty_register_shortcode', array(
        'shortcode' => 'swifty_divider',
        'name' => __( 'Divider', 'swifty-content-creator' ),
        'type' => 'block',
        'category' => 'layout',
        'icon' => 'fa-minus',
        'order' => '10',
        'width' => '100',
        'vars' => array(
            'divider_bg_color' => array(
                'default' => '#404040',
                'type' => 'colorpicker',
                'label' => __( 'Background color', 'swifty-content-creator' ),
                'column' => 0,
                'row' => 0//,
//                'width' => 100
            ),
            'divider_height' => array(
                'default' => '1',
                'label' => __( 'Height', 'swifty-content-creator' ),
                'column' => 0,
                'row' => 1,
                'width' => 100
            ),
        )
    ) );
} );
