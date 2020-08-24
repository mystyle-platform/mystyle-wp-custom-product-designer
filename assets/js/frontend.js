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
                
                var postData = {
                    action: 'design-add-tag',
                    tag: tag,
                    design_id: designId
                }
                
                $.post(design_ajax_url, postData, function(data) {
                    console.log(data) ;
                }) ;
            }
        })
        .on('tokenfield:removedtoken', function (e) {
            //delete from wp via ajax
            var tag = e.attrs.value ;
            
            $.post(design_ajax_url, {
                action: 'design-tag-save',
                tag: tag,
                designId: designId
            }, function(data) {

            }) ;
        })
        .tokenfield({
            delimiter: ',',
            tokens: designTags,
            autocomplete :{
                source: function(request, response)
                {
                    $.get(design_ajax_url, {
                        action: 'ajax-tag-search',
                        tax: 'design_tag',
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
