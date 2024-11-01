<?php
// Exit if accessed directly
defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'Swifty_Pack_Endpoints' ) ) {

    /**
     * Class Swifty_Pack_Endpoints, used to install, activate and license swifty packs from the shop.
     */
    class Swifty_Pack_Endpoints
    {
        /**
         * @var array array with known pack ids with corresponding plugin slugs
         */
        protected $swifty_packs_array = array (
            'Swifty Content Goodies Pack' => 'swifty-content-goodies-pack',
            'Swifty Content Visuals Pack' => 'swifty-content-visuals-pack'
        );

        /**
         * Swifty_Pack_Endpoints constructor.
         */
        public function __construct()
        {
            add_action( 'admin_head', array( $this, 'hook_admin_head' ) );
            add_action( 'admin_menu', array( $this, 'hook_admin_menu' ) );
        }

        /**
         * Add page to wp-admin to handle isntalltion of packs
         */
        public function hook_admin_menu()
        {
            $this->_add_page( 'scc_install_pack', array( $this, 'page_scc_install_pack' ) );
        }

        /**
         * Called via WP Action 'admin_head'
         *
         * Output header for admin page
         */
        public function hook_admin_head()
        {
            // hide wp-admin
            $currentScreen = get_current_screen();
            if ( 'dashboard_page_scc_install_pack' === $currentScreen->base ) {
                // hide update notices from this page
                remove_action( 'admin_notices', 'update_nag', 3 );
?>
<script type="text/javascript">
    jQuery( function( $ ) {
        $( '#wpadminbar' ).hide();
        $( '#adminmenuback' ).hide();
        $( '#adminmenuwrap' ).hide();
        $( '#wpcontent' ).css( 'margin-left', '0px' );
        $( '.updated' ).hide();
        $( '.error' ).hide();
    } );
</script>
<?php
            }

        }

        /**
         * Add a page to the wp-admin.
         * 
         * @param string $name
         * @param callable $callable
         * @return string - Full admin URL, for example http://domain.ext/wp-admin/?page=NAME
         */
        protected function _add_page( $name, $callable )
        {
            $hookName = get_plugin_page_hookname( $name, '' );
            add_action( $hookName, $callable );
            global $_registered_pages;
            $_registered_pages[ $hookName ] = true;
            return admin_url( '?page=' . $name );
        }

        /**
         * Install and register pack
         * IN: pack_id, email, code
         */
        public function page_scc_install_pack()
        {
            $additional_info = '';

            if( ! current_user_can( 'activate_plugins' ) ) {
                $this->send_response( __( 'You do not have sufficient permissions to access this page.', 'swifty-content-creator' ), 'error', $additional_info );
            }

            $pack_id = filter_input( INPUT_GET, 'pack_id', FILTER_SANITIZE_STRING );

            if( $pack_id ) {

                if( key_exists( $pack_id, $this->swifty_packs_array ) ) {
                    $email = filter_input( INPUT_GET, 'email', FILTER_SANITIZE_STRING );
                    $code = filter_input( INPUT_GET, 'code', FILTER_SANITIZE_STRING );
                    if( ! $email ) {
                        $this->send_response( 'Email not set.', 'error', $additional_info );
                    } else if( ! $code ) {
                        $this->send_response( 'Code not set.', 'error', $additional_info );
                    }

                    $pack_slug = $this->swifty_packs_array[ $pack_id ];

                    $plugin_active = has_filter( 'swifty_has_license_' . $pack_slug );
                    $plugin_licensed = apply_filters( 'swifty_has_license_' . $pack_slug, false );

                    if( ! $plugin_active && ! current_user_can( 'install_plugins' ) ) {
                        $this->send_response( __( 'You do not have sufficient permissions to access this page.', 'swifty-content-creator' ), 'error', $additional_info );
                    }

                    if( $plugin_licensed ) {
                        $this->send_response( __( 'Pack already installed and licensed.', 'swifty-content-creator' ), 'error', $additional_info, $plugin_active, $plugin_licensed );
                    }

                    global $swifty_lib_dir;
                    require_once $swifty_lib_dir . '/php/swifty-licenses/swifty-pack-install.php';

                    $installer =  new SwiftyPackInstall( $pack_slug, $pack_id );

                    $status = $installer->license_key_status( $email, $code );

                    if( $status ) {

                        // the license should be inactive
                        if( ( isset( $status['activated'] ) && ( $status['activated'] !== 'inactive' ) ) ) {
                            $this->send_response( $status['error'], 'error', $additional_info, $plugin_active, $plugin_licensed );
                        }

                        if( isset( $status['status_check'] ) && ( $status['status_check'] === 'active' ) ) {
                            list( $plugin_licensed, $additional_info ) = $installer->license_key_activate( $email, $code, true );
                        } else {
                            list( $plugin_licensed, $additional_info ) = $installer->license_key_activate( $email, $code, false );
                        }
                        if( $plugin_licensed ) {

                            list( $status, $url, $message ) = $installer->get_download_url( $email, $code );

                            if( $status !== 'ok' ) {
                                $this->send_response( $message, $status, $additional_info, false, $plugin_licensed );
                            }

                            $plugin = array(
                                'name'               => 'Swifty Pack', // just a dummy value
                                'slug'               => $pack_slug,
                                'source'             => $url,
                                'version'            => '0' // just a dummy value
                            );
                            Swifty_TGM_Plugin_Activation::$instance->register( $plugin );

                            if( Swifty_TGM_Plugin_Activation::$instance->install_or_activate_plugin( $pack_slug, FALSE ) ) {

                                if( Swifty_TGM_Plugin_Activation::$instance->install_or_activate_plugin( $pack_slug, TRUE ) ) {
                                    $this->send_response( __( 'Pack is installed, activated and licensed.', 'swifty-content-creator' ), 'ok', $additional_info, true, $plugin_licensed );
                                }
                                $this->send_response( __( 'Unable to activate pack.', 'swifty-content-creator' ), 'error', $additional_info, false, $plugin_licensed );
                            }
                            $this->send_response( 'Unable to install pack.', 'error', $additional_info, false, $plugin_licensed );
                        }
                        $this->send_response( __( 'Unable to activate license.', 'swifty-content-creator' ), 'error', $additional_info, $plugin_active, $plugin_licensed );
                    }
                    $this->send_response( __( 'Unable to retrieve license information.', 'swifty-content-creator' ), 'error', $additional_info );
                }
                $this->send_response( 'Unknown pack_id.', 'error', $additional_info );
            }
            $this->send_response( 'Please tell us what to do.', 'error', $additional_info );
        }

        /** 
         * Response Handler
         * This sends a JSON response to the browser
         * 
         *  status - "ok", "error"
         *  message - some feedback
         *  plugin_active - false / true
         *  plugin_licensed - 'A' / 'D'
         */
        protected function send_response( $msg, $status = 'ok', $additional_info = '', $plugin_active = '', $plugin_licensed = '' )
        {
            $close_message = __( 'Click here to close this message.', 'swifty-content-creator' );
            $code = filter_input( INPUT_GET, 'code', FILTER_SANITIZE_STRING );
            $code = $code ? $code : '';
?>
<style>
    .swifty_popup {
        position: fixed;
        top: 50%;
        left: 50%;
        text-align: center;
        cursor: default;
        -webkit-transform: translate(-50%, -50%);
        -moz-transform: translate(-50%, -50%);
        -o-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%);
    }
    .swifty_message {
        font-size: 18px;
    }
    .swifty_additional_info {
        font-size: 12px;
        margin-top: 16px;
    }
    .swifty_close {
        font-size: 12px;
        margin-top: 32px;
        cursor: pointer;
    }
    .swifty_close:hover {
        text-decoration: underline;
     }
</style>
<div class="swifty_popup">
    <div class="swifty_message">
        <?php echo $msg; ?>
    </div>
    <div class="swifty_additional_info">
        <?php echo $additional_info; ?>
    </div>
    <div id="swifty_close_button" class="swifty_close">
        <?php echo $close_message; ?>
    </div>
</div>
<script type="text/javascript">
    document.getElementById("swifty_close_button").onclick =
        function() {
            window.close();
        };

    document.addEventListener('DOMContentLoaded', function() {
        if ( window.opener != null && ! window.opener.closed ) {
            window.opener.postMessage( "<?php echo $status; ?>:<?php echo $code; ?>", "https://swifty.online/" );
        }
    }, false);

</script>

<?php

           die();
        }
    }

    new Swifty_Pack_Endpoints();
}