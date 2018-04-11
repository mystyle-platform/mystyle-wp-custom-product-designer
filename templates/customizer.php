<?php
/**
 * The template for displaying the MyStyle customizer.
 * 
 * NOTE: THIS FILE IS NOT YET THEMEABLE.
 * 
 * @package MyStyle
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
<script type="text/javascript">
    
    function logScreenDebugToConsole(){
	var winW = jQuery(window).width();
	var winH = jQuery(window).height();
	var docW = jQuery(document).width();
	var docH = jQuery(document).height();
	var screenW = screen.width;
	var screenH = screen.height;
	//console.log("[mystyle webpage] >> SCREEN w/h: " + screenW + " / " + screenH + " DOC: " + " doc w/h: " + docW + " / " + docH);
	//console.log("..screen size... win w/h: " + winW + " / " + winH);
	//console.log("..screen size... doc w/h: " + docW + " / " + docH);
    }
    
    /**
     * Removes the viewport meta tag.
     */
    function removeViewportMeta(){
        var vp = jQuery('#viewport');
        if( vp.length  ) vp.remove();
    }

    /**
     * Creates a new viewport meta tag, removing the old if there is one
     * @param {type} $newPageWidth
     * @param {type} $doScaleZoom
     * @param {type} $newScale
     * @returns {undefined}
     */
    function createViewportMetaTag($newPageWidth, $doScaleZoom, $newScale){
        removeViewportMeta();// remove old viewport
        // create new viewport
        var newViewportContent = 'width='+$newPageWidth ;
        if($doScaleZoom) newViewportContent += ',maximum-scale='+$newScale+',user-scalable=no';
        jQuery('head').append( "<meta name='viewport' id='viewport' />" );
        var newViewPort = jQuery('#viewport');
            newViewPort.attr('content',newViewportContent);
    }

    /**
     * resets the viewport tag to match the device with zoom 1
     * @returns {undefined}
     */
    function resetViewportToDevice(){
        removeViewportMeta();
        jQuery('head').append( "<meta name='viewport' id='viewport' content='width=device-width,initial-scale=1'/>" );
    }

    /**
     * 
     * @type Arguments
     */
    function autoAdjustViewport(){

            console.log('[autoAdjustViewport]');
            setViewportForMyStyleCustomizers();

    }

    var lastMyStyleViewportZoom = 0;

    /**
     * Zoom the viewport to fit our minimum 500px canvas width
     * @returns {undefined}
     */
    function setViewportForMyStyleCustomizers() {

        // MAIN APP SIZE REQUIREMENTS TO ZOOM FOR
        //... this includes app size and header, footer, logos, margins, etc.
        var landscapeMinH	= 530; // incl +15px margins for draggins
        var landscapeMinW	= 1030;
        var portraitMinW	= 550;
        var portraitMinH	= 1030;

        // find device / window and document / page sizes
        var screenH = screen.height; // does not zoom / change
        var screenW = screen.width; // does not zoom / change
        var winW	= jQuery(window).width();// result size after zoom
        var winH	= jQuery(window).height();// result size after zoom
        var docW	= jQuery(document).width();// result size after zoom
        var docH	= jQuery(document).height(); // result size after zoom

        // orientation
        var deviceAspectRatio		= screenW / screenH;
        var pageAspectRatio			= docW / docH;
        var aspectName				= screenW > screenH ? 'landscape' : 'portrait';
        var deviceIsPortrait		= deviceAspectRatio < 1; //screenW < screenH;
        var deviceIsLandscape		= !deviceIsPortrait;
        var pageIsPortrait			= (docW < docH);
        var customizerLayoutPortrait= deviceIsPortrait;// portrait
        var deviceIsLandscape		= !customizerLayoutPortrait;
        var deviceOrientationName	= (deviceIsPortrait === true		 ? "PORTRAIT" : "LANDSCAPE");
        var customizerLayoutName	= (customizerLayoutPortrait === true ? "PORTRAIT" : "LANDSCAPE");

        //console.log('[webpage] DEVICE ASPECT RATIO: ' + deviceAspectRatio);

        // establish app size reqs for current orientation
        var appMinW;
        var appMinH;
        if( customizerLayoutPortrait ){ // 1 COLUMN, 2 ROW
                //console.log('[mystyle webpage] PORTRAIT LAYOUT (' + customizerLayoutName + ')');
                appMinW = portraitMinW;
                appMinH = portraitMinH;
        } else { // LANDSCAPE 2 COLUMN, 1 ROW
                //console.log('[mystyle webpage] LANDSCAPE LAYOUT (' + customizerLayoutName + ')');
                appMinW = landscapeMinW;// default w for landscape
                appMinH = landscapeMinH;
        }

        var zoomFitW = screenW / appMinW;
        var zoomFitH = screenH / appMinH;

        //var newZoomScale = Math.min(1, Math.min(zoomFitH, zoomFitW));
        var newZoomScale = Math.min(zoomFitH, zoomFitW);

        //console.log('last viewport zoom: ' + lastMyStyleViewportZoom);
        logScreenDebugToConsole();
        //console.log('[mystyle webpage] newZoomScale: ' + newZoomScale + ' (' + customizerLayoutName + ')');

        if (lastMyStyleViewportZoom && lastMyStyleViewportZoom === newZoomScale) {
            //console.log('[mystyle webpage] already at correct viewport zoom, not setting viewport.');
        } else { // do zoom.

            var newViewportContent = (newZoomScale == 1 ? 'width=device-width,' : '') +  'initial-scale=' + newZoomScale;
            var viewportElem = document.getElementById("viewport");
            if (viewportElem) {
                viewportElem.setAttribute("content", newViewportContent);
                lastMyStyleViewportZoom = newZoomScale;
            } else {
                //console.log('[mystyle webpage] could not target viewport tag: ' + viewportElem);
            }
            if (newZoomScale === 1) {
                jQuery('body').removeClass('mystyle-zoom-scale');
                jQuery('body').addClass('mystyle-at-scale');
            } else {
                jQuery('body').removeClass('mystyle-at-scale');
                jQuery('body').addClass('mystyle-zoom-scale');
            }
            //console.log('[mystyle webpage] size after zoom:');
        }
    }
    
    // ON RESIZE
    jQuery(window).resize(function() {
        if(typeof(useMobileCustomizer) !== 'undefined'
        && useMobileCustomizer == true ) {
            setTimeout( autoAdjustViewport, 1000 ); // update viewport
        }
    });
