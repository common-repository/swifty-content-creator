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

    var swcThumbnailClick = function( el, viewer ) {
        var $swcAssetContent = $( el ).closest( '.swc_asset_cntnt' );
        var $imgLinks = $swcAssetContent.find( 'a.swc_image_link' );
        var url = null;

        viewer = viewer || 'lightcase';

        switch( viewer ) {
            case 'thickbox':
                if( !$imgLinks.hasClass( 'thickbox' ) ) {
                    $imgLinks.addClass( 'thickbox' );
                }

                break;
            case 'lightcase':
                if( $imgLinks.length ) {
                    $imgLinks.attr(
                        'data-rel', 'lightcase:group:' + Math.round( Date.now() + ( Math.random() * 100 ) )
                    );
                    var width = ( window.innerWidth > 0 ) ? window.innerWidth : screen.width;
                    $imgLinks.each( function( ii, imgLink ) {
                        var href = $( imgLink ).attr( 'href' );
                        if( href.indexOf( '?swifty=1' ) > 0 ) {
                            $( imgLink ).attr( 'href', href + '&ssw=' + width );
                        }
                    } );

                    $imgLinks.lightcase( {
                        'transition': 'scrollHorizontal',
                        'speedIn': 600,
                        'speedOut': 400,
                        //'maxWidth': 1024
                        'disableShrink': true,
                        'shrinkFactor': .95
                    } );
                }

                break;
            default:
                if( $( el ).is( 'a' ) ) {
                    url = $( el ).attr( 'href' );
                }

                if( $( el ).is( 'img' ) ) {
                    url = $( el ).attr( 'src' );
                }

                if( url ) {
                    window.open( url, '_blank' );
                }
        }

        return false;
    };

    if( !$.isFunction( window.swcThumbnailClick ) ) {
        window.swcThumbnailClick = swcThumbnailClick;
    }
//} ) );
} )( jQuery );