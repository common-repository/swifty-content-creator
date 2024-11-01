define( [
    'jquery',
    './ckeditor_helper_asset',
    './ckeditor_helper_image',
    './ckeditor_helper_row',
    './ckeditor_helper_column',
    './ckeditor_helper_text'
], function(
    $, assetFunctionality, imageFunctionality, rowFunctionality, columnFunctionality, textFunctionality
) {
    'use strict';

    return function( component ) {
        assetFunctionality( component );
        imageFunctionality( component );
        rowFunctionality( component );
        columnFunctionality( component );
        textFunctionality( component );
    };

} );