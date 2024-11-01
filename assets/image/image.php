<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_Image_Asset' ) ) {

    require_once( dirname( __FILE__ ) . '/../image_viewer/image_viewer.php' );

    /**
     * Class SWC_Image_Asset enables image viewer as asset
     */
    class SWC_Image_Asset extends SWC_Image_Viewer_Asset
    {
        protected $shortcode_name = 'swifty_image';

        /**
         * Get html content that will be inserted for this asset shortcode
         *
         * @return string
         */
        public function get_shortcode_html( $atts )
        {
            if( ! $atts[ 'url' ] ) {
                return "<div>" . __( 'No image selected yet.', 'swifty-content-creator' ) . "</div>";
            } else {
                global $swifty_lib_dir;
                if( isset( $swifty_lib_dir ) ) {
                    require_once $swifty_lib_dir . '/php/lib/swifty-image-functions.php';
                }

                if( $atts[ 'viewer' ] === 'lightcase' ) {
                    $this->load_lightbox();
                }

                // when no alt is set use the caption as alt
                if( isset( $atts[ 'alt' ] ) && ! empty( $atts[ 'alt' ] ) ) {
                    $alt_text = $atts[ 'alt' ];
                } else if( isset( $atts[ 'caption' ] ) && ! empty( $atts[ 'caption' ] ) ) {
                    $alt_text = $atts[ 'caption' ];
                } else {
                    $alt_text = '';
                }
                
                $class = '';
                if( isset( $atts[ 'style_type' ] ) && $atts[ 'style_type' ] !== 'default' ) {
                    $class .= ' ' . $atts[ 'style_type' ] . '';
                }
                if( isset( $atts[ 'style_type_caption' ] ) && $atts[ 'style_type_caption' ] !== 'default' ) {
                    $class .= ' ' . $atts[ 'style_type_caption' ] . '';
                }
                
                $html = SwiftyImageFunctions::get_img_tag( $atts[ 'url' ], $alt_text, $atts[ 'viewer' ] === 'go_to_url', $atts[ 'href' ], $atts[ 'target' ], $atts[ 'viewer' ], $atts, $class );

//                if( isset( $atts[ 'caption' ] ) && ! empty( $atts[ 'caption' ] ) ) {
//                    $html = '<figure class="wp-caption">' . $html . '<figcaption class="wp-caption-text">' . $atts[ 'caption' ] . '</figcaption></figure>';
//                }

                return $html;
            }
        }
    }

    new SWC_Image_Asset();
}