</script>

<script type="text/javascript">
    var fullscreen = false;
    
    function toggleFullScreenElement(_el) {
        var doc = window.document;
        var docEl = _el;
        var requestFullScreen = docEl.requestFullscreen || docEl.mozRequestFullScreen || docEl.webkitRequestFullScreen || docEl.msRequestFullscreen;
        var cancelFullScreen = doc.exitFullscreen || doc.mozCancelFullScreen || doc.webkitExitFullscreen || doc.msExitFullscreen;
        if(!doc.fullscreenElement && !doc.mozFullScreenElement && !doc.webkitFullscreenElement && !doc.msFullscreenElement) {
            requestFullScreen.call(docEl);
        }
        else {
            cancelFullScreen.call(doc);
        }
    }
    
    var onClickFullScreen = function() {
        if(!fullscreen) { //enable full screen mode
            jQuery('#customizer-iframe').addClass('mystyle-fullscreen');
            jQuery('#customizer-iframe').parents().addClass('mystyle-fullscreen');
            jQuery(':not(.mystyle-fullscreen)').addClass('mystyle-fullscreen-hidden');
            var closeButton = jQuery('<a id="customizer-close-button" onclick="onClickFullScreen();" class="button"><span class="dashicons dashicons-no"></span></a>');
            jQuery('#customizer-wrapper').append(closeButton);
            
            //browser fullscreen
            toggleFullScreenElement(jQuery('#customizer-wrapper')[0]);
            
            fullscreen = true;
        } else { //disable full screen mode.
            jQuery('#customizer-iframe').removeClass('mystyle-fullscreen');
            jQuery('#customizer-iframe').parents().removeClass('mystyle-fullscreen');
            jQuery('.mystyle-fullscreen-hidden').removeClass('mystyle-fullscreen-hidden');
            jQuery('#customizer-close-button').remove();
            
            //browser fullscreen
            toggleFullScreenElement(jQuery('#customizer-wrapper')[0]);
            
            fullscreen = false;
        }
        
        return true;
    }
</script>
<div id="customizer-wrapper"></div>
<div class="customizer-under-app-wrapper">
    <a onclick="onClickFullScreen();" id="customizer-fullscreen-button" class="customizer-fullscreen-button button">
        <span class="dashicons dashicons-editor-expand"></span>
        <label>Full Screen</label>
    </a>
</div>
<script type="text/javascript">
    var useMobileCustomizer = true;
    
    (function () {
        console.log('loading...');
        //Does the browser support Flash?
        var testFlash = swfobject.getFlashPlayerVersion();
        var flashSupported = false;
        if ( testFlash && testFlash.hasOwnProperty( 'major' ) && testFlash.major > 0 ) {
            flashSupported = true;
        }

        //Do we want Flash?
        var enableFlash = <?php echo $enable_flash; ?>;

        //Show Flash customizer?
        var showFlashCustomizer = false;
        if ( flashSupported && enableFlash ) {
            showFlashCustomizer = true;
        }

        var elem = document.getElementById( 'customizer-wrapper' );
        var iframeCustomizer = '';

        if ( showFlashCustomizer ) {
            useMobileCustomizer = false;
            iframeCustomizer = '<iframe' +
              ' id="customizer-iframe"' +
              ' frameborder="0"' +
              ' hspace="0"' +
              ' vspace="0"' +
              ' scrolling="no"' +
              ' src="<?php echo $flash_customizer_url ?>"' +
              ' width="950"' +
              ' height="550"></iframe>';
        } else {
            iframeCustomizer = '<iframe' +
              ' id="customizer-iframe"' +
              ' frameborder="0"' +
              ' hspace="0"' +
              ' vspace="0"' +
              ' scrolling="no"' +
              ' src="<?php echo $html5_customizer_url; ?>"' +
              ' width="100%"' +
              ' height="100%"></iframe>';
            setTimeout( autoAdjustViewport, 1000 );
        }
        elem.innerHTML = iframeCustomizer;
    }());
    
    
</script>

