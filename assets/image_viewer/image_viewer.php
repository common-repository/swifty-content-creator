<?php

defined( 'ABSPATH' ) or exit;

if( ! class_exists( 'SWC_Image_Viewer_Asset' ) ) {

    /**
     * Class SWC_Image_Viewer_Asset base class for image assets
     */
    class SWC_Image_Viewer_Asset extends SWC_Shortcode
    {
        /**
         * load css en js files for lightbox, called from descendants
         */
        protected function load_lightbox()
        {
            global $scc_build_use;
            global $scc_version;
            $bust_add = '?swcv=scc_' . $scc_version;

            $lightcase_url = plugin_dir_url( __FILE__ ) . '../image_viewer/lib/lightcase/';
            $lightcase_url .= 'lightcase.' . ( ( $scc_build_use == 'build' ) ? 'min.' : '' ) . 'js';

            LibSwiftyPluginView::lazy_load_js( 'image_viewer_view_js', plugin_dir_url( __FILE__ ) . 'image_viewer_view.js' . $bust_add, array( 'jquery' ) );
            LibSwiftyPluginView::lazy_load_js( 'swc_lightcase_js', $lightcase_url, array( 'jquery' ) );
            LibSwiftyPluginView::lazy_load_css( 'swc_lightcase_css', plugin_dir_url( __FILE__ ) . '../image_viewer/lib/lightcase/css/lightcase.css' . $bust_add, false );
            LibSwiftyPluginView::lazy_load_css( 'swc_lightcase_max_640_css', plugin_dir_url( __FILE__ ) . '../image_viewer/lib/lightcase/css/lightcase-max-640.css' . $bust_add, false );
            LibSwiftyPluginView::lazy_load_css( 'swc_lightcase_min_641_css', plugin_dir_url( __FILE__ ) . '../image_viewer/lib/lightcase/css/lightcase-min-641.css' . $bust_add, false );
            LibSwiftyPluginView::lazy_load_css( 'swc_font_lightcase_css', plugin_dir_url( __FILE__ ) . '../image_viewer/lib/lightcase/css/font-lightcase.css' . $bust_add, false );
        }
    }

    global $swifty_path_image_viewer;
    $swifty_path_image_viewer = plugin_dir_url( __FILE__ ) . '../image_viewer';
}