<?php

defined( 'ABSPATH' ) or exit;

// These texts are used multiple times and should always be the same.
$swifty_contact_form_success_msg = __( 'Your form has been successfully submitted.', 'swifty-content-creator' );
$swifty_contact_form_admin_subject = __( 'A new message has been received.', 'swifty-content-creator' );

if( ! class_exists( 'SWC_Contact_Form_Asset' ) ) {

    /**
     * Class SWC_Contact_Form_Asset
     *
     * This class adds import-export function to Fast Secure Contact Form
     * plugin to insert new contact forms
     */
    class SWC_Contact_Form_Asset extends SWC_Shortcode
    {
        protected static $instance;

        protected $shortcode_name = 'swifty_contact_form';

        /**
         * constructor: set defaults, add filters for translating contact form
         */
        public function __construct()
        {
            self::$instance = $this;

            parent::__construct();
            add_filter( 'swifty_asset_check_atts_' . $this->shortcode_name, array( $this, 'hook_swifty_asset_check_atts' ), 10, 2 );
        }

        /**
         * @return SWC_Contact_Form_Asset
         */
        public static function get_instance()
        {
            return self::$instance;
        }

        /**
         * test if a form exists, do this by reading the form information. The fields array length will be 0 if the form
         * does not exist. When the form exists then the cached information will be used when displaying the form.
         * So there is no performance impact
         *
         * @param $form_id
         * @return bool
         */
        public function form_exists( $form_id )
        {
            // FSCF_Util class is already checked
            $form_options = FSCF_Util::get_form_options( $form_id, false );
            return is_array( $form_options );
        }

        /**
         * atts changed in edit toolbar, update contact form.
         * This method is also called when viewing the page
         */
        public function hook_swifty_asset_check_atts( $atts, $default_atts ) {
            // We need this ninja_form_id redirect trick to handle existing ninja form ids. Otherwise new contact forms are
            // created until the updated form_id is saved to the content. (which is not happening on viewing the page)
            if( ( $atts[ 'form_id' ] < 0 ) && isset( $atts[ 'ninja_form_id' ] ) ) {
                $atts[ 'form_id' ] = get_option( 'swifty_ninja_redirect_' + $atts[ 'ninja_form_id' ], -1 );
            }
            $atts = $this->_set_contact_form_content( $atts );

            if( isset( $atts[ 'ninja_form_id' ] ) ) {
                update_option( 'swifty_ninja_redirect_' + $atts[ 'ninja_form_id' ], $atts[ 'form_id' ] );
                unset( $atts[ 'ninja_form_id' ] );
            }
            return $atts;
        }

        /**
         * Get html content that will be inserted for this asset shortcode, return the ninja form shortcode which will
         * be solved to the contact form by the ninja forms plugin
         *
         * @return mixed|string|void
         */
        public function get_shortcode_html( $atts )
        {
            if( LibSwiftyPluginView::is_required_plugin_active( 'si-contact-form' ) ) {
                if( $atts[ 'valid_form_id' ] ) {
                    $content = do_shortcode( '[si-contact-form form="' . $atts['form_id'] . '"]' );
                    // when editing do not create multiple codes
                    if( apply_filters( 'swifty_is_ajax_call', false ) ) {
                        $content = preg_replace('/prefix=([a-z0-9]+)/i', 'prefix=swiftycontent101', $content );
                    }
                    return $content;
                } else {
                    return <<<HTML
<div></div>
HTML;
                }
            } else {
                return apply_filters( 'swifty_plugin_not_active', 'Fast Secure Contact Form' );
            }
        }

        /**
         * this is used from the custom ss1 WordPress Importer to insert a form and return the new id
         *
         * @param $admin_mailto
         * @param $success_msg
         * @param $admin_subject
         * @return int
         */
        public function Import_ContactForm( $admin_mailto, $success_msg, $admin_subject )
        {
            $atts = array();
            $atts[ 'form_id' ] = -1;
            $atts[ 'admin_mailto' ] = $admin_mailto;
            $atts[ 'success_msg' ] = $success_msg;
            $atts[ 'admin_subject' ] = $admin_subject;
            $atts = apply_filters( 'swifty_get_default_atts_of_shortcode', $atts, $this->shortcode_name );
            $atts = $this->_set_contact_form_content( $atts );

            return $atts[ 'form_id' ];
        }

        /**
         * create new ninja form if given id does not exist or is equal to -1, update the form (existing or new) with
         * the current asset attributes
         */
        private function _set_contact_form_content( $atts )
        {
            if( class_exists( 'FSCF_Util' ) ) {

                if( isset( $atts[ 'form_id' ] ) ) {
                    $form_id = $atts[ 'form_id' ];

                    if( ( $form_id == -1 ) || ! $this->form_exists( $form_id ) ) {
                        $form_id = $this->_import_contact_form();
                        $atts[ 'form_id' ] = $form_id;
                    }
                    $atts[ 'valid_form_id' ] = ( $form_id > 0 );

                    // tell js what the new form id is, that will ensure that the data is written to the asset
                    global $scc_asset_return_info;
                    $scc_asset_return_info = array(
                        'form_id' => $form_id
                    );

                    // check if all settings are set to their proper values
                    if( $form_id > 0 ) {
                        $this->_update_form_settings( $form_id, $atts );
                    }
                }
            } else {
                error_log( 'Fast Secure Contact Form plugin not loaded!' );
            }
            return $atts;
        }

        /**
         * Check if field contains this value, return true for $updated when different
         *
         * @param $form_options
         * @param $updated
         * @param $field_name
         * @param $field_value
         */
        private function _update_field( &$form_options, &$updated, $field_name, $field_value ) {
            if( $form_options[ $field_name ] !== $field_value ) {
                $form_options[ $field_name ] = $field_value;
                $updated = true;
            }
        }

        /**
         * update the form (existing or new) with the current asset attributes
         *
         * @param $form_id
         */
        private function _update_form_settings( $form_id, $atts )
        {
            $form_options = FSCF_Util::get_form_options( $form_id, false );
            if( is_array( $form_options ) ) {
                $updated = false;

                global $swifty_contact_form_success_msg;
                global $swifty_contact_form_admin_subject;

                $success_msg = $atts[ 'success_msg' ];
                if( empty( $success_msg ) ) {
                    $success_msg = $swifty_contact_form_success_msg;
                }
                $this->_update_field( $form_options, $updated, 'text_message_sent', $success_msg );

                $admin_mailto = $atts[ 'admin_mailto' ];
                if( ! filter_var( $admin_mailto, FILTER_VALIDATE_EMAIL ) ) {
                    $admin_mailto = get_option( 'admin_email' );
                }
                $this->_update_field( $form_options, $updated, 'email_to', $admin_mailto );
                $this->_update_field( $form_options, $updated, 'email_from', $admin_mailto );

                $admin_subject = $atts[ 'admin_subject' ];
                if( empty( $admin_subject ) ) {
                    $admin_subject = $swifty_contact_form_admin_subject;
                }
                $this->_update_field( $form_options, $updated, 'email_subject', $admin_subject );

                // update translations
                $this->_update_field( $form_options, $updated, 'title_name', __( 'Name:', 'swifty-content-creator' ) );
                $this->_update_field( $form_options, $updated, 'title_email', __( 'Email:', 'swifty-content-creator' ) );
                $this->_update_field( $form_options, $updated, 'title_subj', __( 'Subject:', 'swifty-content-creator' ) );
                $this->_update_field( $form_options, $updated, 'title_mess', __( 'Message:', 'swifty-content-creator' ) );
                $this->_update_field( $form_options, $updated, 'title_capt', __( 'Enter the text:', 'swifty-content-creator' ) );
                $this->_update_field( $form_options, $updated, 'title_submit', __( 'Submit', 'swifty-content-creator' ) );
                $this->_update_field( $form_options, $updated, 'title_submitting', __( 'Submitting...', 'swifty-content-creator' ) );

                if( $updated ) {
                    update_option( "fs_contact_form$form_id", $form_options );
                }
            }
        }

        /**
         * import a contact form from template and return id
         *
         * @return mixed
         */
        private function _import_contact_form() {

            // a test on FSCF_Util is already done, this include should work
            require_once FSCF_PATH . 'includes/class-fscf-options.php';

            FSCF_Options::$global_options = FSCF_Util::get_global_options();

            // get highest ID
            ksort( FSCF_Options::$global_options[ 'form_list' ] );
            $max_form_num = max( array_keys( FSCF_Options::$global_options[ 'form_list' ] ) );

            $new_id = $max_form_num + 1;

            // Find the next form number
            // When forms are deleted, their form number is NOT reused
            FSCF_Options::$global_options[ 'form_list' ][ $new_id ] = __( 'New Form', 'si-contact-form' );
            FSCF_Options::$global_options[ 'max_form_num' ] = $new_id;
            update_option( 'fs_contact_global', FSCF_Options::$global_options );

            $form_options = FSCF_Util::get_form_options( $new_id, true );
            $updated = false;

            $this->_update_field( $form_options, $updated, 'welcome', '' );
            $this->_update_field( $form_options, $updated, 'redirect_enable', false );
            $this->_update_field( $form_options, $updated, 'req_field_label_enable', false );
            $this->_update_field( $form_options, $updated, 'email_html', true );

            $this->_update_field( $form_options, $updated, 'field_prefollow_style', 'clear:left; float:left; width:100%; margin-right:10px;' );
            $this->_update_field( $form_options, $updated, 'field_follow_style', 'float:left; padding-left:10px; width:100%;' );
            $this->_update_field( $form_options, $updated, 'field_style', 'text-align:left; margin:0; width:100%;' );
            $this->_update_field( $form_options, $updated, 'required_style', 'text-align:left; color: red;' );
            $this->_update_field( $form_options, $updated, 'required_text_style', 'text-align:left; color: red;' );
            $this->_update_field( $form_options, $updated, 'captcha_input_style', 'text-align:left; margin:0; width:50px;' );
            $this->_update_field( $form_options, $updated, 'captcha_div_style_m', 'max-width:250px; width:100%; height:65px; padding-top:2px;' );
            $this->_update_field( $form_options, $updated, 'textarea_style', 'text-align:left; margin:0; width:100%; height:120px;' );

            // Add the standard fields (Name, Email, Subject, Message)
            // The main plugin file defines constants to refer to the standard field codes
            $name = array(
                'standard' => '1',        // standard field number, otherwise '0' (internal) NEW
                'req' => 'true',
                'label' => __( 'Name:', 'swifty-content-creator' ),
                'slug' => 'full_name',
                'type' => 'text'
            );
            $email = array(
                'standard' => '2',        // standard field number, otherwise '0' (internal) NEW
                'req' => 'true',
                'label' => __( 'Email:', 'swifty-content-creator' ),
                'slug' => 'email',
                'type' => 'text'
            );

            $phone = array(
                'standard' => '0',        // standard field number, otherwise '0' (internal) NEW
                'req' => 'false',
                'label' => __( 'Phone:', 'swifty-content-creator' ),
                'slug' => 'phone',
                'type' => 'text'
            );

//            $subject = array(
//                'standard' => '3',        // standard field number, otherwise '0' (internal) NEW
//                'req' => 'true',
//                'label' => __( 'Subject:', 'swifty-content-creator' ),
//                'slug' => 'subject',
//                'type' => 'text'
//            );
            $message = array(
                'standard' => '4',        // standard field number, otherwise '0' (internal) NEW
                'req' => 'true',
                'label' => __( 'Message:', 'swifty-content-creator' ),
                'slug' => 'message',
                'type' => 'textarea'
            );

            // Add the standard fields to the form fields array
            $form_options[ 'fields' ] = array();
            $form_options[ 'fields' ][] = array_merge( FSCF_Util::$field_defaults, $name );
            $form_options[ 'fields' ][] = array_merge( FSCF_Util::$field_defaults, $email );
            $form_options[ 'fields' ][] = array_merge( FSCF_Util::$field_defaults, $phone );
//            $form_options[ 'fields' ][] = array_merge( FSCF_Util::$field_defaults, $subject );
            $form_options[ 'fields' ][] = array_merge( FSCF_Util::$field_defaults, $message );


            update_option( "fs_contact_form$new_id", $form_options );

            return $new_id;
        }
    }

    new SWC_Contact_Form_Asset();
}

