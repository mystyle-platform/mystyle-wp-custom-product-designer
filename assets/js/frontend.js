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
            .on( 'tokenfield:createtoken', function( e ) {
                var existingTokens = $( this ).tokenfield( 'getTokens' );
                $.each( existingTokens, function( index, token ) {
                    if ( token.value === e.attrs.value ) {
e.preventDefault();
}
                });
            })
            .on( 'tokenfield:createdtoken', function( e ) {
                var tag, postData;

                if ( ! designTags.includes( e.attrs.value ) ) {

                    // Save to WP via AJAX.
                    tag = e.attrs.value;

                    postData = {
                        'action': 'mystyle_design_tag_add',
                        'tag': tag,
                        'design_id': designId // eslint-disable-line camelcase
                    };

                    $.post( mystyle_wp.ajaxurl, postData, function( data ) { // eslint-disable-line camelcase
                        designTagStatus( 'added' );
                    });
                }
            })
            .on( 'tokenfield:removedtoken', function( e ) {

                // Delete from WP via AJAX.
                var tag = e.attrs.value;

                $.post( mystyle_wp.ajaxurl, { // eslint-disable-line camelcase
                    'action': 'mystyle_design_tag_remove',
                    'tag': tag,
                    'design_id': designId // eslint-disable-line camelcase
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
                        autoFocus: true,
                        source: function( request, response ) {
                            $.get( mystyle_wp.ajaxurl, { // eslint-disable-line camelcase
                                'action': 'mystyle_design_tag_search',
                                'tax': 'design_tag',
                                'q': request.term
                            }, function( data ) {
                                response( data.data );
                            });
                        },
                        delay: 100
                    }
                });
            });

        $( '.form-change-design-access select' ).change( function( e ) {
            var form, accessId, designId, nonce;

            e.preventDefault();

            form = $( this[0].form );
            accessId = $( this ).val();
            designId = form.find( 'input[name=design_id]' ).val();
            nonce = form.find( 'input[name=nonce]' ).val();

            $.ajax({
                type: 'post',
                dataType: 'text',
                url: mystyle_wp.ajaxurl, // eslint-disable-line camelcase
                data: {
                    'action': 'mystyle_design_access_change',
                    'design_id': designId,
                    'access_id': accessId,
                    '_ajax_nonce': nonce
                },
                success: function( response ) {
                    $( '#_mystyle_design_access_' + designId ).after(
                        '<span' +
                            ' style="width:30px;height:30px;font-size:30px;color:green"' +
                            ' class="dashicons dashicons-yes">' +
                        '</span>'
                        );

                    setTimeout( function() {
                        $( '.form-change-design-access .dashicons-yes' ).fadeOut();
                    }, 1000 );
                }
            });

        });

    });

} ( jQuery ) );
