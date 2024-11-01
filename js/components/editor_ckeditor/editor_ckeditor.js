window.CKEDITOR_GETURL = function( resource) {
    //console.log( 'window.CKEDITOR_GETURL', resource );
    if( resource.indexOf( 'plugins/' ) === 0 ) {
        return scc_data.swcreator_url + 'js/libs/ckeditor/' + resource;
    }
};

define( [
    'jquery',
    'can',
    'swiftylib/component',
    'swiftylib/view',
    'swiftylib/evt',
    'stache!js/components/editor_ckeditor/editor_ckeditor.stache',
    'js/libs/jBox.min',
    'swiftylib/i18n/__',
    'js/diverse/utils',
    './ckeditor_helpers/ckeditor_helper_seperate',
    './ckeditor_helpers/ckeditor_helper_widgets',
    'js/libs/yepnope',
    'js/libs/mout/src/function/throttle',
    // No return values:
    'js/libs/jquery-ui',
    'css!./editor_ckeditor.css',
    'css!../tooltip/tooltip.css'
], function(
    $, can, Component, View, evt, tmpl, JBox, __, Utils, addSeperateFunctionality, initWidgets, yepnope, throttle
) {
    'use strict';

    return Component.extend( {
        tag: 'swifty_editor_ckeditor',
        template: new View( tmpl ),
        lastUndoImage: null,
        undoImageStack: null,

        scope: {
            className: 'swifty_editor',
            selector: '@',
            googleFonts: {
                'sans': [
                    'Lato',
                    'Montserrat',
                    'Open Sans',
                    'Roboto',
                    'Muli',
                    'Ubuntu',
                    'Oswald',
                    'Nunito'
                ], 'serif': [
                    'Lora',
                    'Playfair Display',
                    'Noto Serif',
                    'Josefin Slab',
                    'Roboto Slab',
                    'Arvo'
                ], 'special': [
                    'Sacramento',
                    'Cabin Sketch',
                    'Capriola',
                    'Amatic SC',
                    'Londrina Solid',
                    'Pacifico',
                    'Delius Swash Caps',
                    'Bubblegum Sans',
                    'Special Elite'
                ]
            },

            ini: function( /*$el*/ ) {
                var self = this;
                var component = this;

                addSeperateFunctionality( this );

                //var req = typeof( require ) !== 'undefined' ? require : swifty.require;
                //req( [ scc_data.swcreator_url + 'js/libs/ckeditor/ckeditor.js' ], function() {
                //    req( [ scc_data.swcreator_url + 'js/libs/ckeditor/adapters/jquery.js' ], function() {
                yepnope.injectJs( {
                    _url: scc_data.swcreator_url + 'js/libs/ckeditor/ckeditor.js' + '?swcv=scc_' + scc_data.scc_version
                }, function() {
                    yepnope.injectJs( {
                        _url: scc_data.swcreator_url + 'js/libs/ckeditor/adapters/jquery.js' + '?swcv=scc_' + scc_data.scc_version
                    }, function() {

                        initWidgets( component );

                        // Empty p, i and divs tags must be allowed.
                        CKEDITOR.dtd.$removeEmpty.p = 0;
                        CKEDITOR.dtd.$removeEmpty.i = 0;
                        CKEDITOR.dtd.$removeEmpty.div = 0;
                        // do remove empty span tags. When they are not removed changing fonts / colors of selected
                        // text will result in a lot of empty span tags and empty lines will be removed. There is a
                        // side effect when content already contains a lot of empty span tags: they will result in empty
                        // lines. This only happen one time, after this the empty spans will be properly cleaned
                        CKEDITOR.dtd.$removeEmpty.span = 1;
                        CKEDITOR.basePath = scc_data.swcreator_url + 'js/libs/ckeditor/';
                        CKEDITOR.plugins.basePath = scc_data.swcreator_url + 'js/libs/ckeditor/plugins/';

                        CKEDITOR.on( 'instanceReady', function() {
                            if( typeof swifty_add_exec === 'function' ) {
                                swifty_add_exec( { 'status': 'release', 'for': 'ck_inited' } );
                            }

                            // Setting contenteditable to false causes at least 2 problems:
                            // - On some page multiple CK instances get initialised and the tollbars don't have our config.
                            // - On first click on one of the icons (or not editable assets) the page scrolls and the click fails.
                            // We did set contenteditable to false previously because we want to prevent adding text outside of rows and outside of text assets.
                            // Preliminary test seems to indicate that that is no longer easlily achieved anyways.
                            // $( self.attr( 'selector' ) ).attr( 'contenteditable', 'false' );

                            evt( 'swifty_update_scrolleffect' );
                        } );

                        var $editor = $( self.attr( 'selector' ) ).attr( 'contenteditable', 'true' );
                        self.createEditor( $editor );

                        self.instance = $editor.editor;

                        // var focusManager = new CKEDITOR.focusManager( self.instance );
                        // focusManager.focus();
                        $editor.focus();

                        self.tooltip = null;
                        self.$toolbar = null;
                        self.set_focus = 1;
                        self.$asset_in_edit_mode = null;
                        self.swifty_insert_content = false;
                        self.sticky_editor = +scc_data.editor_visibility;   // '0' or '1'
                        self.forceShortcodeCloseTags = {};

                        // Calling the change event after each change was taking too much time,
                        // it is now throttled to 300 ms.
                        self.triggerChangeThrottledObject = throttle( $.proxy( self.triggerChangeEvent, self ), 300 );

                        if( self.instance ) {
                            self.setupEventHandlers();
                            self.setupSave();
                            self.setupCursorGuard();
                            self.setupSelectionQuard();
                            self.setupAssetWidget();
                            self.setupInsert();
                            self.registerSwiftyEditorAssets();
                            self.fillForceShortcodeCloseTags();
                        }
                    } );
                } );
            },

            createEditor: function( $element ) {
                var self  = this;

                var ckeOptions = {
                    'toolbar': [
                        [
                            'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', 'Bold', 'Italic', 'Underline',
                            'Strike', 'RemoveFormat', 'NumberedList', 'BulletedList', 'Outdent', 'Indent',
                            'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'
                        ],
                        '/',
                        [
                            'Format', 'Font', 'FontSize', 'TextColor', 'BGColor', 'Link', 'Unlink',
                            'Undo', 'Redo', 'Table', 'Smiley', 'SpecialChar', 'HideEditor'
                        ]
                    ],
                    'format_tags': 'p;h1;h2;h3;h4;h5;h6',
                    'language': scc_data.locale,
                    'title': false,
                    'extraPlugins': 'swc_asset,swc_text,swc_grid_row,swc_grid_column,swc_image,uploadimage',
                    'imageUploadUrl': scc_data.swifty_upload_url,
                    // Add 'contextmenu,tabletools' if you want to (temporary) remove the right mouse context menu
                    'removePlugins': 'tab,liststyle,magicline,forms,scayt,wsc,image',
                    'undoStackSize': 50, // 20 = default, 50 = what we want
                    'baseFloatZIndex': 10000,
                    'allowedContent': true,
                    'enterMode': CKEDITOR.ENTER_P,   // this is the default
                    'font_names': 'Arial/Arial, Helvetica, sans-serif;' +
                    'Courier New/Courier New, Courier, monospace;' +
                    'Georgia/Georgia, serif;' +
                    'Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;' +
                    'Times New Roman/Times New Roman, Times, serif;' +
                    'Trebuchet MS/Trebuchet MS, Helvetica, sans-serif;' +
                    'Verdana/Verdana, Geneva, sans-serif',
                    'resize_enabled': false,
                    'entities_latin': false,
                    'entities_greek': false,
                    'entities': false,
                    'basicEntities': false
                };

                // if( scc_data && scc_data.area && scc_data.area === 'main' ) {
                    ckeOptions.font_names += ';';
                    self.googleFonts.each( function( fontGroup, ky ) {
                        var kind = ky;
                        var alt = '';
                        if( ky === 'sans' ) {
                            kind = 'Google sans serif';
                            alt = 'sans-serif';
                        }
                        if( ky === 'serif' ) {
                            kind = 'Google serif';
                            alt = 'serif';
                        }
                        if( ky === 'special' ) {
                            kind = 'Google special';
                            alt = 'monospace';
                        }
                        $.each( fontGroup, function( ii, font ) {
                            ckeOptions.font_names += kind + ': ' + font + '/' + font + ', ' + alt + ';'
                        } );
                    } );
                // }

                return $element.ckeditor( ckeOptions );
            },

            showStartUpTooltip: function() {
                if( this.tooltip ) {
                    this.destroySingle( 'tooltip' );
                }

                this.tooltip = this.setupTooltip();
                this.tooltip.open();
            },

            setupTooltip: function() {
                return new JBox( 'Tooltip', {
                    'content': __( 'Start typing here or add content<br> by using the button at the bottom.' ),
                    'target': $( '.swc_page_cntnt.cke_editable p br' ),
                    'reposition': true,
                    'position': {
                        'x': 'right',
                        'y': 'left'
                    },
                    'closeOnClick': true,
                    'outside': 'x',
                    'offset': {
                        'x': 16,
                        'y': 10
                    }
                } );
            },

            onPluginsLoadedHandler: function( ev ) {
                var self = this;

                ev.editor.addCommand && ev.editor.addCommand( 'hideEditor', {
                    'exec': function( /*editor*/ ) {
                        self.setEditorVisibilityByAjax( 0 );
                    }
                } );

                ev.editor.ui.addButton && ev.editor.ui.addButton( 'HideEditor', {
                    'label': __( 'Show editor on text selection' ),
                    'command': 'hideEditor',
                    'icon': scc_data.ckeditor_path + 'images/x.png'
                } );

                ev.editor.commands.hideEditor.disable();
            },

            // When you click outside the editor, for instance on a button in a panel, the editor would lose focus.
            // This handler makes sure the foucus is restored.
            // While an edit panel is opened, this handler is temp disabled via swifty_editor_set_focus,
            // so the edit fields in that edit panel can get focus.

            onBlurHandler: function( ev ) {
                var self = this;

                if( self.sticky_editor ) {
                    if( self.set_focus ) {
                        // var focusManager = new CKEDITOR.focusManager( ev.editor );
                        // focusManager.focus();
                        ev.editor.focus();
                    }
                } else {
                    if( self.set_focus ) {
                        // var focusManager = new CKEDITOR.focusManager( ev.editor );
                        // focusManager.focus();
                        ev.editor.focus();

                        self.toggleToolbarVisibility( self.isTextSelection() );

                        setTimeout( function() {
                            var selectedWidget = $( '.cke_widget_selected' );

                            if( selectedWidget.length === 1 ) {
                                self.setWidgetFocus( { '$asset': selectedWidget.find( '.swc_asset,.swc_text,.swc_grid_row' ) }, true );
                            }
                        }, 1 );
                    } else {
                        if( self.isTextSelection() ) {
                            self.resetSelection();
                            self.toggleToolbarVisibility( false );
                        }
                    }
                }
            },

            onContentDomHandler: function( ev ) {
                var self = this;
                var commands = ev.editor.commands;

                commands.hideEditor && commands.hideEditor[ self.sticky_editor ? 'enable' : 'disable' ]();

                $.each( [ 'mousedown', 'mouseup', 'keyup', 'touchend' ], function( index, eventName ) {
                    var editable = ev.editor.editable();

                    editable.attachListener( editable, eventName, self.onMouseKeyTouchHandler, self );
                } );
            },

            onMouseKeyTouchHandler: function( ev ) {
                var self = this;
                var eventName = ev.name.toLowerCase();

                if( !self.sticky_editor ) {
                    if( eventName === 'mousedown' ) {
                        // the next line was commented because it disables the doubleclick selection in IE
                        // it was introduced to keep the selection when clicking a selection
                        //self.resetSelection();
                        self.mouse_down_state = true;
                    } else {
                        if( eventName === 'mouseup' ) {
                            self.mouse_down_state = false;
                        }

                        self.setEditorVisibility();

                    }
                }
                if( eventName === 'keyup' ) {
                    self.destroySingle( 'tooltip' );

                    // This fix will prevent multiple pastes when CTRL+V is hold down a bit longer.
                    // self.pasteOccured is a CanJS Compute and the change handler is located in
                    // ckeditor_helper_setup_event_handler.js
                    if ( ev.data.getKeystroke() === 1114198 ) {   // 1114198 -> CTRL+V
                        self.pasteOccured( null );
                    }
                }
            },

            setEditorVisibilityByAjax: function( showIt ) {
                var self = this;
                var dfd = new $.Deferred();

                if( !showIt ) {
                    // calculate the start position for the animation
                    var $editor = $( '#cke_editor1' );
                    if( $editor.length === 1 ) {

                        var offset = $editor.offset();
                        var posX = offset.left;
                        var posY = 0;

                        if( $editor.css( 'position' ) === 'absolute' ) {
                            posY = offset.top - $( window ).scrollTop();
                        }

                        var position_start = {
                            'top': posY + 'px',
                            'left': posX + 'px',
                            'width': $editor.css( 'width' ),
                            'height': $editor.css( 'height' ),
                            'opacity': 0,
                            'background': '#f0f0f0',
                            'position': 'fixed',
                            'z-index': 10001
                        };

                        evt( 'editor_toolbar_minimize', { 'position_start': position_start } );
                    }
                }

                self.sticky_editor = showIt;
                self.toggleToolbarVisibility( showIt );

                evt( 'set_editor_visibility_state', {
                    'show_it': showIt
                } );

                self.instance.fire( 'contentDom' );

                $.post(
                    scc_data.ajax_url,
                    {
                        'action': 'set_editor_visibility',
                        'show_it': showIt,
                        'id': scc_data.page_id,
                        'ajax_nonce': scc_data.ajax_nonce
                    }
                );

                dfd.resolve( showIt );

                return dfd;
            },

            setEditorVisibility: function() {
                var self = this;

                this.toggleToolbarVisibility( this.isTextSelection() );

                setTimeout( function() {
                    self.resetPanelStack();
                }, 1 );
            },

            toggleToolbarVisibility: function( visible ) {
                var self = this;

                if( visible ) {
                    setTimeout( function() {
                        self.$toolbar.parent().show();
                        self.$toolbar.show();

                        // CKEditor toolbar is not positioned correctly when a scroll occurred before the toolbar
                        // becomes visible. Here we reposition the toolbar.
                        self.repositionToolbar();
                    }, 1 );
                } else {
                    self.$toolbar.parent().hide();
                    self.$toolbar.hide();
                }
            },

            resetSelection: function() {
                this.instance.getSelection().removeAllRanges();
            },

            resetPanelStack: function() {
                var selection = this.instance.getSelection();
                var doReset = false;
                var focusedEl = $( '.cke_widget_focused' );
                var selectedEl, isWidget;

                if( selection ) {
                    if( this.$asset_in_edit_mode ) {
                        selectedEl = selection.getSelectedElement();
                        isWidget = this.isWidgetElement( selectedEl );

                        if( !isWidget ) {
                            focusedEl.removeClass( 'cke_widget_focused' );
                            doReset = true;
                        } else if(
                            isWidget && ( +isWidget !== +this.getWidgetIdAssetBased( this.$asset_in_edit_mode ) )
                        ) {
                            if( focusedEl && focusedEl.length > 1 ) {
                                focusedEl.not( $( selectedEl.$ ) ).each( function() {
                                    $( this ).removeClass( 'cke_widget_focused' );
                                } );
                            }

                            doReset = true;
                        }

                        if( doReset ) {
                            this.$asset_in_edit_mode = null;
                            this.set_focus = 1;
                            this.setAssetEditIconVisibility( true );
                            evt( 'reset_panel_stack' );
                        }
                    }
                }
            },

            isTextSelection: function() {
                var selection = this.instance.getSelection();
                var range, text;

                if( selection ) {
                    range = selection.getRanges()[ 0 ];
                    text = $.trim( selection.getSelectedText() );

                    // Unlock the selection, otherwise no changes can be done to de selection
                    this.unlockSelection();

                    if( !range ) {
                        selection = this.instance.getSelection();
                        range = selection.getRanges()[ 0 ];
                        text = $.trim( selection.getSelectedText() );
                    }

                    return ( range && text && text.length > 0 ) ? true : false;
                } else {
                    return false;
                }
            },

            unlockSelection: function( opts ) {
                var self = this;

                self.instance.unlockSelection();

                setTimeout( function() {
                    // $asset is only set when the edit panel is visible
                    if( opts && opts.$asset ) {
                        self.setWidgetFocus( opts, true );
                    }
                }, 1 );
            },

            repositionToolbar: function() {
                var $editor = $( '#cke_' + this.instance.name );
                var $article = $( this.instance.element.$ );

                if( this.$toolbar.offset().top === $article.offset().top ) {
                    $editor.css(
                        'top',
                        $editor.position().top - $editor.height()
                    );
                }

                if( this.$toolbar.offset().top < $( window ).scrollTop() ) {
                    $editor.css(
                        'top',
                        $editor.position().top + $editor.height() + $article.outerHeight( true )
                    );
                }
            },

            isContentElement: function( node ) {
                return node && node.equals( this.getContentElement() );
            },

            getContentElement: function() {
                return this.instance && this.instance.element;
            },

            isParagraphElement: function( node ) {
                return ( node && node.type === 1 && ( node.is( 'p' ) || node.is( 'div' ) ) && !this.isWidgetElement( node ) );
            },

            getParagraphElement: function( node ) {
                var self = this;

                if( !self.isContentElement( node ) ) {
                    return node.getAscendant( function( el ) {
                        if( self.isParagraphElement( el ) ) {
                            return el;
                        }
                    }, true );   // includeSelf set to true.
                }
            },

            /**
             * get list of paragraphs and widgets, ignore paragraphs in widgets and accepts only widgets containing asset
             *
             * @returns {CKEDITOR.dom.nodeList}
             */
            getParagraphAndWidgetElements: function() {
                var list = $( this.getContentElement().$ ).find( 'div.cke_widget_wrapper, p:not("div.swc_asset p,div.swc_text p")' );
                list = list.filter( 'p, div:has( > .swc_asset), div:has( > .swc_text)' );

                return new CKEDITOR.dom.nodeList( list.get() );
            },

            /**
             * get list of paragraphs and rows in root of content
             *
             * @returns {CKEDITOR.dom.nodeList}
             */
            getParagraphAndRowElements: function() {
                var list = $( this.getContentElement().$ ).children( 'div.cke_widget_wrapper, p' );
                return new CKEDITOR.dom.nodeList( list.get() );
            },

            getRootElements: function( node ) {
                var list;

                if( node && this.isTextAsset( node ) ) {
                    list = $( node.$ ).find( '.swc_asset_cntnt > p, .swc_asset_cntnt > div.cke_widget_wrapper' );
                } else if( node && this.isGridColumn( node ) ) {
                    list = $( node.$ ).children( 'div.cke_widget_wrapper' );
                } else {
                    list = $( this.getContentElement().$ ).children();
                }

                return new CKEDITOR.dom.nodeList( list.get() );
            },

            isTextAsset: function( node ) {
                if( node && node.type === 1 ) {
                    if( node.hasClass( 'swc_text' ) ) {
                        return true;
                    }

                    if( node.hasClass( 'cke_widget_wrapper' ) ) {
                        var firstNode = node.getChild( 0 );

                        return firstNode.hasClass( 'swc_text' );
                    }
                }

                return false;
            },

            isGridColumn: function( node ) {
                return ( node && node.type === 1 && node.hasClass( 'swc_grid_column' ) );
            },

            getGridRows: function() {
                var list = $( this.getContentElement().$ ).children().children( '.swc_grid_row' );

                return new CKEDITOR.dom.nodeList( list.get() );
            },

            getGridColumnsFromRow: function( rowNode ) {
                var list = $( rowNode.$ ).find( '.swc_grid_column' );

                return new CKEDITOR.dom.nodeList( list.get() );
            },

            isCurrentInsertPositionInTableOrGrid: function() {
                var self = this;

                var range = self.instance.getSelection().getRanges()[ 0 ];
                if( range ) {
                    if( self.getAscendantTextAsset( range.startContainer ) ) {
                        return false;
                    }
                    if( self.getTableOrGrid( range.startContainer ) ) {
                        return true;
                    }
                }
                return false;
            },

            isCurrentInsertPositionInWidget: function() {
                var self = this;
                var range = self.instance.getSelection().getRanges()[ 0 ];
                if( range ) {
                    if( self.getClosestWidget( range.startContainer ) ) {
                        return true;
                    }
                }
                return false;
            },

            isCurrentInsertPositionInAsset: function() {
                var self = this;
                var range = self.instance.getSelection().getRanges()[ 0 ];
                if( range && range.startContainer ) {
                    if( range.startContainer.getAscendant( function( el ) {
                            if( el.type === 1 && ( ( el.getName() === 'div' ) && ( el.hasClass( 'swc_asset' ) /*|| el.hasClass( 'swc_text' )*/ ) ) ) {
                                return el;
                            }
                        }, true )    // includeSelf set to true, needed for empty divs.
                    ) {
                        return true;
                    }
                }
                return false;
            },

            appendHtmlToList: function( list, html ) {
                var self = this;

                var nrOfItems;

                // hack to remove fillingchar which causes an selection error when adding a asset to a column
                self.instance.fire( 'beforeSetMode' );

                if( list && ( nrOfItems = list.count() ) ) {
                    var lastNode = list.getItem( nrOfItems - 1 );
                    var range = self.instance.createRange();

                    range.setStartAfter( lastNode );
                    range.collapse( true );
                    range.select();

                    // hack to remove fillingchar which causes an selection error when adding a asset to a column
                    self.instance.fire( 'beforeSetMode' );
                    
                    try {
                        self.instance.insertHtml( html, range );
                    } catch( e ) {
                        // Sometimes setting the range after inserting HTML will fail. In this case we will simply continue
                        // with what is done next: calling this event
                        if( self.instance.getSelection().getRanges().length === 0 ) {
                            self.instance.fire( 'afterInsertHtml', {} );
                        } else {
                            throw e;
                        }
                    }
                } else {
                    try {
                        self.instance.insertHtml( html );
                    } catch( e ) {
                        // Sometimes setting the range after inserting HTML will fail. In this case we will simply continue
                        // with what is done next: calling this event
                        if( self.instance.getSelection().getRanges().length === 0 ) {
                            self.instance.fire( 'afterInsertHtml', {} );
                        } else {
                            throw e;
                        }
                    }
                }
            },

            appendHtmlToSelector: function( html ) {
                var self = this;

                $( self.attr( 'selector' ) ).attr( 'contenteditable', 'true' );

                var rootElements = self.getRootElements();

                self.appendHtmlToList( rootElements, html );

                // $( self.attr( 'selector' ) ).attr( 'contenteditable', 'false' );

                rootElements = self.getRootElements();

                return rootElements.getItem( rootElements.count() - 1 );
            },

            appendHtmlToColumn: function( $column, html ) {
                var self = this;

                $( self.attr( 'selector' ) ).attr( 'contenteditable', 'false' );
                $column.attr( 'contenteditable', 'true' );

                var elements = self.getRootElements( new CKEDITOR.dom.node( $column[0] ) );

                if( elements.count() === 0 ) {
                    var range = self.instance.createRange();

                    range.setStartAt( new CKEDITOR.dom.node( $column[0] ), CKEDITOR.POSITION_AFTER_START );
                    range.collapse( true );
                    range.select();
                }

                self.appendHtmlToList( elements, html );

                $column.attr( 'contenteditable', 'false' );
                $( self.attr( 'selector' ) ).attr( 'contenteditable', 'true' );
            },

            /**
             * Go to an editable position within the $column
             *
             * @param $column
             */
            setColumnRangeForInsert: function( $column ) {
                var self = this;

                // var focusManager = new CKEDITOR.focusManager( self.instance );
                // focusManager.focus();
                self.instance.focus();

                var range = self.instance.createRange();
                range.moveToElementEditablePosition( new CKEDITOR.dom.node( $column[0].firstChild ), 1 );
                range.select();
            },

            setParagraphRangeForInsert: function( $p ) {
                var self = this;

                // var focusManager = new CKEDITOR.focusManager( self.instance );
                // focusManager.focus();
                self.instance.focus();

                var range = self.instance.createRange();
                range.moveToElementEditablePosition( new CKEDITOR.dom.node( $p[0] ), 1 );
                range.select();
            },

            setAssetInsertPosition: function( only_insert_at_root, force_html_insert ) {
                var self = this;

                var selection = self.instance.getSelection();
                var selectedEl = selection.getSelectedElement();
                var range = selection.getRanges()[ 0 ];
                var startContainer = null;

                // No range found. We create one and set the cursor in the first paragraph.
                if( ! range ) {
                    range = self.instance.createRange();
                    range.moveToElementEditablePosition( self.getContentElement().find( 'p' ).getItem( 0 ), false );
                    self.instance.getSelection().selectRanges( [ range ] );
                }

                // Never insert inside other (text)asset.
                if( only_insert_at_root || self.isCurrentInsertPositionInAsset() ) {
                    // is current position within table or columns?
                    startContainer = range.startContainer;
                    var containingTableOrGrid = self.getTableOrGrid( startContainer );
                    if( containingTableOrGrid ) {
                        // find top table or columns in hierarchy
                        while( containingTableOrGrid ) {
                            startContainer = containingTableOrGrid;
                            containingTableOrGrid = self.getTableOrGrid( startContainer.getParent() );
                        }
                        // get the container of the row, this container will be in the self.getRootElements()
                        if( startContainer.hasClass( 'swc_grid_row' ) ) {
                            startContainer = startContainer.getParent();
                        }

                        var items = self.getRootElements();
                        var nrOfItems = items.count();

                        if( nrOfItems > 1 ) {   // More than one first level elements
                            // find location of this top element, and move just before it
                            var curPos = self.getCurPos( startContainer, items );
                            startContainer = items.getItem( Math.max( 0, curPos - 1 ) );
                            if( ( force_html_insert ) || ! self.isWidgetElement( startContainer ) ) {
                                range.moveToPosition( startContainer, CKEDITOR.POSITION_BEFORE_START );
                                range.select();
                                return null;
                            } else {
                                return startContainer;
                            }
                        }
                    }
                }

                // A widget is selected.
                if( self.isWidgetElement( selectedEl ) ) {
                    if( force_html_insert ) {
                        range.moveToPosition( paragraphEl, CKEDITOR.POSITION_BEFORE_START );
                    } else {
                        return selectedEl;
                    }
                } else {
                    startContainer = range.startContainer;
                    var table = self.getTableOrRowOrCell( startContainer, 'table' );

                    if( table ) {
                        self.moveToPositionInTable( range, table );
                    } else {
                        var paragraphEl = self.getParagraphElement( startContainer );

                        if( paragraphEl ) {
                            if( force_html_insert ) {
                                range.moveToPosition( paragraphEl, CKEDITOR.POSITION_BEFORE_START );
                            } else {
                                return paragraphEl;
                            }
                        } else {
                            range.moveToPosition( self.getContentElement(), CKEDITOR.POSITION_AFTER_START );
                        }
                    }
                }

                range.select();
                return null;
            },

            moveToPositionInTable: function( range, table ) {
                var tableCell = this.getTableOrRowOrCell( range.startContainer, 'td' );

                if( tableCell.getName() === 'td' ) {
                    range.moveToPosition( tableCell, CKEDITOR.POSITION_AFTER_START );
                } else {
                    range.moveToPosition( table, CKEDITOR.POSITION_BEFORE_START );
                }
            },

            registerSwiftyEditorAssets: function() {
                evt( 'register_swifty_editor_assets', { 'assets': [
                    //{ 'category': 'textual', 'action': 'smiley', 'order': '10','icon': '&#xe04a;', 'name': __( 'Smiley' ) },
                    //{ 'category': 'textual', 'action': 'specialchar', 'order': '20', 'icon': '&#xe04b;', 'name': __( 'Special character' ) },
                    //{ 'category': 'textual', 'action': 'horizontalrule', 'order': '40', 'icon': '&#xe046;', 'name': __( 'Horizontal rule' ) },
                    //{ 'category': 'interactive', 'action': 'anchor', 'order': '990', 'icon': '&#xe045;', 'name': __( 'Anchor tag' ) },
                    //{ 'category': 'clipboard', 'action': 'paste', 'order': '10', 'icon': '&#xe047;', 'name': __( 'Paste' ) },
                    //{ 'category': 'clipboard', 'action': 'pastefromword', 'order': '20', 'icon': '&#xe048;', 'name': __( 'Paste from Word' ) },
                    //{ 'category': 'clipboard', 'action': 'pastetext', 'order': '20', 'icon': '&#xe049;', 'name': __( 'Paste as text' ) },
                    //{ 'category': 'layout', 'action': 'table', 'order': '200', 'icon': '&#xe04c;', 'name': __( 'Table' ) }
                ] } );
            },

            /**
             *  Fill the member forceShortcodeCloseTags with shortcode and force_close_tag combinations.
             */
            fillForceShortcodeCloseTags: function() {
                var self = this;

                evt( 'get_asset_force_close_tags' ).then( function( data ) {
                    self.forceShortcodeCloseTags = data;
                } );
            },

            /**
             * After a triple click the selection could be extended beyond the current paragraph into the next widget
             * causing a lot of trouble when deleting / changing styles. This event will check if the current selection
             * is bigger than 1 area with editablecontent and make sure to shrink the selection range to only 1 paragraph
             */
            setupSelectionQuard: function() {
                var self = this;

                this.instance.on( 'selectionCheck', function( evt ) {

                    // ignore fake selections
                    if( evt.data && evt.data && evt.data.isFake ) {
                        return true;
                    }

                    var ranges = evt.data.getRanges();

                    if( ranges && ranges.length === 1 ) {

                        var range = ranges[0];

                        // This check has only meaning when not collapsed and the end container is in the start of a element.
                        if( ! range.collapsed && ( range.endOffset === 0 ) && ( range.endContainer instanceof CKEDITOR.dom.element ) ) {
                            var walker = new CKEDITOR.dom.walker( range ),
                                nodes = [],
                                node;

                            while( node = walker.next() ) {
                                nodes.push( node );
                            }

                            var container, crosses;

                            // Check if the closest parents of each node are all the same editable element.
                            // If not, the range crosses editable boundaries one way or another.
                            OuterLoop:
                                for( var i = 0; i < nodes.length; i ++ ) {
                                    var parents = nodes[i].getParents( ! ! 'closest first' );

                                    parents.shift();

                                    for( var j = 0; j < parents.length; j ++ ) {
                                        if( parents[j].getAttribute( 'contenteditable' ) === 'true' ) {
                                            if( ! container ) {
                                                container = parents[j]; // i == 0
                                                break;
                                            } else {
                                                if( ! parents[j].equals( container ) ) {
                                                    crosses = true;
                                                    node = nodes[i - 1]; // i > 0
                                                    break OuterLoop;
                                                }
                                            }
                                        }
                                    }
                                }

                            if( crosses ) {
                                range.setEndAfter( range.startContainer );
                                self.instance.getSelection().selectRanges( [range] );
                            }
                        }
                        return true;
                    }
                } );
            },

            setupCursorGuard: function() {
                var self = this;

                self.instance.on( 'selectionChange', function( evt ) {
                    // ignore fake selections
                    if( evt.data && evt.data.selection && evt.data.selection.isFake ) {
                        return true;
                    }
                    if( self.swifty_insert_content ) {
                        return true;
                    }

                    var elementsList = [],
                        isContentEditable = true,
                        // Use elementPath to consider children of editable only (#11124).
                        elementsChain = self.instance.elementPath().elements;

                    // Starts iteration from body element, skipping html.
                    for( var j = elementsChain.length; j --; ) {
                        var element = elementsChain[j],
                            ignore = 0;

                        isContentEditable =
                            element.hasAttribute( 'contenteditable' ) ?
                            element.getAttribute( 'contenteditable' ) === 'true' : isContentEditable;

                        // // If elem is non-contenteditable, and it's not specifying contenteditable
                        // // attribute - then elem should be ignored.
                        // if( ! isContentEditable && ! element.hasAttribute( 'contenteditable' ) )
                        //     ignore = 1;

                        if( ! ignore ) {
                            elementsList.unshift( element );
                        }
                    }

                    var in_editable_area = false;
                    for( var w = 0; w < elementsList.length; w ++ ) {
                        var currentElement = elementsList[w];

                        // Sometimes a non content element is selected, catch them and return selection to editable area.
                        if( w === 0 ) {
                            // Could change to switch.
                            if( currentElement.getName() === 'tbody' ) {
                                in_editable_area = false;
                                break;
                            }

                            if( currentElement.getName() === 'tr' ) {
                                in_editable_area = false;
                                break;
                            }

                            // ignore this selection when it is hidden (used when loosing focus)
                            if( currentElement.hasAttribute( 'data-cke-hidden-sel' ) ) {
                                return true;
                            }
                        }

                        // // If selection is inside a non-editable element, break from loop and reset selection.
                        // if( currentElement.hasClass( 'swifty_not_editable' ) ) {
                        //     in_editable_area = false;
                        //     break;
                        // }

                        if( currentElement.hasClass( 'swc_locked' ) ) {
                            in_editable_area = false;
                            break;
                        }

                        // if( currentElement.hasClass( 'swifty_editable' ) ) {
                        //     in_editable_area = true;
                        // }

                        // Is this the editable of a text asset?
                        if( currentElement.hasClass( 'swc_asset_cntnt' ) && currentElement.getParent().hasClass( 'swc_text' ) ) {
                            in_editable_area = true;
                        }
                    }

                    if( ! in_editable_area ) {
                        var $newLocation = self.getContentElement().find( '.swc_text > .swc_asset_cntnt p' );
                        if( $newLocation.length > 0 ) {
                            self.instance.getSelection().removeAllRanges();

                            var range = self.instance.createRange();
                            range.moveToElementEditablePosition( $newLocation.getItem( 0 ), false );
                            self.instance.getSelection().selectRanges( [ range ] );
                            return false;
                        }
                    }

                } );
            },

            setupSave: function() {
                var self = this;

                // If anything changes send out an event
                self.instance.on( 'change', function( ev ) {

                    // make sure this icon is not saved with content
                    $( '.swc_asset_plus_icon_wrapper' ).remove();

                    // Outcommented this code because table element is moved back to the CKE toolbar.
                    // $( '.swc_table_edit_button' ).remove();

                    $( '.swc_asset_resizer_icon_wrapper' ).remove();
                    $( '.swc_asset_cog_icon_wrapper, .swc_asset_any_icon_wrapper, .swc_asset_adv_icons_wrapper' ).remove();
                    self.removeRefreshSpinner();

                    var html = ev.editor.getData();

                    evt(
                        'swifty_editor_content_changed',
                        {
                            'content': html
                        }
                    );

                    evt( 'check_can_undo_redo' );
                } );

                // Receive incoming content change events
                can.bind.call( window, 'evt_swc_swifty_editor_set_new_content', function( ev, opts ) {
                    self.instance.setData( opts.content );
                    evt( 'add_exec', { 'fn': 'swifty_check_inserts' } );
                    if( opts.dfd ) {
                        opts.dfd.resolve();
                    }
                } );

                // Handle save requests
                can.bind.call( window, 'evt_swc_swifty_editor_trigger_save', function( /*ev, opts*/ ) {
                    self.triggerChangeThrottledObject();
                } );
            },

            triggerChangeEvent: function() {
                this.instance.fire( 'change' );
            },

            addRefreshSpinner: function( opts ) {
                $( opts.container ).prepend( '<span class="swc_asset_refreshing"><i class="fa fa-refresh fa-spin"></i></span>' );
            },

            removeRefreshSpinner: function() {
                $( '.swc_asset_refreshing' ).remove();
            },

            contentModfied: function() {
                this.instance.fire( 'change' );
            },

            setupWidgetKeyDeletion: function( widget ) {
                widget.on( 'key', function( evt ) {
                    switch( evt.data.keyCode ) {
                        case 8:
                        case 46:
                            evt.cancel();
                            evt.stop();
                    }
                } );
            },

            setFocus: function( opts ) {
                if( !opts ) {
                    return;
                }

                this.set_focus = opts.focus;
            },

            openEditAfterInsert: function( $element, inline, initCss ) {
                if( $( $element ).hasClass( 'swc_asset_state_ins_updating' ) ) {
                    $( $element ).removeClass( 'swc_asset_state_ins_updating' );

                    // Open the asset panel
                    evt(
                        'swifty_editor_asset_dialog',
                        {
                            'el': $( $element ),
                            'mod': 'added_new',
                            'inline': inline,
                            'init_css': initCss
                        }
                    );
                }
            },

            setAssetEditIconVisibility: function( visible ) {
                $( '.cke_widget_drag_handler_container' )[
                    visible ? 'removeClass' : 'addClass'
                ]( 'swc_hide_asset_icon' );
                $( '.cke_widget_wrapper .cke_widget_element' )[
                    visible ? 'removeClass' : 'addClass'
                    ]( 'swc_hide_outline' );
            },

            /**
             * get the table or row, the returned node tells if current node is located in a table or grid even when nested
             *
             * @param node
             * @returns {*|CKEDITOR.dom.node|CKEDITOR.htmlParser.element}
             */
            getTableOrGrid: function( node ) {
                return node.getAscendant( function( el ) {
                    if( el.type === 1 && ( ( el.getName() === 'table' ) ||
                        ( ( el.getName() === 'div' ) && el.hasClass( 'swc_grid_row' ) ) ||
                        ( ( el.getName() === 'div' ) && el.hasClass( 'swc_grid_column' ) ) ) ) {
                        return el;
                    }
                }, true );   // includeSelf set to true, needed for empty divs.
            },

            getAscendantTextAsset: function( node ) {
                return node.getAscendant( function( el ) {
                    if( el.type === 1 && (
                        ( ( el.getName() === 'div' ) && el.hasClass( 'swc_text' ) ) ) ) {
                        return el;
                    }
                }, true );   // includeSelf set to true, needed for empty divs.
            },

            getTableOrRowOrCell: function( node, name ) {
                return node.getAscendant( function( el ) {
                    if( el.type === 1 && el.getName() === name ) {
                        return el;
                    }
                }, true );   // includeSelf set to true, needed for empty divs.
            },

            /**
             * get the row or column div in which this node lives
             *
             * @param node
             * @param name 'swc_grid_column' or 'swc_grid_row'
             * @returns {*|CKEDITOR.dom.node|CKEDITOR.htmlParser.element}
             */
            getGridRowOrColumn: function( node, name ) {
                return node.getAscendant( function( el ) {
                    if( el.type === 1 && ( el.getName() === 'div' ) && el.hasClass( name ) ) {
                        return el;
                    }
                }, true );   // includeSelf set to true, needed for empty divs.
            },

            /**
             * return index of curItem in items
             *
             * @param curItem
             * @param items
             * @returns {number}
             */
            getCurPos: function( curItem, items ) {
                for( var i = 0, len = items.count(); i < len; i++ ) {
                    if( curItem.equals( items.getItem( i ) ) ) {
                        return i;
                    }
                }
            },

            getNewPos: function( move, curPos ) {
                return ( move === 'up' || move === 'left' ) ? curPos - 1 : curPos + 1;
            },

            /**
             * get the widget, the returned node tells if current node is located in a block widget even when nested
             *
             * @param node
             * @returns {*|CKEDITOR.dom.node|CKEDITOR.htmlParser.element}
             */
            getClosestWidget: function( node ) {
                return node.getAscendant( function( el ) {
                    if( el.type === 1 && ( ( el.getName() === 'div' ) && el.hasClass( 'cke_widget_element' ) ) ) {
                        return el;
                    }
                }, true );   // includeSelf set to true, needed for empty divs.
            },

            moveRow: function( opts ) {
                if( ! opts ) {
                    return;
                }

                var curWidgetObj = this.getWidget( this.getWidgetIdAssetBased( opts.$row ) );
                var curWidget = curWidgetObj.wrapper;

                if( curWidget && opts.move ) {
                    switch( opts.move ) {
                        case 'up':
                        case 'down':

                            var items = this.getParagraphAndRowElements();   // NodeList of P and widget elements.
                            var nrOfItems = items.count();

                            if( nrOfItems > 1 ) {   // More than one first level elements
                                var curPos = this.getCurPos( curWidget, items );

                                if( ( opts.move === 'up' && curPos > 0 ) ||
                                    ( opts.move === 'down' && curPos < nrOfItems - 1 )
                                ) {
                                    var newPos = this.getNewPos( opts.move, curPos );

                                    curWidget[ opts.move === 'up' ? 'insertBefore' : 'insertAfter' ](
                                        items.getItem( newPos )
                                    );
                                } else if ( opts.move === 'up' && curPos === 0 ) {
                                    // put at the top of the page
                                    curWidget.move( this.getContentElement(), true );
                                } else if ( opts.move === 'down' && curPos === nrOfItems - 1 ) {
                                    // put at the bottom of the page
                                    curWidget.move( this.getContentElement(), false );
                                }
                            }

                            break;
                        default:
                            return;
                    }

                    curWidget.scrollIntoView();

                    this.contentModfied();
                }
            },

            insertPAsset: function( opts ) {
                if( ! opts ) {
                    return;
                }

                var curWidgetObj = this.getWidget( this.getWidgetIdAssetBased( opts.$asset ) );
                var curWidget = curWidgetObj.wrapper;

                if( curWidget && opts.action ) {
                    // use the nbsp to allow editing below a asset, otherwise it is shown behind the asset when inserting
                    var p = CKEDITOR.dom.element.createFromHtml( '<p>&nbsp;</p>' );
                    p[opts.action === 'above' ? 'insertBefore' : 'insertAfter']( curWidget );

                    this.contentModfied();
                }
            },

            triggerSetPositionEvent: function( el, position ) {
                evt(
                    'swifty_editor_set_position',
                    {
                        'position': position,
                        'el': el
                    }
                );
            },

            moveInsideItemWithWidgets: function( move, widget, newPos, items ) {
                widget[ move === 'left' ? 'insertBefore' : 'insertAfter' ](
                    items.getItem( newPos )
                );
            },

            /**
             * move widget(node) to the end of another column
             *
             * @param move
             * @param widget
             * @param newPos
             * @param items
             */
            moveToNewColum: function( move, widget, newPos, items ) {
                var newColumn = items.getItem( newPos );
                widget.move( newColumn, false ); // use true to put at start of column
            },

            moveToItemWithWidgets: function( move, widget, newPos, items ) {
                var newCell = items.getItem( newPos );
                var widgetsInNewCell = newCell.find( '.cke_widget_wrapper' );
                var nrOfWidgetsInNewCell = widgetsInNewCell.count();

                if( nrOfWidgetsInNewCell ) {
                    newPos = move === 'left' ? nrOfWidgetsInNewCell - 1 : 0;

                    widget[ move === 'left' ? 'insertAfter' : 'insertBefore' ](
                        widgetsInNewCell.getItem( newPos )
                    );
                } else {
                    widget.move( newCell, true );
                }
            },

            setCursor: function( opts ) {
                var $assetEl, $asset, widgetId, widget;

                if( opts && opts.$asset ) {
                    $asset = opts.$asset;
                } else {   // yes-no panel doesn't know about the asset.
                    $assetEl = $( '.cke_widget_selected' ).find( '.swc_asset,.swc_text,.swc_grid_row' );

                    if( $assetEl.length ) {
                        $asset = $assetEl;
                    }
                }

                if( $asset && $asset.length ) {
                    widgetId = this.getWidgetIdAssetBased( $asset );
                    widget = this.getWidget( widgetId );

                    if( widget ) {
                        this.setWidgetFocus( { '$asset': $asset }, false );
                        this.moveCursorToClosestEditableElement( widget.wrapper, widget.inline );
                    }
                }
            },

            moveCursorToClosestEditableElement: function( node, inline ) {
                var table = this.getTableOrRowOrCell( node, 'table' );
                var range = this.instance.createRange();
                var editableEl = null;

                if( inline ) {
                    editableEl = node;
                } else {
                    if( table ) {
                        var allCells = table.find( 'td' );

                        for( var i = 0, cellLen = allCells.count(); i < cellLen; i++ ) {
                            var cell = allCells.getItem( i );
                            var children = cell.getChildren();

                            for( var k = 0, childLen = children.count(); k < childLen; k++ ) {
                                var child = children.getItem( k );

                                // Text or BR node
                                if( child.type === 3 || ( child.type === 1 && child.is( 'br' ) ) ) {
                                    editableEl = child;
                                    break;
                                }
                            }

                            if( editableEl ) {
                                break;
                            }
                        }

                        if( !editableEl ) {
                            editableEl = this.getEditableElement( table );
                        }
                    } else {
                        editableEl = this.getEditableElement( node );
                    }
                }

                range.moveToElementEditablePosition(
                    editableEl || this.getContentElement(),
                    inline  // isMoveToEnd -> true or false
                );

                this.instance.getSelection().selectRanges( [ range ] );
            },

            getEditableElement: function( node ) {
                var pAndWidgetElements = this.getParagraphAndWidgetElements();   // NodeList of P and widget elements.
                var curPos = this.getCurPos( node, pAndWidgetElements );
                var editableEl = null;
                var el;

                if( curPos >= 0 ) {
                    for( var i = curPos, len = pAndWidgetElements.count(); i < len; i++ ) {
                        el = pAndWidgetElements.getItem( i );

                        if( this.isParagraphElement( el ) ) {
                            editableEl = el;
                            break;
                        }
                    }

                    if( !editableEl ) {
                        for( var j = curPos; j >= 0; j-- ) {
                            el = pAndWidgetElements.getItem( j );

                            if( this.isParagraphElement( el ) ) {
                                editableEl = el;
                                break;
                            }
                        }
                    }
                }

                return editableEl;
            },

            isWidgetElement: function( node ) {
                return ( node && node.type === 1 && node.hasAttribute( 'data-cke-widget-id' ) ) ?
                    node.getAttribute( 'data-cke-widget-id' ) :
                    null;
            },

            getWidget: function( widgetId ) {
                var widgets = this.instance.widgets;
                var curWidgetArr = $.map( widgets.instances, function( widgetInstance /*, k*/ ) {
                    // widgetId can be a number or a string, so we do a typecast.
                    if( widgetInstance.id === +widgetId ) {
                        return widgetInstance;
                    }
                } );

                return curWidgetArr && curWidgetArr.length === 1 ? curWidgetArr[ 0 ] : null;
            },

            getWidgetIdAssetBased: function( $asset ) {
                return $asset ? $asset.closest( '.cke_widget_wrapper' ).data( 'cke-widget-id' ) : null;
            },

            setWidgetFocus: function( opts, focus ) {
                var widgetId = this.getWidgetIdAssetBased( opts.$asset );
                var curWidgetObj = this.getWidget( widgetId );
                if( curWidgetObj ) {
                    var curWidget = curWidgetObj.wrapper;

                    curWidget && curWidget[ focus ? 'addClass' : 'removeClass' ]( 'cke_widget_focused' );
                }
            },

            setAssetInEditMode: function( opts ) {
                this.$asset_in_edit_mode = opts && opts.$asset;
                this.setAssetEditIconVisibility( this.$asset_in_edit_mode ? false : true );

                // all focus rectangles are hidden in setAssetEditIconVisibility, so get it back for the
                // active asset
                if( this.$asset_in_edit_mode ) {
                    this.$asset_in_edit_mode.removeClass( 'swc_hide_outline' );
                }
            },

            documentMouseUp: function() {
                if( !this.sticky_editor && this.mouse_down_state ) {
                    this.instance.fire( 'blur' );
                }
            },

            // Outcommented this code because table element is moved back to the CKE toolbar.
            // openTableProperties: function( opts ) {
            //     var tables = this.getContentElement().find( 'table' );
            //     var range;
            //
            //     for( var i = 0, len = tables.count(); i < len; i++ ) {
            //         var table = tables.getItem( i );
            //
            //         if( $( table.$ ).is( opts.$table ) ) {
            //             range = this.instance.createRange();
            //
            //             range.moveToElementEditablePosition( table, false );
            //
            //             this.instance.getSelection().selectRanges( [ range ] );
            //             this.instance.fire( 'doubleclick', { 'element': table } );
            //         }
            //     }
            // },

            destroySingle: function( key ) {
                if( this[ key ] ) {
                    this[ key ].destroy( true );
                    this[ key ] = null;
                }
            },

            destroyAll: function() {
                this.destroySingle( 'instance' );
                this.destroySingle( 'tooltip' );

                $( this.attr( 'selector' ) ).removeAttr( 'contenteditable' );
            },

            setWrappersHeight: function() {
                $( '.cke_widget_wrapper' ).each( function( index, element ) {
                    var $content = $( element ).find( '.swc_asset,.swc_text,.swc_grid_row,.swc_grid_column' );

                    if( $content[0].style.width === '100%' ) {
                        $( element ).css( 'height', $content.css( 'height' ) );
                    } else {
                        $( element ).css( 'height', '' );
                    }
                } );
            },

            changeAssetLoaction: function( $assetWrapper, $placeholder ) {
                var self = this;
                var editor = self.instance;
                var widgetsRepo = editor.widgets;
                var sourceWidget;
                var id = this.getWidgetIdAssetBased( $assetWrapper );

                var nodePlaceholder = new CKEDITOR.dom.element( $placeholder[ 0 ] );

                // Before drag drop we make columns editable.
                $( '.swc_grid_column' ).attr( 'contenteditable', 'true' );

                sourceWidget = widgetsRepo.instances[ id ];
                if ( !sourceWidget ) {
                    return;
                }

                // Using the events (as we should) will fail. So use direct functions instead.
                self.instance.undoManager.save( true );
                self.instance.undoManager.lock( false, true );

                sourceWidget.wrapper.insertBefore( nodePlaceholder );

                nodePlaceholder.remove();

                self.resetSelection();

                // Using the events (as we should) will fail. So use direct functions instead.
                self.instance.undoManager.unlock();
                self.instance.undoManager.update();
                // There currently is a bug where you have to do ctrl-z twice before the drop is undo.
                // This seems to be a bug in CKeditor itself.

                // Before drag drop we make columns editable.
                $( '.swc_grid_column' ).attr( 'contenteditable', 'false' );

                evt( 'add_exec', { 'fn': 'swifty_check_inserts' } );
                setTimeout( function() {
                    evt( 'add_exec', { 'fn': 'swifty_check_inserts' } );
                }, 1000 );

                self.contentModfied();
            },

            replaceIds: function( node ) {
                var escapeRegExp = function( str ) {
                    return str.replace( /([.*+?^=!:${}()|\[\]\/\\])/g, '\\$1' );
                };

                // find ids
                var oldIds = [];

                // get new id, remember earlier generated ids
                var replaceId = function( id ) {
                    if( ! oldIds[ id ] ) {
                        oldIds[ id ] = Date.now() + '_' + parseInt( Math.random() * 99999, 10 );
                    }
                    return oldIds[ id ];
                };

                var elementsInData = node.find( '.cke_widget_element' );
                $.each( elementsInData.$, function( index, element ) {
                    if( element.hasAttribute( 'id' ) ) {
                        var id = element.getAttribute( 'id' );
                        id = id.slice( 1 );
                        element.setAttribute( 'id', 'c' + replaceId( id ) );
                    }
                } );

                elementsInData = node.find( '.swc_custom_cssclose' );
                $.each( elementsInData.$, function( index, element ) {
                    if( element.hasAttribute( 'id' ) ) {
                        var id = element.getAttribute( 'id' );
                        id = id.slice( 9 );
                        element.setAttribute( 'id', 'cssclose_' + replaceId( id ) );
                    }
                } );

                var assetInData = node.find( '.swc_asset_cntnt' );
                $.each( assetInData.$, function( index, element ) {
                    if( element.hasAttribute( 'data-asset_data' ) ) {
                        var dataString = element.getAttribute( 'data-asset_data' );
                        if( dataString ) {
                            var data = $.parseJSON( Utils.atou( dataString ) );
                            if( data ) {
                                if( data[ 'swc_cssid' ] ) {
                                    data[ 'swc_cssid' ] = replaceId( data[ 'swc_cssid' ] );
                                    element.setAttribute( 'data-asset_data', Utils.utoa( JSON.stringify( data ) ) );
                                }
                            }
                        }
                    }
                } );

                var rowInData = node.find( '.swc_grid_row' );
                $.each( rowInData.$, function( index, element ) {
                    if( element.hasAttribute( 'data-grid_data' ) ) {
                        var dataString = element.getAttribute( 'data-grid_data' );
                        if( dataString ) {
                            var data = $.parseJSON( dataString );
                            if( data ) {
                                if( data[ 'cssid' ] ) {
                                    data[ 'cssid' ] = replaceId( data[ 'cssid' ] );
                                    element.setAttribute( 'data-grid_data', JSON.stringify( data ) );
                                }
                            }
                        }
                    }
                } );

                var styleInData = node.find( 'style.swc_custom_css' );
                $.each( styleInData.$, function( index, element ) {
                    if( element.hasAttribute( 'id' ) ) {
                        var id = element.getAttribute( 'id' );
                        id = id.slice( 4 );
                        var newid = replaceId( id );

                        element.setAttribute( 'id', 'css_' + newid );
                        var styleText = element.textContent;
                        styleText = styleText.replace( new RegExp( escapeRegExp( id ), 'g' ), newid );
                        element.textContent = styleText;
                    }
                } );
            }

        },
        events: {
            inserted: function() {
                this.scope.ini( this.element );
            },

            removed: function() {
                this.scope.destroyAll();
            },

            '{window} evt_swc_replace_ids': function( el, ev, opts, dfd ) {
                var self = this;
                if( opts && opts.html ) {

                    var tempDoc = document.implementation.createHTMLDocument( '' ),
                        temp = new CKEDITOR.dom.element( tempDoc.body );

                    // Without this isReadOnly will not work properly.
                    temp.data( 'cke-editable', 1 );

                    temp.appendHtml( opts.html );

                    self.scope.replaceIds( temp );

                    dfd.resolve( temp.getHtml() );
                }
            },

            '{window} evt_swc_create_editor': function( el, ev, opts, dfd ) {
                if( opts && opts.$element ) {
                    dfd.resolve( this.scope.createEditor( opts.$element ) );
                }
            },

            '{window} evt_swc_ckeditor_command': function( el, ev, opts ) {
                var cmd = opts && opts.command ? opts.command : null;

                if ( cmd ) {
                    this.scope.instance.execCommand( cmd );
                }
            },

            '{window} evt_swc_swifty_editor_content_modified': function( /*el, ev, opts*/ ) {
                this.scope.contentModfied();
            },

            '{window} evt_swc_check_can_undo_redo': function( /*el, ev, opts*/ ) {
                if( this.scope.instance && this.scope.instance.undoManager ) {
                    evt(
                        'can_undo_redo',
                        {
                            'can_undo': this.scope.instance.undoManager.undoable(),
                            'can_redo': this.scope.instance.undoManager.redoable()
                        }
                    );
                }
            },

            // Restore a previously saved 'image'/snapshot of the editor.
            // We do a few tricks here with the snapshot stack because we could not find a better way.
            '{window} evt_swc_ckeditor_undo_last_image': function( /*el, ev, opts*/ ) {
                if( this.scope.instance && this.scope.instance.undoManager ) {
                    this.scope.instance.undoManager.restoreImage( this.scope.lastUndoImage );
                    if( this.scope.undoImageStack ) {
                        this.scope.instance.undoManager.snapshots = this.scope.undoImageStack;
                    }
                }
            },

            '{window} evt_swc_swifty_editor_remove_refresh_spinner': function( /*el, ev, opts*/ ) {
                this.scope.removeRefreshSpinner();
            },

            '{window} evt_swc_swifty_editor_set_focus': function( el, ev, opts ) {
                this.scope.setFocus( opts );
            },

            '{window} evt_swc_swifty_editor_unlock_selection': function( el, ev, opts ) {
                this.scope.unlockSelection( opts );
            },

            '{window} evt_swc_swifty_editor_set_cursor': function( el, ev, opts ) {
                this.scope.setCursor( opts );
            },

            '{window} evt_swc_swifty_editor_move_asset': function( el, ev, opts ) {
                this.scope.moveAsset( opts );
            },

            '{window} evt_swc_swifty_editor_move_row': function( el, ev, opts ) {
                this.scope.moveRow( opts );
            },

            '{window} evt_swc_swifty_editor_insert_p_asset': function( el, ev, opts ) {
                this.scope.insertPAsset( opts );
            },

            '{window} evt_swc_swifty_editor_set_asset_in_edit_mode': function( el, ev, opts ) {
                this.scope.setAssetInEditMode( opts );
            },

            '{document} mouseup': function( /*el, ev*/ ) {
                this.scope.documentMouseUp();
            },

            '{window} evt_swc_editor_visibility': function( el, ev, opts, dfd ) {
                this.scope.setEditorVisibilityByAjax( opts.show_it ).then( function( showIt ) {
                    dfd.resolve( showIt );
                } );
            },

            '{window} evt_swc_set_column_range_for_insert': function( el, ev, opts, dfd ) {
                this.scope.setColumnRangeForInsert( opts.$column );
                dfd.resolve();
            },

            '{window} evt_swc_set_paragraph_range_for_insert': function( el, ev, opts, dfd ) {
                this.scope.setParagraphRangeForInsert( opts.$p );
                dfd.resolve();
            },

            // Outcommented this code because table element is moved back to the CKE toolbar.
            // '{window} evt_swc_editor_open_table_properties': function( el, ev, opts ) {
            //     this.scope.openTableProperties( opts );
            // },

            '{window} evt_swc_editor_drag_handlers_hide': function( /*el, ev, opts*/ ) {
                $( '.cke_widget_drag_handler_container' ).hide();
            },

            '{window} evt_swc_swifty_editor_set_wrappers_height': function( /*el, ev, opts*/ ) {
                this.scope.setWrappersHeight();
            },

            '{window} evt_swc_swifty_editor_change_asset_location': function( el, ev, opts ) {
                this.scope.changeAssetLoaction( opts.$assetWrapper, opts.$placeholder );
            },

            '{window} evt_swc_swifty_editor_show_tooltip': function( /*el, ev, opts*/ ) {
                this.scope.showStartUpTooltip();
            },

            '{window} evt_swc_get_list_google_fonts': function( el, ev, opts, dfd ) {
                dfd.resolve( this.scope.googleFonts );
            }
        }
    } );
} );