define( [
    'jquery',
    'swiftylib/construct',
    'swiftylib/i18n/__'
], function(
    $, Construct, __
) {
    'use strict';

    var LinkType = Construct.extend( {
        insertNewLinkType: function( linkTypeSelect, infoTab ) {
            var self = this;
            var dfd = new $.Deferred();

            // Add a "Internal page" option to the link type select box.
            linkTypeSelect.items.unshift( [ __( 'Internal page' ), 'page' ] );

            infoTab.elements.push( {
                'type': 'vbox',
                'id': 'pageOptions',
                'children': [ {
                    'type': 'select',
                    'id': 'page',
                    'label': 'Select a page',
                    'items': [],
                    'onLoad': function( /*el*/ ) {
                        self._addPagesToSelectElement( this, dfd );
                    },
                    'setup': function( data ) {
                        var that = this;

                        dfd.done( function() {
                            if( data && data.type && data.type === 'page' && data.url && data.url.url ) {
                                var url = data.url.url;

                                // Internal page
                                if( url.indexOf( document.location.hostname ) > -1 ) {
                                    that.setValue( data.url.protocol + url );
                                }
                            }
                        } );
                    }
                } ]
            } );
        },

        linkTypeSelectOnChange: function( linkTypeSelect, editor ) {
            linkTypeSelect.onChange = CKEDITOR.tools.override( linkTypeSelect.onChange, function( original ) {
                return function() {
                    original.call( this );   // Run the default logic (handles all other select items)

                    // Fetch our UI that we've added to the dialog.
                    var dialog = this.getDialog();
                    var ourUIControls = dialog.getContentElement( 'info', 'pageOptions' )
                        .getElement().getParent().getParent();
                    var uploadTab;

                    // Handle our own link type option
                    if( this.getValue() === 'page' ) {
                        ourUIControls.show();

                        // Set the visible states for CkEditor's linkTarget and upload tabs for our option.
                        if( editor.config.linkShowTargetTab ) {
                            dialog.showPage( 'target' );
                        }

                        uploadTab = dialog.definition.getContents( 'upload' );

                        if( uploadTab && !uploadTab.hidden ) {
                            dialog.hidePage( 'upload' );
                        }
                    }
                    else {
                        ourUIControls.hide();
                    }
                };
            } );
        },

        linkTypeSelectSetup: function( linkTypeSelect ) {
            linkTypeSelect.setup = function( data ) {
                if( data ) {
                    if( !data.type ) {   // Set 'internal page' as default
                        data.type = 'page';
                    } else if( data.type && data.type === 'url' && data.url && data.url.url ) {
                        // Internal page
                        if( data.url.url.indexOf( document.location.hostname ) > -1 ) {
                            data.type = 'page';
                        }
                    }
                }

                this.setValue( data.type );
            };
        },

        linkTypeSelectCommit: function( linkTypeSelect ) {
            linkTypeSelect.commit = function( data ) {
                data.type = this.getValue();

                if( data.type === 'page' ) {
                    data.type = 'url';

                    var dialog = this.getDialog();
                    var pageUrl = dialog.getValueOf( 'info', 'page' );
                    var urlParts = [];
                    var protocol = 'http://';   // default
                    var splitter = '://';

                    if( pageUrl.indexOf( splitter ) > -1 ) {
                        urlParts = pageUrl.split( splitter );
                        protocol = urlParts[ 0 ] + splitter;

                        dialog.setValueOf( 'info', 'url', urlParts[ 1 ] );
                    } else {
                        dialog.setValueOf( 'info', 'url', pageUrl );
                    }

                    dialog.setValueOf( 'info', 'protocol', protocol );
                }
            };
        },

        _addPagesToSelectElement: function( that, dfd ) {
            $.post(
                scc_data.ajax_url,
                {
                    'action': 'swcreator_get_page_list',
                    'ajax_nonce': scc_data.ajax_nonce,
                    'id': scc_data.page_id
                }
            ).done(
                function( pages ) {
                    var pageList = [];
                    var builder = function( wpPages, indent ) {
                        $.each( wpPages, function( index, page ) {
                            if( page.menu ) {
                                builder( page.menu, indent + '-' );
                            } else {
                                page.title = indent + page.title;

                                pageList.push( page );
                            }
                        } );
                    };

                    builder( pages, '' );

                    $.each( pageList, function( index, page ) {
                        that.add( page.title, page.value );
                    } );

                    dfd.resolve();
                }
            );
        }
    } );

    return new LinkType();
} );