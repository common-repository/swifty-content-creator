<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_Shortcode' ) ) {

    /**
     * Class SWC_Shortcode base class for shortcode, only implementation of hook_swifty_asset_get_content_html is needed.
     * This class will wrap needed needed code around it
     */
    class SWC_Shortcode
    {
        protected $shortcode_name = '';

        /**
         * constructor adds the shortcode to WP. This class is not needed, but just a easy way to handle the
         * attributes in a consequent way
         */
        public function __construct()
        {
            add_shortcode( $this->shortcode_name, array( $this, 'hook_shortcode' ) );
        }

        /**
         * handle this shortcode, first add extra attributes
         *
         * @param $atts
         * @param $content
         * @return mixed|void
         */
        public function hook_shortcode( $atts, $content )
        {
            LibSwiftyPluginView::lazy_load_css( 'swcreator_swcreator_css' );

            $atts = apply_filters( 'swifty_asset_get_atts_' . $this->shortcode_name, $atts, $content );

            $html = $this->get_shortcode_html( $atts );

            return $html;
        }

        /**
         * Get html content that will be inserted for this shortcode
         * Override to provide content
         *
         * @param $html
         * @param $atts
         * @return string
         */
        public function get_shortcode_html( $atts )
        {
            return '';
        }
    }
}