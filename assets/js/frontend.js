(function($){
    
    $(window).ready(function(){
       $('#ms-edit-title-form').hide() ;
        
        
        $('#ms-edit-title-form-show').click(function(e){
            e.preventDefault() ;
            
            $('#ms-edit-title-form').slideToggle() ;
        })
        
        $('.edit-design-tags input.button').hide() ;
        
        $('.edit-design-tag-input').on('tokenfield:createdtoken', function (e) {
            if(!designTags.includes(e.attrs.value)) {
                //save to wp via ajax
                var tag = e.attrs.value ;
                
            }
        })
        .on('tokenfield:removedtoken', function (e) {
            //delete from wp via ajax
            var tag = e.attrs.value ;
            
        })
        .tokenfield({
            delimiter: ',',
            tokens: designTags,
            autocomplete :{
                source: function(request, response)
                {
                    $.get(se_ajax_url, {
                        action: 'ajax-tag-search',
                        tax: 'post_tag',
                        q : request.term
                    }, function(data){
                        data = { data } ;
                        response(data);
                    });
                },
                delay: 100
            }
        }) ;
        
    }) ;
    
})(jQuery) ;
