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
<div id="customizer-wrapper"></div>
<script type="text/javascript">
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
</script>

