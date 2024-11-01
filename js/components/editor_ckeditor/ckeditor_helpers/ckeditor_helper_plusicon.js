define( [
    'jquery',
    'swiftylib/i18n/__',
    'swiftylib/evt'
], function(
    $, __, evt
) {
    'use strict';

    // Handles plus icon for assets and text widget

    var pTagEventsAdded = 0;

    return function( self, $dragHandlerContainer ) {

        if( pTagEventsAdded === 0 ) {
            var pSelector = '.swc_text > .swc_asset_cntnt > p, .swc_text > .swc_asset_cntnt > * > p';

            // Add + icons to all p tags of text assets on hover.
            // Live binded to future p elements.
            $( '.swc_page_cntnt.cke_editable' ).on( 'mouseenter', pSelector, function( ev ) {
                if( $( 'body' ).hasClass( 'swc_no_editor_hovers' ) ) {
                    return;
                }

                // Do not show the plus icon when the edit icon is hidden.
                if( $dragHandlerContainer.hasClass( 'swc_hide_asset_icon' ) ) {
                    return;
                }

                var $editorWrapper = $( ev.currentTarget );
                var $textWrapper = $editorWrapper.closest( 'div.swc_text' );
                var $assetWrapper = $editorWrapper.closest( 'div.swc_grid_column' );
                var $rowWrapper = $assetWrapper.closest( '.swc_grid_row' );
                $assetWrapper = $rowWrapper.parent();

                // Only show paragraph + icons when:
                // 1. text asset is 100%
                // 2. only 1 column in the row
                // 3. there are at least 2 paragraphs in the text asset.
                if( $textWrapper[ 0 ].style.width !== '100%' ) {
                    return;
                }
                if( $rowWrapper.find( '.swc_grid_column' ).length > 1 ) {
                    return;
                }
                if( $textWrapper.find( '> .swc_asset_cntnt > p, > .swc_asset_cntnt > * > p' ).length <= 1 ) {
                    return;
                }

                var locked = $textWrapper.hasClass( 'swc_locked' );
                if( ! locked || scc_data.swifty_edit_locked ) {

                    if( $editorWrapper.find( '> .swc_asset_p_plus_icon_wrapper' ).length ) {
                        $editorWrapper.find( '> .swc_asset_p_plus_icon_wrapper' ).removeClass( 'swc_will_remove' );
                    } else {
                        $editorWrapper.append( getPlusIconHtml( 'swc_asset_p_plus_icon_wrapper' ) );

                        var $iconWrapper = $editorWrapper.find( '.swc_asset_p_plus_icon_wrapper' );
                        var $icon = $iconWrapper.find( '.fa' );

                        $icon.on( 'click', function( /*ev*/ ) {
                            evt( 'set_paragraph_range_for_insert', {
                                '$p': $editorWrapper
                            } ).done( function() {
                                evt( 'add_content_panel', { 'allowance': 'insize_text_asset' } );
                            } );
                        } );

                        var left = 0;
                        var top = 0;
                        var leftMoved = 0;
                        var topMoved = 1;
                        var leftChanged = 1;
                        var $elsCheck = $editorWrapper.closest( '.swc_text' ).find( '.swc_asset' ).add( '.swc_asset_any_icon_wrapper' ).not( $iconWrapper ).not( '.swc_nopluscollision' ).not( '.swc_asset_p_plus_icon_wrapper' );

                        var posTextAsset = getElPos( $editorWrapper.closest( '.swc_text' ) );

                        // Position the + icon. Make sure it does not overlap.

                        while( leftChanged > 0 || topMoved > 0 ) {
                            leftChanged = 0;

                            $iconWrapper.position( {
                                my: 'right center',
                                at: 'right-' + left + ' center' + ( top >= 0 ? '+' + top : top ),
                                of: $editorWrapper,
                                collision: 'none'
                            } );

                            var posHandler = getElPos( $iconWrapper );

                            // Try to fit the + icon inside the text asset (not overlapping it's top or bottom border).
                            if( topMoved === 1 ) {
                                topMoved = 0;
                                if( posHandler.y < posTextAsset.y ) {
                                    top = posTextAsset.y - posHandler.y;
                                    topMoved = 2;
                                } else if( posHandler.y + posHandler.h > posTextAsset.y + posTextAsset.h ) {
                                    top = ( posTextAsset.y + posTextAsset.h ) - ( posHandler.y + posHandler.h );
                                    topMoved = 2;
                                }
                            } else {
                                topMoved = 0;
                            }

                            // Make sure + icons does not overlap with other icons and assets.
                            $elsCheck.each( function( ii, el ) {
                                var $el = $( el );
                                var pos = getElPos( $el );

                                if( posHandler.x < pos.x + pos.w && posHandler.x + posHandler.w > pos.x ) {
                                    if( posHandler.y < pos.y + pos.h && posHandler.y + posHandler.h > pos.y ) {
                                        if( posHandler.x + posHandler.w - pos.x - left > 0 ) {
                                            left += posHandler.x + posHandler.w - pos.x - left;
                                        }
                                        left ++;
                                        leftMoved = 1;
                                        if( left < $editorWrapper.width() - 32 ) {
                                            leftChanged = 1;
                                        }
                                    }
                                }
                            } );

                            if( leftChanged == 0 && leftMoved > 0 ) {
                                left += 2;
                                leftMoved = 0;
                                leftChanged = 1;
                            }
                        }
                    }
                }
            } );
            $( '.swc_page_cntnt.cke_editable' ).on( 'mouseleave', pSelector, function( ev ) {
                $( ev.currentTarget ).find( '.swc_asset_p_plus_icon_wrapper' ).addClass( 'swc_will_remove' );
                setTimeout( function() {
                    $( ev.currentTarget ).find( '.swc_will_remove' ).remove();
                }, 200 );
            } );

            pTagEventsAdded = 1;
        }

        var $wrapper = $( self.wrapper.$ );

        // When the mouse enters an column the plus icon is shown on top of the column

        $wrapper.on( 'mouseenter', function( el/*, ev*/ ) {
            //// do not show the plus icon when the edit icon is hidden, this solves a difficulty that
            //// we should not create a extra asset instance for storing the resized width while the edit
            //// panel is open
            if( $dragHandlerContainer.hasClass( 'swc_hide_asset_icon' ) ) {
                return;
            }

            var $editorWrapper = $( el.currentTarget );
            var $assetWrapper = $editorWrapper.find( 'div.swc_grid_column' );
            if( $assetWrapper.length <= 0 ) {
                // Find the closest parent column, if exists.
                $assetWrapper = $editorWrapper.closest( '.swc_grid_column' );
            }
            var $assetWrapperColumn = $assetWrapper;
            $assetWrapper = $assetWrapper.closest( '.swc_grid_row' ).parent();
            if( $assetWrapper.length !== 1 ) {
                return;
            }

            // Add the plus icon to the column.
            var appended = 0;
            if( ! $assetWrapper.find( '.swc_asset_plus_icon_wrapper' ).length ) {
                $assetWrapper.append( getPlusIconHtml( 'swc_asset_plus_icon_wrapper' ) );
                appended = 1;
            }

            var $iconWrapper = $assetWrapper.find( '.swc_asset_plus_icon_wrapper' );
            var $icon = $iconWrapper.find( '.fa' );

            var $row = $assetWrapperColumn.closest( '.swc_grid_row' );
            var bottom = 0;
            if( parseInt( $row.css( 'padding-bottom' ), 10 ) > 0 ) {
                bottom -= parseInt( $row.css( 'padding-bottom' ), 10 );
            }

            var left = $assetWrapperColumn.width() / 2;
            left += $assetWrapperColumn.offset().left - $row.offset().left;

            $iconWrapper.position( {
                my: 'center center',
                at: 'left+' + left + ' bottom+' + bottom,
                of: $row,
                collision: 'none'
            } );

            $icon.data( 'column', $assetWrapperColumn );

            if( appended == 1 ) {
                $icon.on( 'click', function( /*ev*/ ) {
                    var $column = $( this ).data( 'column' );

                    var $rowWrapper = $column.closest( 'div.swc_grid_row' ).first();
                    var locked = $rowWrapper.hasClass( 'swc_locked' );

                    //evt( 'reset_panel_stack', { 'left': true } );
                    evt( 'add_content_panel', { 'in_grid': true, '$column': $column, 'locked': locked } );
                } );
            }

            // Remove plus icon when leaving the asset

            $assetWrapper.on( 'mouseleave', function( /* el, ev*/ ) {
                $wrapper.find( '> .swc_asset_plus_icon_wrapper' ).addClass( 'swc_will_remove' );
                setTimeout( function() {
                    $wrapper.find( '.swc_will_remove' ).remove();
                }, 1000 );
            } );
        } );
    };

    function getElPos( $el ) {
        var pos = {};

        var offset = $el.offset();
        pos.x = offset.left;
        pos.y = offset.top;
        pos.w = $el.outerWidth( false );
        pos.h = $el.outerHeight( false );

        return pos;
    }

    function getPlusIconHtml( cls ) {
        return '<span ' +
        'class="' + cls + ' swc_asset_any_icon_wrapper cke_reset cke_widget_drag_handler_container" ' +
        'contenteditable="false" ' +
        'title="' + __( 'Click to add content' ) + '" ' +
        '>' +
        '<i class="fa fa-plus" contenteditable="false"></i>' +
        '</span>';
    }
} );
