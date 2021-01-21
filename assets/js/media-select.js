( function( $ ) {
	var mystyleBGMediaLibrary, mystyleFGMediaLibrary;

	// If there is no media on the page, just return.
	if (
		( 'undefined' === typeof window.wp ) ||
		( 'undefined' === typeof window.wp.media )
	) {
		return;
	}

    mystyleBGMediaLibrary = window.wp.media({

        // Accepts [ 'select', 'post', 'image', 'audio', 'video' ]
        // Determines what kind of library should be rendered.
        frame: 'select',

        // Modal title.
        title: 'Select a Custom Template Background Image',

        // Enable/disable multiple select.
        multiple: false,

        // Library wordpress query arguments.
        library: {
            order: 'DESC',

            // [ 'name', 'author', 'date', 'title', 'modified', 'uploadedTo', 'id', 'post__in', 'menuOrder' ]
            orderby: 'date',

            // mime type. e.g. 'image', 'image/jpeg'
            type: 'image',

            // Searches the attachment title.
            search: null,

            // Includes media only uploaded to the specified post (ID)
            uploadedTo: null // wp.media.view.settings.post.id (for current post ID)
        },

        button: {
            text: 'Done'
        }

    });

    mystyleFGMediaLibrary = window.wp.media({

        // Accepts [ 'select', 'post', 'image', 'audio', 'video' ]
        // Determines what kind of library should be rendered.
        frame: 'select',

        // Modal title.
        title: 'Select a Custom Template Foreground Image',

        // Enable/disable multiple select
        multiple: false,

        // Library wordpress query arguments.
        library: {
            order: 'DESC',

            // [ 'name', 'author', 'date', 'title', 'modified', 'uploadedTo', 'id', 'post__in', 'menuOrder' ]
            orderby: 'date',

            // mime type. e.g. 'image', 'image/jpeg'
            type: 'image',

            // Searches the attachment title.
            search: null,

            // Includes media only uploaded to the specified post (ID)
            uploadedTo: null // wp.media.view.settings.post.id (for current post ID)
        },

        button: {
            text: 'Done'
        }

    });

    $( '#_mystyle_custom_template_bgimg_button' ).on( 'click', function( e ) {
        e.preventDefault();
        mystyleBGMediaLibrary.open();
    });

    $( '#_mystyle_custom_template_fgimg_button' ).on( 'click', function( e ) {
        e.preventDefault();
        mystyleFGMediaLibrary.open();
    });

    mystyleBGMediaLibrary.on( 'select', function() {

        var selectedImages = mystyleBGMediaLibrary.state().get( 'selection' );

        $( '#_mystyle_custom_template_bgimg' ).val( selectedImages.first().toJSON().url );

    });


    mystyleFGMediaLibrary.on( 'select', function() {

        var selectedImages = mystyleFGMediaLibrary.state().get( 'selection' );

        $( '#_mystyle_custom_template_fgimg' ).val( selectedImages.first().toJSON().url );

    });

} ( jQuery ) );
