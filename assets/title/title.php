<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_Title_Asset' ) ) {

    /**
     * Class SWC_Title_Asset Shows text in h tag style
     */
    class SWC_Title_Asset extends SWC_Shortcode
    {
        protected $shortcode_name = 'swifty_title';

        /**
         * SWC_Title_Asset constructor.
         * Add filter to remove html tags from title content.
         */
        public function __construct() {
            parent::__construct();
            add_filter( 'swifty_asset_check_atts_' . $this->shortcode_name, array( $this, 'hook_swifty_asset_check_atts' ), 10, 2 );
        }

        /**
         * Remove html tags from title content.
         *
         * @param $atts
         * @param $default_atts
         * @return mixed
         */
        public function hook_swifty_asset_check_atts( $atts, $default_atts )
        {
            $atts['content'] = strip_tags( $atts['content'] );
            return $atts;
        }

        /**
         * Get html content that will be inserted for this asset shortcode
         *
         * @return string
         */
        public function get_shortcode_html( $atts )
        {
            // Remove html tags from title when rendering.
            $content = strip_tags( $atts['content'] );

            return <<<HTML
<{$atts['type']} style="text-align:{$atts['align']};">{$content}</{$atts['type']}>
HTML;
        }
    }

    new SWC_Title_Asset();
}

add_action('swifty_register_shortcodes', function() {
    /**
     * add this asset as shortcode
     */
    do_action( 'swifty_register_shortcode', array(
        'shortcode' => 'swifty_title',
        'name' => __( 'Title', 'swifty-content-creator' ),
        'type' => 'block',
        'category' => 'textual',
        'icon' => '&#xe011;',
        'order' => '2',
        'width' => '100',
        'vars' => array(
            'content' => array(
                'default' => __( 'Headline - change this text', 'swifty-content-creator' ),
                'label' => __( 'Title', 'swifty-content-creator' ),
                'column' => 0,
                'row' => 0,
                'width' => 400
            ),
            'type' => array(
                'default' => 'h1',
                'type' => 'radiobutton',
                'values' => 'h1^h1|h2^h2|h3^h3|h4^h4',
                'label' => __( 'Style', 'swifty-content-creator' ),
                'column' => 0,
                'row' => 1
            ),
            'align' => array(
                'default' => 'left',
                'type' => 'radiobutton',
                'values' => 'left^^fa-align-left|center^^fa-align-center|right^^fa-align-right',
                'label' => 'Align',
                'column' => 1,
                'row' => 0
            ),
        )
    ) );
} );
