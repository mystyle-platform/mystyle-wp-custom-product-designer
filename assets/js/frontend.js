(function($){
    
    $(window).ready(function(){
       $('#ms-edit-title-form').hide() ;
        
        
        $('#ms-edit-title-form-show').click(function(e){
            e.preventDefault() ;
            
            $('#ms-edit-title-form').slideToggle() ;
        })
    }) ;
    
})(jQuery) ;
