define( [
    'jquery',
    'swiftylib/i18n/__',
    'js/diverse/utils',
    'swiftylib/evt'
], function(
    $, __, Utils, evt
) {
    'use strict';

    // Handles resize icon for assets and text widget

    return function( self, ckDragHandlerImage, $dragHandlerImage, $dragHandlerContainer ) {

        var wrapper = self.wrapper;
        // When the mouse enters an asset the resize icon is shown on top of the asset

        $( wrapper.$ ).on( 'mouseenter', function( el/*, ev*/ ) {
            // do not show the resize icon when the edit icon is hidden, this solves a difficulty that
            // we should not create a extra asset instance for storing the resized width while the edit
            // panel is open
            if( $dragHandlerContainer.hasClass( 'swc_hide_asset_icon' ) ) {
                return;
            }

            var $editorWrapper = $( el.currentTarget );
            // we do not want inline assets which are span
            var $assetWrapper = $editorWrapper.children( 'div.swc_asset, div.swc_text' );
            if( $assetWrapper.length !== 1) {
                return;
            }

            // Do not show the resize icon when advanced ui mode is not advanced and the asset is not inside a text asset.
            if( $assetWrapper.closest( '.swc_text' ).length <= 0 ) {
                return;
            }

            var cssFloat = $assetWrapper.css( 'float' );

            var assetType = $assetWrapper.attr( 'data-asset_type' );
            var $assetContainer = $editorWrapper.find( '.swc_asset_cntnt' );

            var $widthContainer = $assetWrapper;

            var data = $.parseJSON( Utils.atou( $assetContainer.attr( 'data-asset_data' ) ) );
            if( data && data.swc_position ) {
                if( data.swc_position === 'center' ) {
                    $widthContainer = $assetContainer;
                    cssFloat = 'none';
                } else if( ( + data.swc_width ) === 100 ) {
                    cssFloat = 'none';
                } else {
                    cssFloat = data.swc_position;
                }
            }

            var $iconWrapper;
            var $icon;
            var startPos;
            var startWidth;

            // Add the resize icon to the asset

            if( $assetWrapper.find( '.swc_asset_resizer_icon_wrapper' ).length ) {
                $assetWrapper.find( '> div > .swc_asset_resizer_icon_wrapper' ).removeClass( 'swc_will_remove' );
            } else {
                $assetWrapper.prepend(
                    '<span ' +
                    'class="swc_asset_resizer_icon_wrapper cke_reset cke_widget_drag_handler_container" ' +
                    'contenteditable="false" ' +
                    'title="' + __( 'Click and drag to resize' ) + '" ' +
                    '>' +
                    '<i class="fa fa-arrows-h" contenteditable="false"></i>' +
                    '</span>'
                );
            }

            $iconWrapper = $assetWrapper.find( '.swc_asset_resizer_icon_wrapper' );
            $icon = $iconWrapper.find( '.fa' );

            // Handle the resizing of the asset via it's resize icon

            $icon.mousecapture( {
                'down': function( ev ) {
                    startPos = ev.pageX;
                    $dragHandlerContainer.addClass( 'swc_hide_asset_icon' );
                    $('body').addClass('swc_resize');
                },
                'move': function( ev ) {
                    var diff = startPos - ev.pageX;
                    var multiFactor = 2;

                    if( cssFloat === 'right' ) {
                        multiFactor = 1;
                    } else if( cssFloat === 'left' ) {
                        multiFactor = -1;
                    }

                    var newPercent = Math.ceil( 100 * ( startWidth + diff * multiFactor ) / $widthContainer.offsetParent().width() );

                    newPercent = Math.max( newPercent, 10 );
                    newPercent = Math.min( newPercent, 100 );

                    $widthContainer.css( 'width', newPercent + '%' );

                    if ( assetType === 'swifty_slider' || assetType === 'swifty_slideshow' ) {
                        evt( 'resize_slides', {}, $assetContainer );
                    }
                },
                'up': function() {
                    $dragHandlerContainer.removeClass( 'swc_hide_asset_icon' );
                    $icon.remove();
                    $('body').removeClass('swc_resize');

                    // send to server
                    evt(
                        'swifty_editor_asset_dialog',
                        {
                            'el': $assetContainer,
                            'mod': 'icon_resized',
                            'inline': self.inline,
                            'init_css': false
                        }
                    );
                }
            } );

            var css = {};
            var doCss = true;
            var posObj = {
                my: 'top',
                at: 'bottom+4',
                of: $assetWrapper,
                collision: 'none'
            };

            if( !cssFloat || cssFloat === 'none' || cssFloat === 'right' ) {
                css.left = 0;

                // It doesn't fit
                if( $assetWrapper.outerHeight() < $iconWrapper.outerHeight() ) {
                    doCss = false;
                    $iconWrapper.position( posObj );
                } else {
                    css.bottom = 0; // '2px';
                }
            } else if( cssFloat === 'left' ) {
                css.right = 0;

                // It doesn't fit
                if( $assetWrapper.outerHeight() < $iconWrapper.outerHeight() ) {
                    doCss = false;
                    $iconWrapper.position( posObj );
                } else if( $assetWrapper.outerHeight() > $iconWrapper.outerHeight() &&
                    $assetWrapper.outerHeight() < 2 * $iconWrapper.outerHeight()
                ) {
                    // No room for 2 buttons, place at the other side
                    css.bottom = 0; // '2px';
                } else {
                    css.left = 'auto';
                    css.right = 0;
                    css.bottom = 0; // '2px';
                }
            }

            if( doCss ) {
                $iconWrapper.css( css );
            }

            // remember for drag resize
            startWidth = $widthContainer.width();

            return false;
        } );

        // Remove resize icon when leaving the asset

        $( wrapper.$ ).on( 'mouseleave', function( /* el, ev*/ ) {
            $( wrapper.$ ).find( '> div > .swc_asset_resizer_icon_wrapper' ).addClass( 'swc_will_remove' );
            setTimeout( function() {
                $( wrapper.$ ).find( '.swc_will_remove' ).remove();
            }, 1000 );
        } );
    };
} );
