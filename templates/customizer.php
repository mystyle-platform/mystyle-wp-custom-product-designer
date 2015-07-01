<?php
/**
 * The template for displaying the MyStyle customizer.
 * @package MyStyle
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
<div id="customizer-wrapper"></div>
<script type="text/javascript">
    var testFlash = swfobject.getFlashPlayerVersion();
    var isMobileBrowser = false;
    var forceMobile = <?php echo $force_mobile; ?>;
    if (testFlash && testFlash.hasOwnProperty('major') && testFlash.major == 0) {
        isMobileBrowser = true;
    }
    var elem = document.getElementById('customizer-wrapper');
    var iframeCustomizer = '';

    if (!isMobileBrowser && forceMobile) {
        isMobileBrowser = true;
    }

    if (!isMobileBrowser) {
        iframeCustomizer = '<iframe' +
          ' id="customizer-iframe"' +
          ' frameborder="0"' +
          ' hspace="0"' +
          ' vspace="0"' +
          ' scrolling="no"' +
          ' src="<?php echo $customizer_url ?>"' +
          ' width="950"' +
          ' height="550"></iframe>';
    } else {
        iframeCustomizer = '<iframe' +
          ' id="customizer-iframe"' +
          ' frameborder="0"' +
          ' hspace="0"' +
          ' vspace="0"' +
          ' scrolling="no"' +
          ' src="<?php echo $mobile_customizer_url; ?>"' +
          ' width="100%"' +
          ' height="100%"></iframe>';
    }
    elem.innerHTML = iframeCustomizer;
</script>

