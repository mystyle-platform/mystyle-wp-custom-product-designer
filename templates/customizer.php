<?php
/**
 * The template for displaying the MyStyle customizer.
 *
 * NOTE: THIS FILE IS NOT YET THEMEABLE.
 *
 * @package MyStyle
 * @since 1.1.0
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
?>

<script type="text/javascript">
    // Code for fullscreen functionality

    var fullscreen = false;

    var onClickFullScreen = function () {
        if (!fullscreen) { //enable full screen mode
            console.log('enabling full screen mode');
            jQuery('#customizer-iframe').addClass('mystyle-fullscreen');
            jQuery('#customizer-iframe').parents().addClass('mystyle-fullscreen');
            jQuery(':not(.mystyle-fullscreen)').addClass('mystyle-fullscreen-hidden');
            var closeButton = jQuery('<a id="customizer-close-button" onclick="onClickFullScreen();" class="button"><span class="dashicons dashicons-no"></span></a>');
            jQuery('#customizer-wrapper').append(closeButton);

            fullscreen = true;
        } else { //disable full screen mode.
            console.log('disabling full screen mode');
            jQuery('#customizer-iframe').removeClass('mystyle-fullscreen');
            jQuery('#customizer-iframe').parents().removeClass('mystyle-fullscreen');
            jQuery('.mystyle-fullscreen-hidden').removeClass('mystyle-fullscreen-hidden');
            jQuery('#customizer-close-button').remove();

            fullscreen = false;
        }

        return true;
    };
</script>
<script type="text/javascript">
    // Code for viewport rewriting

    var disableViewportRewrite = <?php echo ($disable_viewport_rewrite) ? 'true' : 'false'; ?>;

    /**
     * Calculates the ideal orientation for the app (either "portrait" or
     * "landscape").
     * @returns {string} Returns the ideal orientation for the app ("portait" or
     * "landscape").
     */
    var calculateOrientation = function () {
        var orientation = 'landscape';
        var winWidth = jQuery(window).width();
        var winHeight = jQuery(window).height();

        if ((winHeight > winWidth)) {
            orientation = 'portrait';
        }

        //console.log(winWidth + ':' + winHeight + ' (' + orientation + ')');

        return orientation;
    };

    /**
     * Sets the orientation of the iframe and rewrites the viewport meta tag.
     * This is done to ensure proper scaling and orientation of the MyStyle
     * Customizer.
     */
    var setOrientation = function () {
        var orientation = calculateOrientation();

        if (disableViewportRewrite) {
            return;
        }

        var viewportSettings;
        if (orientation === 'landscape') { //landscape

            var scale = jQuery('body').width() / 1000;

            viewportSettings = 'initial-scale=' + scale + ', maximum-scale=' + scale;

        } else { //portrait
            var scale = jQuery('body').width() / 550;

            viewportSettings = 'initial-scale=' + scale + ', maximum-scale=' + scale;
        }

        //set the viewport
        jQuery('meta[name="viewport"]').attr('content', viewportSettings);

    };

    // ON READY
    jQuery(window).ready(function () {
        setOrientation('on ready');
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
        if (testFlash && testFlash.hasOwnProperty('major') && testFlash.major > 0) {
            flashSupported = true;
        }

        //Do we want Flash?
        var enableFlash = <?php echo ( $enable_flash ) ? 'true' : 'false'; ?>;

        //Show Flash customizer?
        var showFlashCustomizer = false;
        if (flashSupported && enableFlash) {
            showFlashCustomizer = true;
        }

        var elem = document.getElementById('customizer-wrapper');
        var iframeCustomizer = '';

        if (showFlashCustomizer) {
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


