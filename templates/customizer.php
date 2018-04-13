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
    // Code for fullscreen functionality
    
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
<script type="text/javascript">
    // Code for viewport rewriting
    
    var disableViewportRewrite = <?php echo ($disable_viewport_rewrite) ? 'true' : 'false'; ?>;
    
    /**
     * Rewrites the viewport meta tag for proper scaling of the MyStyle
     * Customizer.
     */
    var rewriteViewport = function() {
        if (disableViewportRewrite) {
            console.log('MyStyle: Viewport rewrite disabled.');
            return;
        }
        console.log('MyStyle: Rewriting the viewport');
        jQuery('meta[name="viewport"]').remove();
        jQuery('head').append('<meta name="viewport" content="maximum-scale=1.0" />');
    }
    
    
    // ON READY
    jQuery(window).ready(function() {
        rewriteViewport();
    });
    
    // ON RESIZE
    jQuery(window).resize(function() {
        rewriteViewport();
    });
</script>
<div id="customizer-wrapper"></div>
<div class="customizer-under-app-wrapper">
    <a onclick="onClickFullScreen();" id="customizer-fullscreen-button" class="customizer-fullscreen-button button">
        <span class="dashicons dashicons-editor-expand"></span>
        <label>Full Screen</label>
    </a>
</div>
<script type="text/javascript">
    // Code for rendering the customizer
    (function () {
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
        }
        elem.innerHTML = iframeCustomizer;
    }());
    
    
</script>

