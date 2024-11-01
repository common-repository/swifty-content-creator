define( [
    'jquery'
], function(
    $
) {
    'use strict';

    return function( opts ) {
        if( !opts ) {
            return;
        }

        var curWidgetObj = this.getWidget( this.getWidgetIdAssetBased( opts.$asset ) );
        var curWidget = curWidgetObj.wrapper;
        var curTable = this.getTableOrRowOrCell( curWidget, 'table' );
        var items, nrOfItems, curPos, newPos, curCell, nrOfColumns;
        var curGridColumn = this.getGridRowOrColumn( curWidget, 'swc_grid_column' );
        var nearestTableOrGrid = this.getTableOrGrid( curWidget );
        var isMoveHandled = false;

        if( curWidget && opts.move ) {
            switch( opts.move ) {
                case 'up':
                case 'down':
                    if( curTable ) {   // Asset inside a table
                        curWidget[ opts.move === 'up' ? 'insertBefore' : 'insertAfter' ]( curTable );
                    } else {
                        var textAsset = this.getAscendantTextAsset( curWidget );
                        var nodeList = null;

                        if( textAsset ) {   // Widget is inside a text asset.
                            nodeList = this.getRootElements( textAsset );
                        } else {   // Widget is inside a column.
                            nodeList = this.getRootElements( curGridColumn );
                        }

                        var gridRows = this.getGridRows();
                        var curGridRow = this.getGridRowOrColumn( curWidget, 'swc_grid_row' );   // The current row
                        var nrOfRows = gridRows.count();
                        var curRowPos = this.getCurPos( curGridRow, gridRows );

                        items = this.getParagraphAndWidgetElements();   // NodeList of P and widget elements.
                        nrOfItems = nodeList.count();

                        if( nrOfItems > 0 ) {   // One or more first level elements
                            curPos = this.getCurPos( curWidget, nodeList );

                            if( ( opts.move === 'up' && curPos > 0 ) ||
                                ( opts.move === 'down' && curPos < nrOfItems - 1 )
                            ) {
                                newPos = this.getNewPos( opts.move, curPos );
                                var newNode = nodeList.getItem( newPos );

                                // Text assets can't move inside text assets. Here the newNode is the text asset
                                // including the widget wrapper.
                                if( this.isTextAsset( newNode ) && ! this.isTextAsset( curWidget ) ) {
                                    var textNodes = this.getRootElements( newNode );
                                    var nrOfNodes = textNodes.count();

                                    if( opts.move === 'up' ) {
                                        curWidget.insertAfter( textNodes.getItem( nrOfNodes - 1 ) );
                                    } else {
                                        curWidget.insertBefore( textNodes.getItem( 0 ) );
                                    }
                                } else {
                                    curWidget[ opts.move === 'up' ? 'insertBefore' : 'insertAfter' ]( newNode );
                                }
                            } else if(  // Widget is the first or last item.
                                ( opts.move === 'up' && curPos === 0 ) ||
                                ( opts.move === 'down' && curPos === nrOfItems - 1 )
                            ) {
                                if( textAsset ) {
                                    // Text asset without the widget wrapper, so we need to get the parent.
                                    curWidget[ opts.move === 'up' ? 'insertBefore' : 'insertAfter' ]( textAsset.getParent() );
                                } else {   // Check if the widget can be moved to another row.
                                    var newRowIndex = null;

                                    // Widget is not in the first row and can be moved to the row above the current row.
                                    if( opts.move === 'up' && curRowPos > 0 ) {
                                        newRowIndex = curRowPos - 1;
                                    }

                                    // Widget is not in the last row and can be moved to the row below the current row.
                                    if( opts.move === 'down' && curRowPos < nrOfRows - 1 ) {
                                        newRowIndex = curRowPos + 1;
                                    }

                                    // Widget can be moved to another row.
                                    if( newRowIndex !== null ) {
                                        var newRow = gridRows.getItem( newRowIndex );
                                        var gridColumns = this.getGridColumnsFromRow( newRow );
                                        
                                        nrOfColumns = gridColumns.count();

                                        // When move is up, we insert into the last column, otherwise the first column.
                                        var newColumn = gridColumns.getItem(
                                            opts.move === 'up' ? nrOfColumns - 1 : 0
                                        );

                                        if( newColumn ) {
                                            // Widget is added as last element in the column when moving up.
                                            if( opts.move === 'up' ) {
                                                curWidget.appendTo( newColumn );
                                            } else {   // Widget is added as first element in the column when moving down.
                                                var firstNode = newColumn.getFirst();

                                                if( firstNode ) {
                                                    curWidget.insertBefore( firstNode );
                                                } else {
                                                    curWidget.appendTo( newColumn );
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    break;
                case 'left':
                case 'right':
                    if( curTable && ( curTable.equals( nearestTableOrGrid ) ) ) {   // Asset inside a table
                        curWidget[ opts.move === 'left' ? 'insertBefore' : 'insertAfter' ]( curTable );

                        isMoveHandled = true;
                    } else if( curGridColumn && ( curGridColumn.equals( nearestTableOrGrid ) ) ) {
                        // asset lives in grid, we are moving to another column
                        var curRow = this.getGridRowOrColumn( curWidget, 'swc_grid_row' );   // The current row
                        var columns = curRow.find( 'div.swc_grid_column' );   // Is a nodeList of columns in row
                        
                        nrOfColumns = columns.count();

                        curCell = this.getGridRowOrColumn( curWidget, 'swc_grid_column' );
                        curPos = this.getCurPos( curCell, columns );

                        if( nrOfColumns > 1 ) {   // More than 1 grid column
                            if( ( opts.move === 'left' && curPos > 0 ) ||
                                ( opts.move === 'right' && curPos < nrOfColumns - 1 )
                            ) {
                                this.moveToNewColum(
                                    opts.move,
                                    curWidget,
                                    this.getNewPos( opts.move, curPos ),
                                    columns
                                );
                            }
                        }
                        isMoveHandled = true;
                    }

                    if( ! isMoveHandled ) {   // Asset inside a paragraph
                        items = this.getParagraphAndWidgetElements();   // NodeList of P and widget elements.
                        nrOfItems = items.count();
                        curPos = this.getCurPos( curWidget, items );

                        var floating = curWidget.getFirst().getComputedStyle( 'float' );

                        if( opts.move === 'left' ) {
                            if( this.isWidgetElement( items.getItem( curPos - 1 ) ) ) {
                                if( floating === 'right' ) {
                                    this.triggerSetPositionEvent( curWidget.getChild( 0 ).$, opts.move );
                                } else {
                                    curWidget.insertBefore( items.getItem( curPos - 1 ) );
                                }
                            } else {
                                this.triggerSetPositionEvent( curWidget.getChild( 0 ).$, opts.move );
                            }
                        }

                        if( opts.move === 'right' ) {
                            if( this.isWidgetElement( items.getItem( curPos + 1 ) ) ) {
                                curWidget.insertAfter( items.getItem( curPos + 1 ) );
                            } else {
                                this.triggerSetPositionEvent( curWidget.getChild( 0 ).$, opts.move );
                            }
                        }
                    }

                    break;
                default:
                    return;
            }
            curWidget.scrollIntoView();

            this.contentModfied();
        }
    }

} );