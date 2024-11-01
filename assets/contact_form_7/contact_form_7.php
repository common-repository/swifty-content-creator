<?php

defined( 'ABSPATH' ) or exit;

add_action('swifty_register_shortcodes', function() {

    if ( shortcode_exists( 'contact-form-7' ) ) {

        do_action( 'swifty_register_shortcode', array(
            'shortcode' => 'contact-form-7',
            'name' => __( 'Contact Form 7', 'swifty-content-creator' ),
            'type' => 'block',
            'category' => 'thirdparty',
            'icon' => '&#xe026;',
            'order' => '30',
            'width' => '50',
            'vars' => array(
                'id' => array(
                    'default' => '',
                    'label' => __( 'Id', 'swifty-content-creator' ),
                    'column' => 0,
                    'row' => 0,
                    'width' => 200
                ),
                'title' => array(
                    'default' => '',
                    'label' => __( 'Title', 'swifty-content-creator' ),
                    'column' => 0,
                    'row' => 1,
                    'width' => 300
                ),
                'html_id' => array(
                    'default' => '',
                    'label' => 'html_id',
                    'column' => 1,
                    'row' => 0,
                    'width' => 300
                ),
                'html_class' => array(
                    'default' => '',
                    'label' => 'html_class',
                    'column' => 1,
                    'row' => 1,
                    'width' => 300
                )
            )
        ) );
    }
} );