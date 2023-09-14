<?php
/**
 * The template for displaying a MyStyle Design.
 *
 * NOTE: THIS FILE IS NOT YET THEMEABLE.
 *
 * @package MyStyle
 * @since 3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<p>
	<img class="mystyle-centered" src="<?php echo esc_attr( $design->mystyle_design_Url() ); ?>"/>
</p>
<?php if ( null !== $design->get_print_url() ) { ?>
	<ul class="mystyle-button-group">
		<li>
			<a target="_blank" href="<?php echo esc_attr( $design->get_print_url() ); ?>" class="button">
				Print
			</a>
		</li>
	</ul>
<?php } else { ?>
	<ul class="mystyle-button-group">
		<li>
			<a
				onclick="
							jQuery( '#mystyle-renderer-wrapper-<?php echo esc_attr( $design->get_design_id() ); ?>:not(:has(>iframe))')
								.append( '<iframe src=\'<?php echo esc_attr( $renderer_url ); ?>\' width=\'100%\' height=\'300\'></iframe>' );
							return true;
				"
				class="button"
				>
				Render Print File
			</a>
		</li>
	</ul>
	<div id="mystyle-renderer-wrapper-<?php echo esc_attr( $design->get_design_id() ); ?>" class="mystyle-renderer-wrapper"></div>
<?php } ?>

</div>