add_action('swifty_register_shortcodes', function() {
    /**
     * add this asset as shortcode
     */
    do_action( 'swifty_register_shortcode', array(
        'shortcode' => 'swifty_image',
        'name' => __( 'Image', 'swifty-content-creator' ),
        'type' => 'block',
        'category' => 'visuals',
        'icon' => '&#xe022;',
        'order' => '10',
        'width' => '50',

        'vars' => array(
            'selected_ids' => array(
                'default' => '',
                'type' => 'icon_button',
                'button_size' => 'small',
                'action' => 'select_image',
                'text' => __( 'Choose image', 'swifty-content-creator' ),
                'column' => 0,
            ),
            'viewer' => array(
                'default' => 'nothing',
                'type' => 'radiobutton',
                'values' => 'lightcase^' . __( 'Shows in lightbox', 'swifty-content-creator' ) . '|nothing^' . __( 'Does nothing', 'swifty-content-creator' ) . '|go_to_url^' . __( 'Goes to a URL', 'swifty-content-creator' ),
                'label' => __( 'Click on image:', 'swifty-content-creator' ),
                'column' => 1,
                'direction' => 'vertical'
            ),
            'href' => array(
                'default' => '',
                'label' => __( 'Specify URL', 'swifty-content-creator' ),
                'column' => 2,
                'row' => 0,
                'width' => 400
            ),
            'target' => array(
                'default' => '_blank',
                'type' => 'radiobutton',
                'values' => '_blank^' . __( 'New window', 'swifty-content-creator' ) . '|_self^' . __( 'Same window', 'swifty-content-creator' ),
                'label' => __( 'Open URL in:', 'swifty-content-creator' ),
                'column' => 2,
                'row' => 1,
                'width' => 270
            ),
            'caption' => array(
                'default' => '',
                'label' => __( 'Image caption', 'swifty-content-creator' ),
                'column' => 3,
                'row' => 0,
                'width' => 400
            ),
            'alt' => array(
                'default' => '',
                'label' => __( 'Image description (alt text)', 'swifty-content-creator' ),
                'column' => 3,
                'row' => 1,
                'width' => 400
            ),
            'url' => array(
                'default' => '',
                'type' => 'hide',

            ),
            'style_type' => array(
                'default' => 'default',
                'type' => 'select',
                'values' =>
                    'default^' . __( 'Default', 'swifty-content-creator' ) .
                    '|swc_style_img_1^' . __( 'Basic', 'swifty-content-creator' ) .
                    '|swc_style_img_2^' . __( 'Rounded corners 1', 'swifty-content-creator' ) .
                    '|swc_style_img_3^' . __( 'Rounded corners 2', 'swifty-content-creator' ) .
                    '|swc_style_img_4^' . __( 'Oval', 'swifty-content-creator' ) .
                    '|swc_style_img_5^' . __( 'Border 1', 'swifty-content-creator' ) .
                    '|swc_style_img_6^' . __( 'Border 2', 'swifty-content-creator' ) .
                    '|swc_style_img_7^' . __( 'Floating', 'swifty-content-creator' ),
                'label' => __( 'Style type', 'swifty-content-creator' ),
                'column' => 4,
            ),
            'style_type_caption' => array(
                'default' => 'default',
                'type' => 'select',
                'values' =>
                    'default^' . __( 'Default', 'swifty-content-creator' ) .
                    '|swc_style_cap_1^' . __( 'More padding', 'swifty-content-creator' ) .
                    '|swc_style_cap_2^' . __( 'Bold', 'swifty-content-creator' ) .
                    '|swc_style_cap_3^' . __( 'Overlay light', 'swifty-content-creator' ) .
                    '|swc_style_cap_4^' . __( 'Overlay dark', 'swifty-content-creator' ) .
                    '|swc_style_cap_5^' . __( 'Hover bottom', 'swifty-content-creator' ) .
                    '|swc_style_cap_6^' . __( 'Hover full', 'swifty-content-creator' ),
                'label' => __( 'Caption style type', 'swifty-content-creator' ),
                'column' => 4,
                'row' => 1,
            ),
        ),

        'edit__' => array(
            'template' => <<<HTML
<!--<div class="swc_form_column" style="width: 500px;">-->
    <!--<div class="swifty_form_input_wrapper" style="white-space: nowrap;">-->
        <!--<div class="swifty_form_label">-->
            <!--{{__ 'Image info'}}:-->
        <!--</div>-->
        <!--<table>-->
            <!--{{#if asset_data.file_name}}-->
                <!--<tr>-->
                    <!--<td>{{__ 'File name'}}:&nbsp;</td>-->
                    <!--<td>{{asset_data.file_name}}</td>-->
                <!--</tr>-->
            <!--{{/if}}-->
            <!--{{#if asset_data.file_size}}-->
                <!--<tr>-->
                    <!--<td>{{__ 'File size'}}:&nbsp;</td>-->
                    <!--<td>{{asset_data.file_size}}</td>-->
                <!--</tr>-->
            <!--{{/if}}-->
            <!--{{#if asset_data.image_size}}-->
                <!--<tr>-->
                    <!--<td>{{__ 'Image size'}}:&nbsp;</td>-->
                    <!--<td>{{asset_data.image_size}}</td>-->
                <!--</tr>-->
            <!--{{/if}}-->
            <!--{{#if asset_data.date}}-->
                <!--<tr>-->
                    <!--<td>{{__ 'Uploaded on'}}:&nbsp;</td>-->
                    <!--<td>{{asset_data.date}}</td>-->
                <!--</tr>-->
            <!--{{/if}}-->
        <!--</table>-->
    <!--</div>-->
<!--</div>-->
HTML
        )
    ) );
} );

