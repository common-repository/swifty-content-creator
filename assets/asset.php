<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_Asset' ) ) {

    /**
     * Class SWC_Asset base class for assets
     */
    class SWC_Asset
    {
        protected $shortcode_name = '';
        protected $shortcode_type = 'inline';
        protected $default_atts_base = array(
            'swc_margin_top' => 5,
            'swc_margin_bottom' => 5,
            'swc_margin_left' => 10,
            'swc_margin_right' => 0,
            'swc_padding_top' => 0,
            'swc_padding_bottom' => 0,
            'swc_padding_left' => 0,
            'swc_padding_right' => 0,
            'swc_position' => 'right',
            'swc_width' => 50,
            'swc_bg_color' => '',
            'swc_border_color' => '',
            'swc_border_width' => 0,
            'swc_shadow_color' => '',
            'swc_shadow_offset_x' => 0,
            'swc_shadow_offset_y' => 0,
            'swc_shadow_blur' => 0,
            'swc_temporary' => '',
            'swc_locked' => '',
            'swc_scrolleffect' => 'none',
            'swc_se_factor' => 1,
            'swc_se_offset' => 0,
            'swc_se_move_dir' => 'up',
            'swc_se_move_mot' => 'in',
            'swc_se_move_rev' => 'normal'
        );
        protected $default_atts = null;   // Will be defined in the registration of an asset
        protected $align_array  = array( 'left', 'right', 'center' );

        /**
         * constructor, inherited classes can set default attributes
         */
        public function __construct()
        {
            // do nothing
        }

        /**
         * initialize the $shortcode_name and $default_atts members with the shortcode name
         *
         * @param $shortcode_name
         */
        public function set_shortcode_name( $shortcode_name, $shortcode_type )
        {
            $this->shortcode_name = $shortcode_name;
            $this->shortcode_type = $shortcode_type;

            // when descending class has initialized this then keep that data
            if( ! isset( $this->default_atts ) ) {
                $this->default_atts = array(
                    'swc_shortcode' => $shortcode_name
                );
            } else {
                $this->default_atts[ 'swc_shortcode' ] = $shortcode_name;
            }

            add_filter( 'swifty_asset_get_atts_' . $this->shortcode_name, array( $this, 'hook_swifty_asset_get_atts' ), 10, 2 );
        }

        /**
         * Filter to get the atts of this asset
         *
         * @param $atts_in
         * @param null $content
         * @return mixed|void
         */
        public function hook_swifty_asset_get_atts( $atts_in, $content = null ) {
            return $this->fill_atts( $this->get_default_attributes(), $atts_in, $content );
        }

        /**
         * return with combination of $atts_in atributes and default attributes
         *
         * @param $atts_in
         * @param $content
         * @return array
         */
        public function get_full_attributes( $atts_in, $content )
        {
            return $this->fill_atts( $this->get_default_attributes(), $atts_in, $content );
        }

        /**
         * combine default attributes of base class with attributes of descendant class
         *
         * @return array
         */
        public function get_default_attributes()
        {
            if( ! isset( $this->default_atts ) ) {
                $this->default_atts = array();
            }
            // we need this when only the shortcode name is set
            if( count( $this->default_atts ) < 2 ) {
                $this->default_atts = apply_filters( 'swifty_get_default_atts_of_shortcode', $this->default_atts, $this->shortcode_name );
            }
            if( $this->shortcode_type === 'inline' ) {
                return $this->default_atts;
            } else {
                return array_merge( $this->default_atts_base, $this->default_atts );
            }
        }

        /**
         * return atts_in without the atts that still have the default value
         *
         * @param $atts_in
         * @return array
         */
        public function get_non_default_attributes( $atts_in )
        {
            $atts_out = array();
            $default_atts = $this->get_default_attributes();
            $is_swifty_asset = isset( $atts_in[ 'swc_swifty_on' ] ) && (bool) $atts_in[ 'swc_swifty_on' ];

            foreach( $atts_in as $key => $value ) {
                if( ! array_key_exists( $key, $default_atts ) ||
                    $default_atts[ $key ] != $value ||
                    $key === 'swc_shortcode' ||
                    // This is needed so when moving assets with the arrows they have initial values instead of undefined.
                    // Now the left and right margins will be set correctly when moving from left to right.
                    ( $is_swifty_asset && (
                            $key === 'swc_position' || $key === 'swc_margin_left' || $key === 'swc_margin_right'
                        ) )
                ) {
                    $atts_out[ $key ] = $value;
                }
            }

            return $atts_out;
        }

        /**
         * Combine default attributes with attributes set in the shortcode
         *
         * @param $atts_default
         * @param $atts_in
         * @param $content
         */
        protected function fill_atts( $atts_default, $atts_in, $content )
        {
            $atts_out = array();

            if( $atts_in === '' ) {
                $atts_in = array();
            }
            $atts = array_merge( $atts_default, $atts_in );
            $atts = shortcode_atts( $atts, $atts_in );
            foreach( $atts as $key => $value ) {
                $atts_out[ $key ] = $value;
            }

            if( $content ) {
                $atts_out[ 'content' ] = $content;
            }

            return $this->check_atts( $atts_out );
        }

        /**
         * Is called when the attributes of a asset are changed in the edit toolbar
         * Override in derived class, or filter is implemented
         */
        protected function check_atts( $atts )
        {
            return apply_filters( 'swifty_asset_check_atts_' . $this->shortcode_name, $atts, $this->get_default_attributes() );
        }

        /**
         * make sure align contains a valid value, if not use 'left'
         */
        protected function check_align_attr( $atts )
        {
            if( array_key_exists( 'align', $atts ) &&
                ! in_array( $atts[ 'align' ], $this->align_array )
            ) {
                $atts[ 'align' ] = 'left';
            }
            return $atts;
        }

        /**
         * Change the style of the inserted html, this is also possible for
         * Override in derived classes
         *
         * @return string
         */
        protected function get_inline_styles( $atts )
        {
//            $style  = ' margin-bottom: ' . $atts[ 'swc_margin_below' ] . 'px !important;';
//            $style .= ' margin-top: 0 !important;';
//
//            return $style;
            return '';
        }
    }
}