add_action('swifty_register_shortcodes', function() {

    global $swifty_contact_form_success_msg;
    global $swifty_contact_form_admin_subject;

    /**
     * add this asset as shortcode
     */
    do_action( 'swifty_register_shortcode', array(
        'shortcode' => 'swifty_contact_form',
        'name' => __( 'Contact form', 'swifty-content-creator' ),
        'type' => 'block',
        'category' => 'interactive',
        'icon' => '&#xe026;',
        'order' => '10',
        'width' => '50',
        'vars' => array(
            'form_id' => array(
                'default' => -2,
                'type' => 'hide',
            ),
            'admin_mailto' => array(
                'default' => get_option( 'admin_email' ),
                'label' => __( 'Send all messages to this email', 'swifty-content-creator' ),
                'column' => 0,
                'row' => 0,
                'width' => 400
            ),
            'admin_subject' => array(
                'default' => $swifty_contact_form_admin_subject,
                'label' => __( 'Subject', 'swifty-content-creator' ),
                'column' => 0,
                'row' => 1,
                'width' => 400
            ),
            'success_msg' => array(
                'default' => $swifty_contact_form_success_msg,
                'type' => 'textarea',
                'label' => __( 'Thank you text', 'swifty-content-creator' ),
                'column' => 1,
                'row' => 0,
                'width' => 400
            ),
        )
    ) );
} );
