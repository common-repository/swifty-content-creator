//(function( factory ) {
//    if( typeof define === 'function' && define.amd ) {
//        // AMD. Register as an anonymous module.
//        define( [ 'jquery' ], factory );
//    } else {
//        // Browser globals
//        factory( jQuery );
//    }
//}( function( $ ) {
( function( $ ) {
    'use strict';

    var swcVideoFitVids = function() {
        if( $ && $.fn.fitVids ) {
            $( '.swc_video_wrapper' ).fitVids();

            var $div = $( '<div class="frameOverlay"/>' );
            $( '.swc_video_wrapper iframe' ).after( $div );
        } else {
            setTimeout( function() {
                swcVideoFitVids();
            }, 500 );
        }
    };

    $( window ).on( 'evt_swc_trigger_view_fn', function( /*ev, opts, dfd*/ ) {
        swcVideoFitVids();

        return false;
    } );

    swcVideoFitVids();
} )( jQuery );
//} ) );