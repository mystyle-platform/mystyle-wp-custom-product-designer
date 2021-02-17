( function( $ ) {

    // Add Color Picker to the #_mystyle_custom_template_color field.
	if ( $().wpColorPicker ) {
		$( '#_mystyle_custom_template_color' ).wpColorPicker();
		$( '#_mystyle_custom_template_default_text_color' ).wpColorPicker();
	}

} ( jQuery ) );
