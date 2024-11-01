<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_Quote_Asset' ) ) {

    class SWC_Quote_Asset extends SWC_Shortcode
    {
        protected $shortcode_name = 'swifty_quote';

        /**
         * Get html content that will be inserted for this asset shortcode
         *
         * @return string
         */
        public function get_shortcode_html( $atts )
        {
            $cite = ( $atts[ 'cite' ] ) ? '<span class="swc_quote_cite">' . $atts[ 'cite' ] . '</span>' : '';
            $cite_class = ( $atts[ 'cite' ] ) ? 'swc_quote_has_cite' : '';

            $cite = do_shortcode( str_replace( array( '{', '}' ), array( '[', ']' ), $cite ) );

            $content = $atts['content'];

            if( ! has_filter( 'the_content', 'wpautop' ) || apply_filters( 'swifty_is_ajax_call', false ) ) {
                $content = wpautop( $content );
            }

            return <<<HTML
<style>
.swc_asset_swifty_quote .swc_asset_cntnt { overflow: hidden; }
.swc_asset_swifty_quote .swc_asset_cntnt .swc_quote_wrapper { position: relative; margin-bottom: 1.5em; padding: 0.5em 3em; font-style: italic; }
.swc_asset_swifty_quote .swc_asset_cntnt .swc_quote_wrapper.swc_quote_has_cite { margin-bottom: 3em; }
.swc_asset_swifty_quote .swc_asset_cntnt .swc_quote_wrapper .swc_quote_cite { position: absolute; right: 2em; bottom: -2.5em; font-style: normal; }
.swc_asset_swifty_quote .swc_asset_cntnt .swc_quote_wrapper .swc_quote_cite:before { content: "\\2014\\0000a0"; }
.swc_asset_swifty_quote .swc_asset_cntnt .swc_quote_wrapper .swc_quote_cite a { text-decoration: underline; }
.swc_asset_swifty_quote .swc_asset_cntnt .swc_quote_wrapper:before,
.swc_asset_swifty_quote .swc_asset_cntnt .swc_quote_wrapper:after { position: absolute; display: block; font-style: normal; font-size: 50px !important; }
.swc_asset_swifty_quote .swc_asset_cntnt .swc_quote_wrapper:before { left: 0; bottom: -42px; content: "\\201C"; }
.swc_asset_swifty_quote .swc_asset_cntnt .swc_quote_wrapper:after { right: 0; top: -15px; content: "\\201D"; }
</style>

<div class="swc_quote_wrapper {$cite_class}">
    {$content}{$cite}
</div>
HTML;
        }
    }
    new SWC_Quote_Asset();
}

add_action('swifty_register_shortcodes', function() {
    do_action( 'swifty_register_shortcode', array(
        'shortcode' => 'swifty_quote',
        'name' => __( 'Quote', 'swifty-content-creator' ),
        'type' => 'block',
        'category' => 'layout',
        'icon' => '&#xe044;',
        'order' => '30',
        'width' => '50',
        'content_removep' => '1',
        'vars' => array(
            'content' => array(
                'default' => '',
                'type' => 'textarea',
                'label' => __( 'Quote', 'swifty-content-creator' ),
                'column' => 0,
                'width' => 600
            ),
            'cite' => array(
                'default' => '',
                'label' => __( 'Author', 'swifty-content-creator' ),
                'column' => 1,
                'width' => 400
            )
        )
    ) );
} );