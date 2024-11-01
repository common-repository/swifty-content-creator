<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_Social_Icons_Asset' ) ) {

    /**
     * Class SWC_Social_Icons_Asset Show social icons, these are rendered by WP Canvas - Shortcodes plugin
     *
     * plugin can be found at https://wordpress.org/plugins/wc-shortcodes/
     * Edit toolbar can be used to set the social media url's
     */
    class SWC_Social_Icons_Asset extends SWC_Shortcode
    {
        protected $shortcode_name = 'swifty_social_icons';

        /*
         * constructor, adds filter for external plugin
         */
        public function __construct()
        {
            parent::__construct();
            add_filter( 'wc_shortcodes_social_link', array( &$this, 'hook_wc_shortcodes_social_link' ), 10, 2 );
        }

        /**
         * Called when rendering social media icons, put our own urls in from the attributes
         *
         * @param $social_link
         * @param $key
         * @return mixed
         */
        function hook_wc_shortcodes_social_link( $social_link, $key )
        {
            $atts = &$this->atts;

            $social = array(
                'facebook'  => $atts[ 'facebook' ],
                'twitter'   => $atts[ 'twitter' ],
                'pinterest' => $atts[ 'pinterest' ],
                'google'    => $atts[ 'google' ],
                'instagram' => $atts[ 'instagram' ],
                'email'     => $atts[ 'email' ]
            );

            if( $key !== '' && array_key_exists( $key, $social ) ) {
                if( $social[ $key ] === $social_link ) {
                    return $social_link;
                } else {
                    update_option( 'wc_shortcodes_' . $key . '_link', $social[ $key ] );

                    $social_link = $social[ $key ];
                }
            }

            return $social_link;
        }

        /*
         * Get html content that will be inserted for this asset shortcode
         *
         * @return string
         */
        public function get_shortcode_html( $atts )
        {
            return <<<HTML
[wc_social_icons
    align="{$atts['align']}"
    size="{$atts['size']}"
]
HTML;
        }
    }

    new SWC_Social_Icons_Asset();
}

add_action('swifty_register_shortcodes', function() {
    /**
     * add this asset as shortcode
     */
    do_action( 'swifty_register_shortcode', array(
        'shortcode' => 'swifty_social_icons',
        'name' => 'Social Icons',
        'type' => 'block',
        'category' => 'others',
        'icon' => '???',
        'order' => '9999',
        'vars' => array(
            'size' => array(
                'default' => 'medium',
                'type' => 'radiobutton',
                'values' => 'small^Small|medium^Medium|large^Large',
                'label' => 'Icon size',
                'column' => 0,
                'row' => 0
            ),
            'align' => array(
                'default' => 'right',
                'type' => 'radiobutton',
                'values' => 'left^^fa-align-left|center^^fa-align-center|right^^fa-align-right',
                'label' => 'Align',
                'column' => 0,
                'row' => 1
            ),
            'facebook' => array(
                'default' => '',
                'label' => 'Facebook link',
                'column' => 1,
                'row' => 0
            ),
            'twitter' => array(
                'default' => '',
                'label' => 'Twitter link',
                'column' => 1,
                'row' => 1
            ),
            'pinterest' => array(
                'default' => '',
                'label' => 'Pinterest link',
                'column' => 2,
                'row' => 0
            ),
            'google' => array(
                'default' => '',
                'label' => 'Google+ link',
                'column' => 2,
                'row' => 1
            ),
            'instagram' => array(
                'default' => '',
                'label' => 'Instagram link',
                'column' => 3,
                'row' => 0
            ),
            'email' => array(
                'default' => '',
                'label' => 'Email link',
                'column' => 3,
                'row' => 1
            ),
        )
    ) );
} );
