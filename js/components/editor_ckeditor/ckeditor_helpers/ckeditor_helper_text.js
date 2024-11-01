define( [
    'jquery',
    'swiftylib/i18n/__',
    'js/diverse/utils',
    'swiftylib/evt'
], function(
    $, __, Utils, evt
) {
    'use strict';

    return function( component ) {

        if( !CKEDITOR.plugins.registered.swc_text ) {
            CKEDITOR.plugins.add( 'swc_text', {
                requires: 'widget',

                init: function( editor ) {
                    editor.widgets.add( 'swc_text', {
                        editables: {
                            content: {
                                selector: '.swc_asset_cntnt'
                            }
                        },

                        upcast: function( element ) {
                            return element.name === 'div' && element.hasClass( 'swc_text' );
                        },

                        downcast: function( element ) {
                            var attrString = '';

                            var firstChild = element.getFirst();
                            if( firstChild ) {
                                element = firstChild;
                                var attrs = element.attributes;

                                if( attrs && attrs['data-asset_data'] ) {
                                    attrs = $.parseJSON( Utils.atou( attrs['data-asset_data'] ) );

                                    $.each( attrs, function( key, val ) {
                                        if( key !== 'content' ) {
                                            // Trick to prevent enters messing up shortcode data.
                                            val = val.replace( /\n/g, '_=EnTEr=-' );

                                            attrString += ' ' + key + '=_=QUoTe=-' + val + '_=QUoTe=-';
                                        }
                                    } );

                                    element.name = 'swifty_text_replacer';
                                    element.attributes = {
                                        'data-text_data': attrString
                                    };

                                    // Replace the text-asset content by the content of the .swc_asset_cntnt wrapper,
                                    // where containing assets are correctly downcast.
                                    // I would prefer a method that does not convert to dom and back,
                                    // because this could potentionaly change the html details or order of attributes,
                                    // but could not yet find a better solution.

                                    var html = this.editables.content.getData(); // This gets the downcasted content.
                                    // Remove the .swc_asset_cntnt wrapper.
                                    var $html = $( html );
                                    if( ! $html.hasClass( 'swc_asset_cntnt' ) ) {
                                        var $html2 = $html.find( '.swc_asset_cntnt' );
                                        if( $html2.length > 0 ) {
                                            $html = $html2;
                                        }
                                    }
                                    if( $html.length > 0 ) {
                                        // Ignore comment node.
                                        if( ( $html.length !== 1) || ( $html[0].nodeType !== CKEDITOR.NODE_COMMENT ) ) {
                                            element.setHtml( $html.html() );
                                        }
                                    }
                                }
                            }

                            return element;
                        },

                        init: function() {
                            var self = this;
                            var wrapper = self.wrapper;
                            var $dragHandlerContainer = $( wrapper.$ ).find( '> .cke_widget_drag_handler_container' );
                            var ckDragHandlerImage = wrapper.find( '> .cke_widget_drag_handler_container img' ).getItem( 0 );
                            var $dragHandlerImage = $dragHandlerContainer.find( 'img' );

                            $( wrapper.$ ).css( 'clear', $( wrapper.$ ).find( '> .swc_text' ).css( 'clear' ) );

                            var $assetWrapper = $( wrapper.$ ).find( 'div.swc_text' ).first();
                            var locked = $assetWrapper.hasClass( 'swc_locked' );
                            if( ! locked || scc_data.swifty_edit_locked ) {
                                component.addCogIconFunctionality( self, ckDragHandlerImage, $dragHandlerImage, $dragHandlerContainer, 'text', false );
                                component.addPlusIconFunctionality( self, $dragHandlerContainer );
                            } else {
                                component.addCogIconFunctionality( self, ckDragHandlerImage, $dragHandlerImage, $dragHandlerContainer, 'text', true );
                                // disable editable when widget is ready
                                self.on( 'ready', function() {
                                    self.editables.content.$.setAttribute( 'contenteditable', 'false' );
                                } );
                            }
                            // Add our own asset icon to the CK drag handle.
                            $dragHandlerContainer.prepend( '<i class="fa fa-cog" style="padding-left: 3px;"></i>' );
                            // Hide the CK drag handle.
                            $dragHandlerContainer.addClass( 'cke_widget_drag_handler_container_swc_hidden' );

                            // // Open the asset panel when the CK drag handle is clicked
                            // $dragHandlerImage.on( 'click', function( /*ev*/ ) {
                            //     var $el = $( this ).closest( '.cke_widget_wrapper' ).find( '.swc_text' );
                            //
                            //     if( $( this ).hasClass( 'swc_hide_asset_icon' ) ) {
                            //         return false;
                            //     }
                            //
                            //     // hide resizer when opening panel
                            //     $( '.swc_asset_resizer_icon_wrapper' ).remove();
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
                        }
                    } );
                }
            } );
        }

    }

} );