define( [
    'jquery',
    './ckeditor_helper_setup_event_handlers',
    './ckeditor_helper_cogs',
    './ckeditor_helper_resizeicon',
    './ckeditor_helper_cogicon',
    './ckeditor_helper_plusicon',
    './ckeditor_helper_move_asset',
    './ckeditor_helper_instance_ready_handler',
    './ckeditor_helper_setup_insert',
    './ckeditor_helper_setup_asset_widget'
], function(
    $, setupEventHandlers, cogsIconFunctionality, resizeIconFunctionality, cogIconFunctionality, plusIconFunctionality,
    moveAssetFunctionality, onInstanceReadyHandler, setupInsert, setupAssetWidget
) {
    'use strict';

    return function( comp ) {
        comp.setupEventHandlers=setupEventHandlers;
        comp.addCogsIconFunctionality = cogsIconFunctionality;
        comp.addResizeIconFunctionality = resizeIconFunctionality;
        comp.addCogIconFunctionality = cogIconFunctionality;
        comp.addPlusIconFunctionality = plusIconFunctionality;
        comp.moveAsset = moveAssetFunctionality;
        comp.onInstanceReadyHandler = onInstanceReadyHandler;
        comp.setupInsert = setupInsert;
        comp.setupAssetWidget = setupAssetWidget;
    }

} );