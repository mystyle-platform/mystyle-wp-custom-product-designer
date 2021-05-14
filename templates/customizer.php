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

	// On ready: load the MyStyle Customizer.
	(MyStyleCustomize=window.MyStyleCustomize||[]).push(function() {
		MyStyleCustomize.init({
			"apiKey": "<?php echo intval( $mystyle_app_id ); ?>",
			"templateId": "<?php echo intval( $mystyle_template_id ); ?>",
			"enableFlash": <?php echo ( ( $enable_flash ) ? 'true' : 'false' ); ?>,
			"disableViewportRewrite": <?php echo ( ( $disable_viewport_rewrite ) ? 'true' : 'false' ); ?>,
			"skipEmail": <?php echo ( ( $skip_email ) ? 'true' : 'false' ); ?>,
			"handoffUrl": "<?php echo esc_url( $redirect_url ); ?>",
			"printType": "<?php echo esc_js( $print_type ); ?>",
			<?php echo ( null !== $design_id ) ? '"designId": "' . intval( $design_id ) . "\",\n" : ''; ?>
			<?php echo ( ! empty( $customizer_ux ) ) ? '"customizerUx": "' . esc_js( $customizer_ux ) . "\",\n" : ''; ?>
			"passthru": [{"fieldName": "h", "fieldValue": "<?php echo esc_js( $passthru ); ?>"}]
		});
		MyStyleCustomize.renderCustomizer('#customizer-wrapper');
	});

	// On resize: set the MyStyle customizer orientation.
	jQuery( window ).resize(function () {
		if (window.MyStyleCustomize) {
			window.MyStyleCustomize.setOrientation();
		};
	});

</script>
