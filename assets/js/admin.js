/**
 * Toggles a panel so that its contents can be seen or hidden.
 *
 * @param {integer} id The id of the panel being toggled.
 * @returns {Boolean} Returns false;
 */
function mystyleTogglePanelVis( id ) {
	var panelObj  = document.getElementById( 'mystyle-panel-' + id );
	var toggleObj = document.getElementById( 'mystyle-toggle-handle-' + id );

	if ( 'none' == panelObj.style.display ) { // Open the data.
		panelObj.style.display = 'block';
		toggleObj.className   += ' mystyle-closed';
	} else { // Close the data.
		panelObj.style.display = 'none';
		toggleObj.className    = toggleObj.className.replace( /(?:^|\s)mystyle\-closed(?!\S)/g, '' );
	}

	return false;
}


(function($){
    
    $(window).ready(function(){
        var appid = mystyle_api.app_key ;
        var appSecret = mystyle_api.app_secret ;
        var action = 'design' ;
        var method = 'get';
        var datatype = action + '_id';
        var jsonobj = "{'design_id':[34535]}";
        const methodtype = 'get';

        var ts = Math.floor(new Date().getTime() / 1000);

        /*end data for hash*/
        var hashstring = action + methodtype + appid + jsonobj + ts;
        /*var signature = base64_encode(hash_hmac('sha1', Hash, appSecret, true));*/

        var hash = CryptoJS.HmacSHA256(hashstring, appSecret);
        var signature = CryptoJS.enc.Base64.stringify(hash);

        var url = 'https://api.ogmystyle.com?action=' + action + '&method=' + method + '&app_id=' + appid + '&data=' + jsonobj + '&ts=' + ts + '&sig=' + signature;
        
        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                $('.license-status span').removeClass('spinner is-active') ;
                
                if(typeof response.error !== 'string') {
                    $('.license-status span').addClass('dashicons dashicons-yes') ;
                }
                else {
                    $('.license-status span').html('<a href="/wp-admin/admin.php?page=mystyle_settings" title="Check License Status">Check License Status</a>') ;
                }
                
            }
        }) ;
        
    }) ;
    
    
})(jQuery) ;
