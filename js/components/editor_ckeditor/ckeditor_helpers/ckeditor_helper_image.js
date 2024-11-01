define( [
    'jquery',
    'swiftylib/i18n/__',
    'swiftylib/evt'
], function(
    $, __, evt
) {
    'use strict';

    return function( component ) {

        if( !CKEDITOR.plugins.registered.swc_image ) {
            CKEDITOR.plugins.add( 'swc_image', {
                requires: 'widget',

                beforeInit: function( editor ) {

                    var fileTools = CKEDITOR.fileTools;

                    editor.on( 'paste__', function( evt ) {
                        // For performance reason do not parse data if it does contain img tag.
                        if( ! evt.data.dataValue.match( /<img/i ) ) {
                            return;
                        }

                        var data = evt.data,
                        // Prevent XSS attacks.
                            tempDoc = document.implementation.createHTMLDocument( '' ),
                            temp = new CKEDITOR.dom.element( tempDoc.body ),
                            imgs, img, i;

                        var getLocation = function (href) {
                            var location = document.createElement('a');
                            location.href = href;
                            // IE doesn't populate all link properties when setting .href with a relative URL,
                            // however .href will return an absolute URL which then can be used on itself
                            // to populate these additional fields.
                            if (location.host === '') {
                                location.href = location.href;
                            }
                            return location;
                        }

                        // Without this isReadOnly will not works properly.
                        temp.data( 'cke-editable', 1 );

                        temp.appendHtml( data.dataValue );

                        imgs = temp.find( 'img' );

                        for ( i = 0; i < imgs.count(); i++ ) {
                            img = imgs.getItem( i );

                            // Image should not contain src=data:...
                            var src = img.getAttribute( 'src' );
                            var isDataInSrc = src && src.substring( 0, 5 ) === 'data:',
                                isRealObject = img.data( 'cke-realelement' ) === null;


                            // We are not uploading images in non-editable blocs and fake objects (#13003).
                            if ( ( getLocation( src ).hostname !== scc_data.domain ) && !isDataInSrc && isRealObject && !img.data( 'cke-upload-id' ) && !img.isReadOnly( 1 ) ) {
                                var loader = editor.uploadRepository.create( null );
                                loader.loadAndUpload( src );

                                fileTools.markElement( img, 'uploadimage', loader.id );

                                fileTools.bindNotifications( editor, loader );
                            }
                        }
                        data.dataValue = temp.getHtml();
                    } );
                },

                init: function( editor ) {
                    editor.widgets.add( 'swc_image', {

                        allowedContent:  {
                            img: {
                                attributes: '!src,alt,width,height'
                            },
                            figure: true,
                            figcaption: true
                        },

                        requiredContent: 'img[src,alt]',

                        parts: {
                            image: 'img',
                            caption: 'figcaption'
                        },

                        upcast: function( element ) {
                            var image;
                            // #11110 Don't initialize on pasted fake objects.
                            if ( element.attributes[ 'data-cke-realelement' ] ) {
                                return;
                            }
                            if( element.name === 'figure' ) {
                                image = element.getFirst( 'img' ) || element.getFirst( 'a' ).getFirst( 'img' );
                            } else if ( ( element.name === 'img' ) || (( element.name === 'a' ) && ( element.children.length === 1 && element.getFirst( 'img' ) ) ) ) {
                                image = element.name === 'a' ? element.children[ 0 ] : element;
                            }

                            if ( !image ) {
                                return;
                            }

                            // data URIs are not convertible
                            var imageSrc;
                            if( imageSrc = image.attributes.src ) {
                                if( imageSrc.substring( 0, 5 ) === 'data:' ) {
                                    return;
                                }
                            }

                            return true;
                        },

                        downcast: function( element ) {
                            return element;
                        },

                        init: function() {
                            var self = this;
                            var wrapper = self.wrapper;
                            var $dragHandlerContainer = $( wrapper.$ ).find( '> .cke_widget_drag_handler_container' );
                            var ckDragHandlerImage = wrapper.find( '> .cke_widget_drag_handler_container img' ).getItem( 0 );
                            var $dragHandlerImage = $dragHandlerContainer.find( 'img' );

                            // remove drag and drop text
                            $dragHandlerImage.attr( 'title', '' );

                            // Add our own asset icon to the CK drag handle
                            $dragHandlerContainer.prepend( '<i class="fa fa-cog" style="padding-left: 3px;"></i>' );

                            // Open the row panel when the CK drag handle is clicked
                            $dragHandlerImage.on( 'click', function( /*ev*/ ) {
                                var $el = $( this ).closest( '.cke_widget_wrapper' );

                                if( $( this ).hasClass( 'swc_hide_asset_icon' ) ) {
                                    return false;
                                }

                                // Open the row panel
                                evt(
                                    'swifty_editor_image_dialog',
                                    {
                                        '$image_wrapper': $el
                                    }
                                );
                            } );

                            component.addCogsIconFunctionality( self, ckDragHandlerImage, $dragHandlerImage, 0 );
                        }
                    } );
                }
            } );
        }
    };
} );