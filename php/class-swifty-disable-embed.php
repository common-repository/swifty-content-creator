<?php
// Exit if accessed directly
defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'Swifty_Disable_Embed' ) ) {

    /**
     * Class Swifty_Disable_Embed
     */
    class Swifty_Disable_Embed {

        public function __construct()
        {
            add_action( 'init', array( $this, 'disable_embeds_init' ), 9999 );
        }

        function disable_embeds_init() {

            if( apply_filters('get_option_scc_wp_embed', 'default' ) === 'disable' ) {
                $this->disable_embeds();
            }
        }

        /**
         * Disable embeds on init.
         * Taken from Disable embeds plugin: https://wordpress.org/plugins/disable-embeds/
         *
         * - Leaves the needed query vars for now, can disable this later.
         * - Disables oEmbed discovery.
         * - Completely removes the related JavaScript.
         */
        function disable_embeds() {
            /* @var WP $wp */
            global $wp;

            // Remove the embed query var.
//            $wp->public_query_vars = array_diff( $wp->public_query_vars, array(
//                'embed',
//            ) );

            // Remove the REST API endpoint.
//            remove_action( 'rest_api_init', 'wp_oembed_register_route' );

            // Turn off oEmbed auto discovery.
            add_filter( 'embed_oembed_discover', '__return_false' );

            // Don't filter oEmbed results.
            remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

            // Remove oEmbed discovery links.
            remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

            // Remove oEmbed-specific JavaScript from the front-end and back-end.
            remove_action( 'wp_head', 'wp_oembed_add_host_js' );
            add_filter( 'tiny_mce_plugins', array( $this, 'disable_embeds_tiny_mce_plugin') );

            // Remove all embeds rewrite rules.
//            add_filter( 'rewrite_rules_array', array( $this, 'disable_embeds_rewrites') );
        }

        /**
         * Removes the 'wpembed' TinyMCE plugin.
         *
         * @param array $plugins List of TinyMCE plugins.
         * @return array The modified list.
         */
        function disable_embeds_tiny_mce_plugin( $plugins ) {
            return array_diff( $plugins, array( 'wpembed' ) );
        }

        /**
         * Remove all rewrite rules related to embeds.
         *
         * @param array $rules WordPress rewrite rules.
         * @return array Rewrite rules without embeds rules.
         */
        function disable_embeds_rewrites( $rules ) {
            foreach ( $rules as $rule => $rewrite ) {
                if ( false !== strpos( $rewrite, 'embed=true' ) ) {
                    unset( $rules[ $rule ] );
                }
            }

            return $rules;
        }
    }

    new Swifty_Disable_Embed();
}