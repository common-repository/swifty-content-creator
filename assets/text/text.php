<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_TEXT_Asset' ) ) {

    /**
     * Class SWC_TEXT_Asset
     */
    class SWC_TEXT_Asset extends SWC_Shortcode
    {
        protected $shortcode_name = 'swifty_text';

        /**
         * Get html content that will be inserted for this asset shortcode
         *
         * @return string
         */
        public function get_shortcode_html( $atts )
        {
            $content = isset( $atts[ 'content' ] ) ? $atts[ 'content' ] : '';
            return <<<HTML
{$content}
HTML;
        }
    }

    new SWC_TEXT_Asset();
}

add_action('swifty_register_shortcodes', function() {
    /**
     * add this asset as shortcode
     */
    do_action( 'swifty_register_shortcode', array(
        'shortcode' => 'swifty_text',
        'name' => __( 'Text', 'swifty-content-creator' ),
        'type' => 'block',
        'category' => 'textual',
        'icon' => '&#xe00f;',
        'order' => '1',
        'width' => '100'//,
//        'vars' => array(
//            'content' => array(
//                'default' => '<p>' . __('Type your text here', 'swifty-content-creator') . '</p>',
//                'type' => 'hide'
//            ),
//        )
    ) );
} );
