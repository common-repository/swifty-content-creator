<?php

defined( 'ABSPATH' ) or exit;

add_action( 'swifty_register_shortcodes', function() {
    if( shortcode_exists( 'rev_slider' ) ) {

        do_action( 'swifty_register_shortcode', array(
            'shortcode' => 'rev_slider',
            'name' => __( 'Slider Revolution', 'swifty-content-creator' ),
            'type' => 'block',
            'category' => 'thirdparty',
            'icon' => '&#xe012;',
            'order' => '40',
            'width' => '50',
            'use_edit_placeholder' => '1',
            'vars' => array(
                'alias' => array(
                    'default' => '',
                    'label' => __( 'Alias', 'swifty-content-creator' ),
                    'column' => 0,
                    'row' => 0,
                    'width' => 200
                )
            )
        ) );
    }
} );