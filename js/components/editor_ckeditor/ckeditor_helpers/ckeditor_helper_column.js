define( [
    'jquery',
    'swiftylib/i18n/__',
    'swiftylib/evt'
], function(
    $, __, evt
) {
    'use strict';

    return function( component ) {

        if( !CKEDITOR.plugins.registered.swc_grid_column ) {
            CKEDITOR.plugins.add( 'swc_grid_column', {
                requires: 'widget',

                init: function( editor ) {
                    editor.widgets.add( 'swc_grid_column', {
                        editables: {
                            content: {
                                selector: '.swc_grid_column'
                            }
                        },

                        upcast: function( element ) {
                            return element.name === 'div' && element.hasClass( 'swc_grid_column' );
                        },

                        downcast: function( element ) {
                            var attrString = '';
                            var attrs = element.attributes;

                            if( attrs && attrs['data-grid_data'] ) {
                                attrs = $.parseJSON( attrs['data-grid_data'] );

                                $.each( attrs, function( key, val ) {
                                    if( key !== 'content' ) {
                                        // Trick to prevent enters messing up shortcode data.
                                        val = val.replace( /\n/g, '_=EnTEr=-' );

                                        attrString += ' ' + key + '=_=QUoTe=-' + val + '_=QUoTe=-';
                                    }
                                } );

                                element.name = 'swifty_grid_column_replacer';
                                element.attributes = {
                                    'data-grid-column-shortcode_data': attrString
                                };
                            }

                            return element;
                        },

                        init: function() {
                            var self = this;
                            var wrapper = self.wrapper;
                            var $dragHandlerContainer = $( wrapper.$ ).find( '> .cke_widget_drag_handler_container' );
                            var ckDragHandlerImage = wrapper.find( '> .cke_widget_drag_handler_container img' ).getItem( 0 );
                            var $dragHandlerImage = $dragHandlerContainer.find( 'img' );

                            // disable editable when widget is ready
                            self.on( 'ready', function() {
                                self.editables.content.$.setAttribute( 'contenteditable', 'false' );
                            } );

                            var $rowWrapper = $( wrapper.$ ).closest( 'div.swc_grid_row' ).first();
                            var locked = $rowWrapper.hasClass( 'swc_locked' );

                            if( ! locked || scc_data.swifty_edit_locked ) {
                                component.addPlusIconFunctionality( self, $dragHandlerContainer );
                            }

                            // Hide the CK drag handle for columns

                            $dragHandlerContainer.addClass( 'swc_hide_icon' );

                            component.addCogsIconFunctionality( self, ckDragHandlerImage, $dragHandlerImage, 0 );

                            // prevent delete and backspace
                            component.setupWidgetKeyDeletion( self );
                        }
                    } );
                }
            } );
        }

    }

} );