define( [
    'jquery',
    'swiftylib/i18n/__',
    'js/diverse/utils',
    'swiftylib/evt',
    './ckeditor_helper_mouse'
], function(
    $, __, Utils, evt, mouseFunctionality
) {
    'use strict';

    return function( component ) {

        mouseFunctionality();

        // Plugin is registered on global CKEDITOR (needs to be done only once).
        // It stays registered, even when the editor instance is destroyed.
        if( !CKEDITOR.plugins.registered.swc_asset ) {
            CKEDITOR.plugins.add( 'swc_asset', {
                requires: 'widget',

                init: function( editor ) {
                    // Placement over icon that shows when you hover over a widget
                    CKEDITOR.plugins.widget.prototype.updateDragHandlerPosition = CKEDITOR.tools.override(
                        CKEDITOR.plugins.widget.prototype.updateDragHandlerPosition, function( /*original*/ ) {
                            return function() {
                                // Below is copied (and adapted) code from CKEditor original source, and slightly changed
                                var editor = this.editor,
                                    domElement = this.element.$,
                                    isGridElement = $( domElement ).attr( 'data-widget' ) === 'swc_grid_row',
                                    oldPos = this._.dragHandlerOffset,
                                    newPos;

                                // image widgets are inline, and the container uses a span instead of div
                                if( domElement.parentElement && domElement.parentElement.nodeName === 'SPAN') {
                                    domElement = domElement.parentElement;
                                    newPos = {
                                        x: 0 + domElement.offsetWidth - 32,
                                        y: 0 - ( isGridElement ? 32 : 0 )
                                    };
                                } else {
                                    newPos = {
                                        x: domElement.offsetLeft + domElement.offsetWidth - 32,
                                        y: domElement.offsetTop - ( isGridElement ? 32 : 0 )
                                    };
                                }
                                newPos.x = newPos.x - 1;

                                if( oldPos && newPos.x === oldPos.x && newPos.y === oldPos.y ) {
                                    return;
                                }

                                // We need to make sure that dirty state is not changed (#11487).
                                var initialDirty = editor.checkDirty();

                                editor.fire( 'lockSnapshot' );
                                this.dragHandlerContainer.setStyles( {
                                    top: newPos.y + 'px',
                                    left: newPos.x + 'px',
                                    display: 'block'
                                } );
                                editor.fire( 'unlockSnapshot' );
                                !initialDirty && editor.resetDirty();

                                this._.dragHandlerOffset = newPos;
                                // End of copied code

                                // original.call( this ); // No need to call the original code, as it's included above.
                            };
                        }
                    );

                    editor.widgets.add( 'swc_asset', {
                        //editables: {
                        //    title: {
                        //        selector: '.su-box-title',
                        //        allowedContent: 'br strong em'
                        //    },
                        //    content: {
                        //        selector: '.su-box-content',
                        //        allowedContent: 'p br ul ol li strong em'
                        //    }
                        //},

                        //allowedContent:
                        //    //'div(!swc_asset); div(!swc_asset_cntnt); div(!su-box); div(!su-box-title); div(!su-box-content)',
                        //    'div[*]',

                        //requiredContent: 'div(swc_asset)',

                        upcast: function( element ) {
                            return ( element.name === 'div' || element.name === 'span' ) && element.hasClass( 'swc_asset' );
                        },

                        downcast: function( element ) {
                            var isBlock = element.name.toLowerCase() === 'div';
                            var elName = isBlock ? 'swifty_block_replacer' : 'swifty_inline_replacer';
                            var newElement = new CKEDITOR.htmlParser.element( elName );
                            var firstChild = element.getFirst();

                            if( firstChild ) {
                                var html = '';
                                var attrs = firstChild.attributes;

                                if( attrs && attrs['data-asset_data'] ) {
                                    attrs = $.parseJSON( Utils.atou( attrs['data-asset_data'] ) );

                                    $.each( attrs, function( key, val ) {
                                        if( key !== 'swc_shortcode' && key !== 'content' && key !== 'swc_shortcode_status' ) {
                                            if( parseFloat( key ) === key >>> 0 ) {  // Is this a integer?
                                                html += ' ' + val;
                                            } else {
                                                // Trick to prevent enters messing up shortcode data.
                                                if( typeof val === 'string' ) {
                                                    val = val.replace( /\n/g, '_=EnTEr=-' );
                                                }

                                                html += ' ' + key + '=_=QUoTe=-' + val + '_=QUoTe=-';
                                            }
                                        }
                                    } );

                                    html = '[' + attrs.swc_shortcode + html + ']';

                                    if( attrs.content ) {
                                        attrs.content = attrs.content.replace( /\n/g, '_=EnTEr=-' );
                                        html += '<!--keep_swifty_content_start-->' + attrs.content + '<!--keep_swifty_content_end-->';
                                    }

                                    var force_close_tag = component.forceShortcodeCloseTags[ attrs.swc_shortcode ] ? component.forceShortcodeCloseTags[ attrs.swc_shortcode ] : false;

                                    if( attrs.content || force_close_tag ) {
                                        html += '[/' + attrs.swc_shortcode + ']';
                                    }

                                    newElement.setHtml( html );
                                }
                            }

                            return newElement;
                        },

                        init: function() {
                            var self = this;
                            var wrapper = self.wrapper;
                            var $dragHandlerContainer = $( wrapper.$ ).find( '> .cke_widget_drag_handler_container' );
                            var $dragHandlerImage = $dragHandlerContainer.find( 'img' );
                            var ckDragHandlerImage = wrapper.find( '> .cke_widget_drag_handler_container img' ).getItem( 0 );

                            $( wrapper.$ ).css( 'clear', $( wrapper.$ ).find( '> .swc_asset' ).css( 'clear' ) );

                            var $assetWrapper = $( wrapper.$ ).find( 'div.swc_asset' ).first();
                            var locked = $assetWrapper.hasClass( 'swc_locked' );
                            if( ! locked || scc_data.swifty_edit_locked ) {
                                component.addResizeIconFunctionality( self, ckDragHandlerImage, $dragHandlerImage, $dragHandlerContainer );
                                component.addCogIconFunctionality( self, ckDragHandlerImage, $dragHandlerImage, $dragHandlerContainer, 'asset', false );
                                component.addPlusIconFunctionality( self, $dragHandlerContainer );
                            } else {
                                component.addCogIconFunctionality( self, ckDragHandlerImage, $dragHandlerImage, $dragHandlerContainer, 'asset', true );
                            }
                            // Add our own asset icon to the CK drag handle.
                            $dragHandlerContainer.prepend( '<i class="fa fa-cog" style="padding-left: 3px;"></i>' );
                            // Hide the CK drag handle.
                            $dragHandlerContainer.addClass( 'cke_widget_drag_handler_container_swc_hidden' );

                            // // Open the asset panel when the CK drag handle is clicked
                            //
                            // $dragHandlerImage.on( 'click', function( /*ev*/ ) {
                            //     var $el = $( this ).closest( '.cke_widget_wrapper' ).find( '.swc_asset_cntnt' );
                            //
                            //     if( $( this ).hasClass( 'swc_hide_asset_icon' ) ) {
                            //         return false;
                            //     }
                            //
                            //     // hide resizer when opening panel
                            //     $( '.swc_asset_resizer_icon_wrapper' ).remove();
                            //     $( '.swc_asset_cog_icon_wrapper, .swc_asset_any_icon_wrapper, .swc_asset_adv_icons_wrapper' ).remove();
                            //
                            //     // Open the asset panel
                            //     evt(
                            //         'swifty_editor_asset_dialog',
                            //         {
                            //             'el': $el,
                            //             'mod': 'icon_clicked',
                            //             'inline': self.inline,
                            //             'init_css': false
                            //         }
                            //     );
                            // } );

                            //component.addCogsIconFunctionality( self, ckDragHandlerImage, $dragHandlerImage, 1 );

                            // prevent delete and backspace
                            component.setupWidgetKeyDeletion( self );
                        }
                    } );
                }
            } );
        }


    };

} );