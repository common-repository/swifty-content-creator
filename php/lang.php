<?php
// Exit if accessed directly
defined( 'ABSPATH' ) or exit;

global $scc_build_use;

global $scc_locale;
$scc_locale = array();

//$testPreLocale = '';
//if( $scc_build_use != 'build' ) {
//    $testPreLocale = ':';
//}

// Have WP load the applicable language files
if( $scc_build_use != 'build' ) {
    // split 'swifty-content-' . 'creator' to prevent being found when looking for translations
    load_plugin_textdomain( 'swifty-content-creator', false, 'swifty-content-' . 'creator/languages' );
    load_plugin_textdomain( 'swifty-content-creator', false, 'swifty-content-' . 'creator/lib/swifty_plugin/languages' );
} else {
    load_plugin_textdomain( 'swifty-content-creator', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages' );
}

// Read and process lang_js.pot so we get a list of all strins used in javascipt and mustache

// outcommented because we only want js extracted translations and the syntax for long msgid's is different
// for swifty-nl_NL.po a better regex is needed for reading multi-line msgid's:
// msgid ""
// "Start typing here or add content<br> by using the + button at the bottom."
// is using 2 lines

//if( $scc_build_use != 'build' ) {
    $poSrc = file_get_contents( plugin_dir_path( __FILE__ ) . '../languages/lang_js.pot' );
//} else {
//    $poSrc = file_get_contents( plugin_dir_path( __FILE__ ) . '../languages/swifty-nl_NL.po' );
//}
preg_match_all( '/msgid\s+\"([^\"]*)\"/', $poSrc, $matches );
foreach( $matches[ 1 ] as $msgid ) {
    $scc_locale[ $msgid ] = __( $msgid, 'swifty-content-creator' );
}

//// for internal use we want to be sure everything is translated, for this we prefix ':'
//if ( $testPreLocale != '' ) {
//    foreach( $scc_locale as $key ) {
//        $scc_locale[ $key ] = $testPreLocale . $key;
//    }
//}
