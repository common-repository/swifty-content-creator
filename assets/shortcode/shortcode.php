<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_Asset_Shortcode' ) ) {

    class SWC_Asset_Shortcode extends SWC_Shortcode
    {
        protected $use_edit_placeholder = false;

        function __construct( $shortcode_name = '', $use_edit_placeholder = false ) {
            if( $shortcode_name ) {
                $this->shortcode_name = $shortcode_name;
            }
            $this->use_edit_placeholder = $use_edit_placeholder;
            parent::__construct();
        }

        protected function get_shortcode( $atts ) {
            $shortcode = $atts[ 'shortcode' ];
            $shortcode = preg_replace( '/[^0-9a-zA-Z_]/', '', $shortcode );

            return $shortcode;
        }

        protected function get_attribute( $atts, $attr_name ) {
            $attribute = isset( $atts[ $attr_name ] ) ? $atts[ $attr_name ] : false;
            if( $attribute ) {
                return " {$attr_name}=\"{$attribute}\"";
            } else {
                return '';
            }
        }

        protected function get_attributes_string( $atts ) {

            // only use key=value pairs
            $attributes = $atts[ 'attributes' ];
            $attributes_string = '';
            $attributes = preg_split ('/$\R?^/m', $attributes);
            foreach( $attributes as $attribute ) {
                $attribute = explode( '=', $attribute );
                if( count($attribute) === 2 ) {
                    $attributes_string .= " {$attribute[0]}=\"{$attribute[1]}\"";
                }
            }

            return $attributes_string;
        }

        /**
         * Get html content that will be inserted for this asset shortcode
         *
         * @return string
         */
        public function get_shortcode_html( $atts ) {
            if( $this->use_edit_placeholder ) {
                return $this->get_placeholder_html();
            } else {
                $shortcode = $this->get_shortcode( $atts );
                $attributes_string = $this->get_attributes_string( $atts );

                if( $shortcode && ( strncasecmp( $shortcode, 'swifty_', 7 ) !== 0 ) ) {
                    return do_shortcode( "[{$shortcode}{$attributes_string}]" );
                } else {
                    return '<div>&nbsp;</div>';
                }
            }
        }

        /**
         * Get placeholder html when asset is not viewable in edit mode.
         *
         * @return string
         */
        public function get_placeholder_html()
        {
            return '<div class="swc_asset_placeholder">'
            .   '<span>' . sprintf( __( 'A %s can not be shown while editing.', 'swifty-content-creator' ), $this->shortcode_name ) . '</span>'
            . '</div>'
            . "\n";
        }
    }
    //new SWC_Asset_Shortcode();
}

//add_action('swifty_register_shortcodes', function() {
//    do_action( 'swifty_register_shortcode', array(
//        'shortcode' => 'swifty_shortcode',
//        'name' => __( 'Shortcode', 'swifty-content-creator' ),
//        'type' => 'block',
//        'category' => 'thirdparty',
//        'icon' => 'fa-edit',
//        'order' => '20',
//        'width' => '50',
//        'vars' => array(
//            'shortcode' => array(
//                'default' => '',
//                'label' => __( 'Shortcode', 'swifty-content-creator' ),
//                'column' => 0,
//                'width' => 200
//            ),
//            'attributes' => array(
//                'default' => '',
//                'type' => 'textarea',
//                'label' => __( 'Attributes', 'swifty-content-creator' ),
//                'column' => 1,
//                'width' => 800
//            )
//        )
//    ) );
//} );