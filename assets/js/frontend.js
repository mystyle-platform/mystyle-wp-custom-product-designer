( function( $ ) {

    var designTags, designTagStatus;
    var designCollections, designCollectionStatus;

    designTags = ( window.designTags || '' );
    designCollections = ( window.designCollections || '' );

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
    
    designCollectionStatus = function( status ) {
        var text, color;

        switch ( status ) {
            case 'removed':
                text = 'DESIGN COLLECTION REMOVED!';
                color = 'red';
                break;
            case 'added':
                text = 'DESIGN COLLECTION SAVED!';
                color = 'forestgreen';
                break;
        }

        $( '.design-collection-status' ).text( text );
        $( '.design-collection-status' ).show();
        $( '.design-collection-status' ).css( 'color', color );

        setTimeout( function() {
            $( '.design-collection-status' ).fadeOut();
        }, 3000 );
    };

    $( window ).ready( function() {
        $( '#ms-edit-title-form' ).hide();

        $( '#ms-edit-title-form-show' ).click( function( e ) {
            e.preventDefault();
            $( '#ms-edit-title-form' ).slideToggle();
        });

        $( '.edit-design-tags input.button' ).hide();
        $( '.edit-design-collections input.button' ).hide();
        
        $( '.design-tag-collection-toggle-menu a' ).click(function(e){
            e.preventDefault() ;
            
            $( '.design-tag-collection-toggle-menu li' ).each(function(i, el){
                $(el).removeClass('selected') ;
            }) ;
            
            $(this).parent().addClass('selected') ;
            
            var menuItem = $(this).attr('href') ;
            
            switch(menuItem) {
                case '#design-tags' :
                    $( '.edit-design-collections' ).fadeOut(400, function(){
                        $( '.edit-design-tags' ).fadeIn() ;
                    }) ;
                    break;
                    
                case '#design-collections' :
                    $( '.edit-design-tags' ).fadeOut(400, function(){
                        $( '.edit-design-collections' ).fadeIn() ;
                    }) ;
                    break;
            }
        })
        
        //Design Tag tokenfield
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
                        autoFocus: false,
                        source: function( request, response ) {
                            $.get( mystyle_wp.ajaxurl, { // eslint-disable-line camelcase
                                'action': 'mystyle_design_tag_search',
                                'tax': 'design_tag',
                                'q': request.term
                            }, function (data) {
                                // Decode HTML entities before responding
                                const decodedData = data.data.map(item => $("<textarea/>").html(item).text());
                                response(decodedData);
                            });
                        },
                        delay: 100
                    }
                });
            });
        
        
        //Design Collections TokenField
        $( '.edit-design-collection-input' )
            .on( 'tokenfield:createtoken', function( e ) {
                var existingTokens = $( this ).tokenfield( 'getTokens' );
                $.each( existingTokens, function( index, token ) {
                    if ( token.value === e.attrs.value ) {
                        e.preventDefault();
                    }
                });
            })
            .on( 'tokenfield:createdtoken', function( e ) {
                var collection, postData;

                if ( ! designCollections.includes( e.attrs.value ) ) {

                    // Save to WP via AJAX.
                    collection = e.attrs.value;

                    postData = {
                        'action': 'mystyle_design_collection_add',
                        'collection': collection,
                        'design_id': designId // eslint-disable-line camelcase
                    };

                    $.post( mystyle_wp.ajaxurl, postData, function( data ) { // eslint-disable-line camelcase
                        designCollectionStatus( 'added' );
                    });
                }
            })
            .on( 'tokenfield:removedtoken', function( e ) {

                // Delete from WP via AJAX.
                var collection = e.attrs.value;

                $.post( mystyle_wp.ajaxurl, { // eslint-disable-line camelcase
                    'action': 'mystyle_design_collection_remove',
                    'collection': collection,
                    'design_id': designId // eslint-disable-line camelcase
                }, function( data ) {
                    designCollectionStatus( 'removed' );
                });
            })
            .each( function() {
                if ( ! $().tokenfield ) {
                    return;
                }

                $( this ).tokenfield({
                    delimiter: ',',
                    tokens: designCollections,
                    autocomplete: {
                        autoFocus: false,
                        source: function( request, response ) {
                            $.get( mystyle_wp.ajaxurl, { // eslint-disable-line camelcase
                                'action': 'mystyle_design_collection_search',
                                'tax': 'design_collection',
                                'q': request.term
                            }, function (data) {
                                // Decode HTML entities before responding
                                const decodedData = data.data.map(item => $("<textarea/>").html(item).text());
                                response(decodedData);
                            });
                        },
                        delay: 100
                    }
                });
            });
        
        
        //Change deign permissions
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
        
        //sorting form select
        $('.mystyle-sort-select').on('change', function() {
            var parentForm = $(this).closest("form") ;
            if (parentForm && parentForm.length > 0) {
                console.log(parentForm.attr('action')) ;
                parentForm.submit() ;
            }   
        });
        

    });

} ( jQuery ) );
