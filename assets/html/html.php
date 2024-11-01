<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_HTML_Asset' ) ) {

    /**
     * Class SWC_HTML_Asset
     */
    class SWC_HTML_Asset extends SWC_Shortcode
    {
        protected $shortcode_name = 'swifty_html';

        /**
         * Get html content that will be inserted for this asset shortcode
         *
         * @return string
         */
        public function get_shortcode_html( $atts )
        {
            return <<<HTML
{$atts['content']}
HTML;
        }
    }

    new SWC_HTML_Asset();
}

add_action('swifty_register_shortcodes', function() {
    /**
     * add this asset as shortcode
     */
    do_action( 'swifty_register_shortcode', array(
        'shortcode' => 'swifty_html',
        'name' => __( 'Html', 'swifty-content-creator' ),
        'type' => 'block',
        'category' => 'thirdparty',
        'icon' => '&#xe036;',
        'order' => '10',
        'width' => '100',
        'vars' => array(
            'content' => array(
                'default' => 'HTML: Lorem ipsum',
                'type' => 'textarea',
                'label' => __( 'Html code', 'swifty-content-creator' ),
                'column' => 0,
                'width' => 600
            ),
        )
    ) );
} );
