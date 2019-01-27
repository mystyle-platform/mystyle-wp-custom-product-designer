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
	exit; // Exit if accessed directly.
}
?>

<div class="above-customizer-wrapper">
	<a onclick="MyStyleCustomize.toggleFullscreen();" id="customizer-fullscreen-button" class="customizer-fullscreen-button button">
		<span class="dashicons dashicons-editor-expand"></span>
		<label>Full Screen</label>
	</a>
</div>

<div id="customizer-wrapper">

</div>
<div class="customizer-under-app-wrapper">
	<!-- under customizer app -->
</div>

<script type="text/javascript">

	// On ready.
	jQuery( window ).ready(
		function () {
			MyStyleCustomize.init({
				"disableViewportRewrite": <?php echo ( $disable_viewport_rewrite ) ? 'true' : 'false'; ?>,
				"enableFlash": <?php echo ( $enable_flash ) ? 'true' : 'false'; ?>,
				"flashCustomizerUrl": "<?php echo esc_attr( $flash_customizer_url ); ?>",
				"html5CustomizerUrl": "<?php echo esc_attr( $html5_customizer_url ); ?>",
			});
			MyStyleCustomize.setOrientation();
			MyStyleCustomize.renderCustomizer();
		}
	);

	// On resize.
	jQuery( window ).resize(
		function () {
			MyStyleCustomize.setOrientation();
		}
	);

</script>
