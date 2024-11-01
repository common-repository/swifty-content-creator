define( [
    'jquery',
    'swiftylib/i18n/__',
    'swiftylib/evt'
], function(
    $, __, evt
) {
    'use strict';

    return function( component ) {

        if( !CKEDITOR.plugins.registered.swc_grid_row ) {
            CKEDITOR.plugins.add( 'swc_grid_row', {
                requires: 'widget',

                init: function( editor ) {
                    editor.widgets.add( 'swc_grid_row', {
                        editables: {
                            content: {
                                selector: '.swc_grid_row'
                            }
                        },

                        upcast: function( element ) {
                            return element.name === 'div' && element.hasClass( 'swc_grid_row' );
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

                                element.name = 'swifty_grid_row_replacer';
                                element.attributes = {
                                    'data-grid-row-shortcode_data': attrString
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

                            // Disable editable when widget is ready.
                            self.on( 'ready', function() {
                                self.editables.content.$.setAttribute( 'contenteditable', 'false' );
                            } );

                            // remove drag and drop text
                            $dragHandlerImage.attr( 'title', '' );

                            var $rowWrapper = $( wrapper.$ ).find( 'div.swc_grid_row' ).first();
                            var locked = $rowWrapper.hasClass( 'swc_locked' );
                            var isLocked = ( locked && ( ! scc_data.swifty_edit_locked ) );
                            component.addCogIconFunctionality( self, ckDragHandlerImage, $dragHandlerImage, $dragHandlerContainer, 'row', isLocked );
                            // Add our own asset icon to the CK drag handle.
                            $dragHandlerContainer.prepend( '<i class="fa fa-cog" style="padding-left: 3px;"></i>' );
                            // Hide the CK drag handle.
                            $dragHandlerContainer.addClass( 'cke_widget_drag_handler_container_swc_hidden' );

                            // Open the row panel when the CK drag handle is clicked

                            $dragHandlerImage.on( 'click', function( /*ev*/ ) {
                                var $el = $( this ).closest( '.cke_widget_wrapper' ).find( '.swc_grid_row' );

                                if( $( this ).hasClass( 'swc_hide_asset_icon' ) ) {
                                    return false;
                                }

                                // Open the row panel
                                evt(
                                    'swifty_editor_row_dialog',
                                    {
                                        '$row': $el,
                                        'mod': 'icon_clicked',
                                        'inline': self.inline
                                    }
                                );
                            } );

                            //component.addCogsIconFunctionality( self, ckDragHandlerImage, $dragHandlerImage, 0 );

                            // prevent delete and backspace
                            component.setupWidgetKeyDeletion( self );
                        }
                    } );
                }
            } );
        }

    }

} );