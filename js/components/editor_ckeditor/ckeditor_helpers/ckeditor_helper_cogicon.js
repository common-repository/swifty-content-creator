define( [
    'jquery',
    'swiftylib/i18n/__',
    'swiftylib/evt',
], function(
    $, __, evt
) {
    'use strict';

    // Handles cog icon for assets and text widgets and rows,

    function addCogs( self, ckDragHandlerImage, $dragHandlerImage, $dragHandlerContainer, asType, $wrapper, isLocked ) {

        var $textWrapper = $wrapper.closest( '.swc_text' );
        var $wrapper2 = $textWrapper.parent();
        if( $wrapper2.length > 0 ) {
            var locked = $textWrapper.hasClass( 'swc_locked' );
            var childIsLocked = ( locked && ( ! scc_data.swifty_edit_locked ) );
            addCogs( self, ckDragHandlerImage, $dragHandlerImage, $dragHandlerContainer, 'text', $wrapper2, childIsLocked );
        }

        var $rowWrapper = $wrapper.closest( '.swc_grid_row' );
        $wrapper2 = $rowWrapper.parent();
        if( $wrapper2.length > 0 ) {
            var locked = $rowWrapper.hasClass( 'swc_locked' );
            var childIsLocked = ( locked && ( ! scc_data.swifty_edit_locked ) );
            addCogs( self, ckDragHandlerImage, $dragHandlerImage, $dragHandlerContainer, 'row', $wrapper2, childIsLocked );
        }

        // Do not show the cog icon when the edit icon is hidden.
        if( $dragHandlerContainer.hasClass( 'swc_hide_asset_icon' ) ) {
            return;
        }

        var $editorWrapper = $wrapper;
        var $assetWrapper = $editorWrapper.find( 'div.swc_grid_row, div.swc_asset, div.swc_text' ).first();
        var $assetWrapperCK = $assetWrapper.parent();

        var locked = $assetWrapper.hasClass( 'swc_locked' );

        // Add the cog icon to the asset

        if( $assetWrapperCK.find( '.swc_asset_cog_icon_wrapper' ).length ) {
            $assetWrapperCK.find( '> .swc_asset_cog_icon_wrapper, > .swc_asset_any_icon_wrapper, > .swc_asset_adv_icons_wrapper' ).removeClass( 'swc_will_remove' );
        } else {
            var dragData = {};

            var $handler = $( getGuiIcon(
                'swc_asset_cog_icon_wrapper' + ( asType === 'row' ? ' swc_asset_cog_icon_row_wrapper' : '' ),
                // ( scc_data.swifty_gui_mode === 'advanced' ? '&#xe013;' : 'fa-cog' ),
                ( isLocked ? 'fa-lock' : 'fa-cog' ),
                ( isLocked ? '' : __( 'Click for options or drag to move' ) ), 0 )
            ).prependTo( $assetWrapperCK );

            var xOffsetTransform = 0;
            var yOffsetTransform = 0;
            if( typeof  swifty_parseMatrix === 'function' ) {
                // For scroll moving assets place the cog icons at the original position.
                var mx = swifty_parseMatrix( getComputedStyle( $assetWrapper[0], null ).transform );
                xOffsetTransform = mx.m41;
                yOffsetTransform = mx.m42;
            }

            $handler.position( {
                my: ( asType === 'row' ) ? 'right bottom-1' : 'right top',
                at: 'right-' + xOffsetTransform + ' top-' + yOffsetTransform,
                of: $assetWrapper,
                collision: 'none'
            } );

            evt( 'get_asset_name', {
                'el': $assetWrapper.find( '> .swc_asset_cntnt' ),
                'inline': false
            } ).then( function( name ) {
                if( asType === 'row' ) {
                    name = __( 'Columns' );
                }

                var $title = $( '<span class="swc_asset_title_icons_wrapper">' + name.toLowerCase() + '</span>' ).appendTo( $handler );

                $title.position( {
                    my: 'left',
                    at: 'right',
                    of: $handler,
                    collision: 'none'
                } );
            } );

            var posHandler = getElPos( $handler );

            // Make sure cog icons do not overlap.
            $( '.swc_asset_cog_icon_wrapper' ).not( $handler ).each( function( ii, el ) {
                var $el = $( el );
                var pos = getElPos( $el );

                if( posHandler.x < pos.x + pos.w && posHandler.x + posHandler.w > pos.x ) {
                    if( posHandler.y < pos.y + pos.h && posHandler.y + posHandler.h > pos.y ) {
                        $handler.position( {
                            my: 'top',
                            at: 'bottom+1',
                            of: $el,
                            collision: 'none'
                        } );
                    }
                }
            } );

            // Add extra icons in advanced gui mode
            var $advGuiWrapper = null;
            if( ( scc_data.swifty_gui_mode === 'advanced' ) && ! isLocked ) {
                $advGuiWrapper = $( '<span class="swc_asset_adv_icons_wrapper swc_asset_any_icon_wrapper swc_nopluscollision">' + getAdvGuiIcons( locked ) + '</span>' ).appendTo( $assetWrapperCK.find( '> .swc_asset_cog_icon_wrapper' ) );

                // Force the width of the wrapper. Otherwise they will wrap vertically.
                $advGuiWrapper.attr( 'style', 'width: ' + ( $advGuiWrapper.find( '.swc_asset_any_icon_wrapper' ).length * 33 ) + 'px !important;' );

                $advGuiWrapper.position( {
                    my: 'right',
                    at: 'left',
                    of: $handler,
                    collision: 'none'
                } );

                $advGuiWrapper.find( '.swc_asset_edit_icon_wrapper' ).on( 'click', function( /*ev*/ ) {
                    clickedIcon( 'edit', self, $( this ), asType );
                } );
                $advGuiWrapper.find( '.swc_asset_move_icon_wrapper' ).on( 'click', function( /*ev*/ ) {
                    clickedIcon( 'move', self, $( this ), asType );
                } );
                $advGuiWrapper.find( '.swc_asset_delete_icon_wrapper' ).on( 'click', function( /*ev*/ ) {
                    clickedIcon( 'delete', self, $( this ), asType );
                } );
                $advGuiWrapper.find( '.swc_asset_lock_icon_wrapper' ).on( 'click', function( /*ev*/ ) {
                    clickedIcon( 'lock', self, $( this ), asType );
                } );
                $advGuiWrapper.find( '.swc_asset_unlock_icon_wrapper' ).on( 'click', function( /*ev*/ ) {
                    clickedIcon( 'unlock', self, $( this ), asType );
                } );
                $advGuiWrapper.find( '.swc_asset_copy_icon_wrapper' ).on( 'click', function( /*ev*/ ) {
                    clickedIcon( 'copy', self, $( this ), asType );
                } );
            }

            if( isLocked ) {
                $handler.on( 'click', function( /*ev*/ ) {
                    clickedIcon( 'locked', self, $( this ), asType );
                } );
            } else {
                // Open the asset panel when the cog icon is clicked.
                $handler.on( 'click', function( /*ev*/ ) {
                    clickedIcon( 'main', self, $( this ), asType );
                } );

                $handler.draggable( {
                    cursorAt: { right: 0, top: 0 },
                    cursor: 'move',
                    delay: 100,
                    distance: 5,
                    opacity: 0.6,
                    zIndex: 999999,
                    appendTo: $( '.swc_page_cntnt.cke_editable' ),
                    helper: function() {
                        var pos = getElPos( $assetWrapper );
                        var $el = $assetWrapper.clone();
                        $el.addClass( 'swc_drag_ori_placeholder' );

                        // Replace video asset contents, because theay are too intensive to drag.
                        $el.find( '.swc_video_wrapper' ).replaceWith(
                            '<span class="swc_video_drag_wrapper">' + __( 'Video' ) + '</span>' );

                        var hMax = 200;
                        var hGradient = 100;
                        var h2 = pos.h;
                        if( h2 > hMax ) {
                            h2 = hMax;
                        }

                        $el = $( '<div></div>' ).append( $el );

                        var html = '<div class="swc_drag_helper" style="width: ' + pos.w + 'px; height: ' + h2 + 'px;">' +
                            '<svg style="width: ' + pos.w + 'px; height: ' + h2 + 'px;">' +
                            '  <defs>' +
                            '    <mask id="swc_drag_mask123" maskUnits="userSpaceOnUse" maskContentUnits="userSpaceOnUse">' +
                            '      <linearGradient id="swc_drag_gradient123" gradientUnits="objectBoundingBox" x2="0" y2="1">' +
                            '        <stop stop-color="white" stop-opacity="1" offset="0" />' +
                            '        <stop stop-color="white" stop-opacity="0" offset="1" />' +
                            '      </linearGradient>';
                        if( pos.h > hMax ) {
                            html += '      <rect x="0" y="0" width="' + pos.w + 'px" height="' + ( h2 - hGradient ) + 'px" fill="rgb(255,255,255)" />' +
                                '      <rect x="0" y="' + ( h2 - hGradient ) + '" width="' + pos.w + 'px" height="' + hGradient + 'px" fill="url(#swc_drag_gradient123)" />';
                        } else {
                            html += '      <rect x="0" y="0" width="' + pos.w + 'px" height="' + h2 + 'px" fill="rgb(255,255,255)" />';
                        }
                        html += '    </mask>' +
                            '  </defs>' +
                            '  <foreignObject width="100%" height="100%" style="mask: url(#swc_drag_mask123);">' +
                            $el.html();
                        '  </foreignObject>' +
                        '</svg>' +
                        '</div>';

                        $el = $( html );

                        dragData = {};
                        dragData.width = pos.w;

                        $assetWrapper.addClass( 'swc_drag_drag_hide' );
                        $el.addClass( 'swc_drag_drag_placeholder' );

                        return $el;
                    },
                    start: function( /*event, ui*/ ) {
                        $( 'body' ).addClass( 'swc_no_editor_hovers' );

                        // Fix for browsers not setting the drag cursor correctly (because of iframe?)
                        $( '*' ).addClass( 'swc_fix_cursor_move' );

                    },
                    stop: function( /*event, ui*/ ) {
                        $( 'body' ).removeClass( 'swc_no_editor_hovers' );

                        // Fix for browsers not setting the drag cursor correctly (because of iframe?)
                        $( '*' ).removeClass( 'swc_fix_cursor_move' );

                        $assetWrapper.removeClass( 'swc_drag_drag_hide' );

                        if( $( '.swc_drag_insert_placeholder' ).length > 0 ) {
                            $( '.swc_drag_helper' ).remove();

                            evt(
                                'swifty_editor_change_asset_location',
                                {
                                    '$assetWrapper': $assetWrapper,
                                    '$placeholder': $( '.swc_drag_insert_placeholder' )
                                }
                            );
                        }
                    },
                    drag: function( event, ui ) {
                        var xOffset = ui.offset.left;
                        var yOffset = ui.offset.top;
                        var x = xOffset + dragData.width;
                        var y = yOffset;

                        var $elOver = null;
                        var modeOver = 'prev';

                        if( asType === 'row' ) {
                            var posSet = 0;
                            $( '.swc_page_cntnt.cke_editable' ).find( '.swc_grid_row' ).not( '.swc_drag_ori_placeholder' ).not( '.swc_drag_drag_hide' ).each( function( ii, el ) {
                                var $el = $( el );
                                var pos = getElPos( $el );
                                if( y >= pos.y + pos.h / 2 ) {
                                    $elOver = $el.parent();
                                    modeOver = 'next';
                                    posSet ++;
                                }
                                if( posSet === 0 ) {
                                    $elOver = $( '.swc_page_cntnt.cke_editable' );
                                    modeOver = 'prepend';
                                }
                            } );
                        } else {
                            $( '.swc_page_cntnt.cke_editable' ).find( '.swc_grid_column, .swc_asset, .swc_text' ).not( '.swc_drag_ori_placeholder' ).not( '.swc_drag_drag_hide' ).each( function( ii, el ) {
                                var $el = $( el );
                                var pos = getElPos( $el );
                                if( x >= pos.x && x < pos.x + pos.w ) {
                                    if( y >= pos.y && y < pos.y + pos.h ) {
                                        if( $el.hasClass( 'swc_text' ) ) {
                                            var posSet = 0;
                                            $el.find( '> .swc_asset_cntnt > *' ).not( '.swc_drag_ori_placeholder' ).not( '.swc_drag_drag_hide' ).each( function( ii, el ) {
                                                var $el2 = $( el );
                                                var pos2 = getElPos( $el2 );
                                                if( y >= pos2.y + pos2.h / 2 ) {
                                                    $elOver = $el2;
                                                    modeOver = 'next';
                                                    posSet ++;
                                                }
                                            } );
                                            if( posSet === 0 ) {
                                                $elOver = $el.find( '.swc_asset_cntnt' );
                                                modeOver = 'prepend';
                                            }
                                            // Mouse over the top 5px of the text asset will move the asset above the text asset instead of inside.
                                            if( y < pos.y + 5 ) {
                                                $elOver = $el.closest( '.swc_grid_column' );
                                                modeOver = 'prepend';
                                            }
                                            // Mouse over the bottom 5px of the text asset will move the asset below the text asset instead of inside.
                                            if( y > pos.y + pos.h - 5 ) {
                                                $elOver = $el;
                                                modeOver = 'next';
                                            }
                                        }
                                        if( $el.hasClass( 'swc_grid_column' ) ) {
                                            var posSet = 0;
                                            $el.find( '> .cke_widget_wrapper > .swc_asset, > .cke_widget_wrapper > .swc_text' ).not( '.swc_drag_ori_placeholder' ).not( '.swc_drag_drag_hide' ).each( function( ii, el ) {
                                                var $el2 = $( el );
                                                var pos2 = getElPos( $el2 );
                                                if( y >= pos2.y + pos2.h / 2 ) {
                                                    $elOver = $el2.parent();
                                                    modeOver = 'next';
                                                    posSet ++;
                                                }
                                            } );
                                            if( posSet === 0 ) {
                                                $elOver = $el;
                                                modeOver = 'prepend';
                                            }
                                        }
                                    }
                                }
                            } );
                        }

                        if( $elOver ) {
                            var $oldPlaceholder = $( '.swc_drag_insert_placeholder' );
                            var $placeholder = $( '<div class="swc_drag_insert_placeholder"></div>' );

                            if( modeOver === 'prepend' ) {
                                if( ! $elOver.children().first().hasClass( 'swc_drag_insert_placeholder' ) ) {
                                    $placeholder.prependTo( $elOver );
                                    $oldPlaceholder.remove();
                                }
                            } else if( modeOver === 'next' ) {
                                if( ! $elOver.next().hasClass( 'swc_drag_insert_placeholder' ) ) {
                                    $placeholder.insertAfter( $elOver );
                                    $oldPlaceholder.remove();
                                }
                            } else {
                                if( ! $elOver.prev().hasClass( 'swc_drag_insert_placeholder' ) ) {
                                    $placeholder.insertBefore( $elOver );
                                    $oldPlaceholder.remove();
                                }
                            }
                        } else {
                            $( '.swc_drag_insert_placeholder' ).remove();
                        }
                    }
                } );
            }
        }
    }

    function getElPos( $el ) {
        var pos = {};

        var offset = $el.offset();
        pos.x = offset.left;
        pos.y = offset.top;
        pos.w = $el.outerWidth( false );
        pos.h = $el.outerHeight( false );

        return pos;
    }

    function getGuiIcon( cls, icon, title, divider ) {
        var html = '<span ' +
        'class="' + cls + ' swc_asset_any_icon_wrapper cke_reset cke_widget_drag_handler_container' +
        ( divider === 1 ? ' swc_nopluscollision' : '' ) +
        ( scc_data.swifty_gui_mode === 'advanced' ? ' swc_asset_adv_icon_wrapper' : '' ) +
        '" ' +
        'contenteditable="false" ' +
        'title="' + title + '" ' +
        '>' +
        ( icon.substr( 0, 3 ) !== 'fa-' ? '<div class="swc_icon" contenteditable="false"><span aria-hidden="true" contenteditable="false">' + icon + '</span></div>'
            : '<i class="fa ' + icon + '" contenteditable="false"></i>' ) +
        '</span>';
        
        if( divider === 1 ) {
            html += '<span class="swc_icon_wr_divider"></span>';
        }
        
        return html;
    }

    function getAdvGuiIcons( locked ) {
        var html = '';

        if( scc_data.swifty_change_lock ) {
            if( locked ) {
                html += getGuiIcon( 'swc_asset_unlock_icon_wrapper', 'fa-lock', '', 1 );
            } else {
                html += getGuiIcon( 'swc_asset_lock_icon_wrapper', 'fa-unlock', '', 1 );
            }
        }

        html += getGuiIcon( 'swc_asset_delete_icon_wrapper', '&#xe00b;', '', 1 );
        html += getGuiIcon( 'swc_asset_copy_icon_wrapper', 'fa-copy', '', 1 );
        html += getGuiIcon( 'swc_asset_move_icon_wrapper', '&#xe013;', '', 1 );
        // html += getGuiIcon( 'swc_asset_edit_icon_wrapper', '&#xe03f;', '', 1 );

        return html;
    }

    function clickedIcon( mod, self, $clickEl, asType ) {

        // Hide resizer when opening panel.
        $( '.swc_asset_resizer_icon_wrapper' ).remove();

        if( asType === 'row' ) {
            var $el = $clickEl.closest( '.cke_widget_wrapper' ).find( '.swc_grid_row' );

            if( $clickEl.hasClass( 'swc_hide_asset_icon' ) ) {
                return;
            }

            $( '.swc_asset_cog_icon_wrapper, .swc_asset_any_icon_wrapper, .swc_asset_adv_icons_wrapper' ).remove();

            // Open the row panel.
            if( mod === 'main' && scc_data.swifty_gui_mode !== 'advanced' ) {
                evt(
                    'swifty_editor_row_dialog',
                    {
                        '$row': $el,
                        'mod': 'icon_clicked',
                        'inline': self.inline
                    }
                );
            } else if(  mod === 'main' && scc_data.swifty_gui_mode === 'advanced' ) {
                // evt( 'swifty_editor_row_move_dialog', { '$row': $el } );
                evt( 'swifty_editor_row_edit_dialog', { '$row': $el } );
            } else if( mod === 'edit' ) {
                evt( 'swifty_editor_row_edit_dialog', { '$row': $el } );
            } else if( mod === 'move' ) {
                evt( 'swifty_editor_row_move_dialog', { '$row': $el } );
            } else if( mod === 'delete' ) {
                evt( 'swifty_editor_row_delete_dialog', { '$row': $el } );
            } else if( mod === 'lock' ) {
                evt( 'swifty_editor_row_lock', { '$row': $el, 'locked': true } );
            } else if( mod === 'unlock' ) {
                evt( 'swifty_editor_row_lock', { '$row': $el, 'locked': false } );
            } else if( mod === 'copy' ) {
                evt( 'swifty_editor_row_copy', { '$row': $el } );
            } else if( mod === 'locked' ) {
                evt( 'swifty_editor_row_locked', {} );
            }
        } else {
            var $el = $clickEl.closest( '.cke_widget_wrapper' ).children( '.swc_text, .swc_asset' ).children( '.swc_asset_cntnt' );

            if( $clickEl.hasClass( 'swc_hide_asset_icon' ) ) {
                return;
            }

            $( '.swc_asset_cog_icon_wrapper, .swc_asset_any_icon_wrapper, .swc_asset_adv_icons_wrapper' ).remove();

            // Open the asset panel
            if( mod === 'main' && scc_data.swifty_gui_mode !== 'advanced' ) {
                evt(
                    'swifty_editor_asset_dialog',
                    {
                        'el': $el,
                        'mod': 'icon_clicked',
                        'inline': self.inline,
                        'init_css': false
                    }
                );
            } else if(  mod === 'main' && scc_data.swifty_gui_mode === 'advanced' ) {
                // evt( 'swifty_editor_asset_move_dialog', { 'el': $el } );
                evt( 'swifty_editor_asset_edit_dialog', { 'el': $el } );
            } else if( mod === 'edit' ) {
                evt( 'swifty_editor_asset_edit_dialog', { 'el': $el } );
            } else if( mod === 'move' ) {
                evt( 'swifty_editor_asset_move_dialog', { 'el': $el } );
            } else if( mod === 'delete' ) {
                evt( 'swifty_editor_asset_delete_dialog', { 'el': $el } );
            } else if( mod === 'lock' ) {
                evt( 'swifty_editor_asset_lock', { 'el': $el, 'locked': true } );
            } else if( mod === 'unlock' ) {
                evt( 'swifty_editor_asset_lock', { 'el': $el, 'locked': false } );
            } else if( mod === 'copy' ) {
                evt( 'swifty_editor_asset_copy', { 'el': $el } );
            } else if( mod === 'locked' ) {
                evt( 'swifty_editor_asset_locked', {} );
            }
        }
    }

    return function( self, ckDragHandlerImage, $dragHandlerImage, $dragHandlerContainer, asType, isLocked ) {
        var $wrapper = $( self.wrapper.$ );

        // When the mouse enters an asset the cog icon is shown on top of the asset.
        $wrapper.on( 'mouseenter', function( /*el, ev*/ ) {
            addCogs( self, ckDragHandlerImage, $dragHandlerImage, $dragHandlerContainer, asType, $wrapper, isLocked );
        } );

        // Remove cog icon when leaving the asset.
        $wrapper.on( 'mouseleave', function( /* el, ev*/ ) {
            $wrapper.find( '> .swc_asset_cog_icon_wrapper, > .swc_asset_any_icon_wrapper, > .swc_asset_adv_icons_wrapper' ).addClass( 'swc_will_remove' );
            setTimeout( function() {
                $wrapper.find( '.swc_will_remove' ).remove();
            }, 1000 );
        } );
    }
} );
