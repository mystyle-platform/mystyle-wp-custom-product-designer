( function( $ ) {

	var designTags, designTagStatus;

	designTags = ( window.designTags || '' );

    designTagStatus = function( status ) {
		var text, color;

        switch ( status ) {
            case 'removed':
                text = 'DESIGN TAG REMOVED!';
                color = 'red';
                break;
            case 'added':
                text = 'DESIGN TAG SAVED!';
                color = 'forestgreen';
                break;
        }

        $( '.design-tag-status' ).text( text );
        $( '.design-tag-status' ).show();
        $( '.design-tag-status' ).css( 'color', color );

        setTimeout( function() {
            $( '.design-tag-status' ).fadeOut();
        }, 3000 );
    };

    $( window ).ready( function() {
       $( '#ms-edit-title-form' ).hide();


        $( '#ms-edit-title-form-show' ).click( function( e ) {
            e.preventDefault();

            $( '#ms-edit-title-form' ).slideToggle();
        });

        $( '.edit-design-tags input.button' ).hide();

        $( '.edit-design-tag-input' )
			.on( 'tokenfield:createdtoken', function( e ) {
				var tag, postData;

				if ( ! designTags.includes( e.attrs.value ) ) {

					// Save to WP via AJAX.
					tag = e.attrs.value;

					postData = {
						action: 'design_tag_add',
						tag: tag,
						design_id: designId // eslint-disable-line camelcase
					};

					$.post( design_ajax_url, postData, function( data ) {
						designTagStatus( 'added' );
					});
				}
			})
			.on( 'tokenfield:removedtoken', function( e ) {

				// Delete from WP via AJAX.
				var tag = e.attrs.value;

				$.post( design_ajax_url, {
					action: 'design_tag_remove',
					tag: tag,
					design_id: designId // eslint-disable-line camelcase
				}, function( data ) {
					designTagStatus( 'removed' );
				});
			})
			.each( function() {
				if ( ! $().tokenfield ) {
					return;
				}

				$( this ).tokenfield({
					delimiter: ',',
					tokens: designTags,
					autocomplete: {
						source: function( request, response ) {
							$.get( design_ajax_url, {
								action: 'design_tag_search',
								tax: 'design_tag',
								q: request.term
							}, function( data ) {
								response( data );
							});
						},
						delay: 100
					}
				});
			});

    });


} ( jQuery ) );
