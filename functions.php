<?php
/**
 * Miscellaneous functions used by the plugin.
 *
 * @package MyStyle
 * @since 1.5.0
 */

/**
 * Returns our interface with WooCommerce.
 *
 * @return MyStyle_WC_Interface
 */
function MyStyle_WC() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
	return MyStyle::get_instance()->get_WC();
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *    yourtheme/$template_path/$template_name
 *    yourtheme/$template_name
 *    $default_path/$template_name
 *
 * This function is bsed off of the wc_get_template function.
 *
 * @access public
 * @param string $template_name The name of the template.
 * @param string $template_path (default: '') The path to the template.
 * @param string $default_path (default: '') The default path.
 * @return string
 */
function mystyle_locate_template(
	$template_name,
	$template_path = '',
	$default_path = ''
) {
	if ( ! $template_path ) {
		$template_path = '/mystyle/';
	}

	if ( ! $default_path ) {
		$default_path = MYSTYLE_PATH . '/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template.
	if ( ! $template || MYSTYLE_TEMPLATE_DEBUG_MODE ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'mystyle_locate_template', $template, $template_name, $template_path );
}

/**
 * Get template passing attributes and including the file.
 *
 * This function is based off of the wc_get_template function.
 *
 * @access public
 * @param string $template_name The name of the template.
 * @param array  $args          (default: array()) Any arguments.
 * @param string $template_path (default: '') The path to the template.
 * @param string $default_path  (default: '') The default path.
 * @todo Add unit testing
 */
function mystyle_get_template(
			$template_name,
			$args = array(),
			$template_path = '',
			$default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args ); // @codingStandardsIgnoreLine
	}

	$located = mystyle_locate_template(
		$template_name,
		$template_path,
		$default_path
	);

	if ( ! file_exists( $located ) ) {
		/* translators: %s template */
		_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'mystyle' ), '<code>' . $located . '</code>' ), '2.1' );
		return;
	}

	// Allow 3rd party plugin to filter template file from their plugin.
	$located = apply_filters(
		'mystyle_get_template',
		$located,
		$template_name,
		$args,
		$template_path,
		$default_path
	);

	do_action( 'mystyle_before_template_part', $template_name, $template_path, $located, $args );

	include $located;

	do_action( 'mystyle_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Like mystyle_get_template but returns the HTML instead of outputting.
 *
 * This function is based off of the wc_get_template_html function.
 *
 * @see mystyle_get_template
 * @since 2.1.0
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 * @todo Add unit testing
 */
function mystyle_get_template_html(
	$template_name,
	$args = array(),
	$template_path = '',
	$default_path = '' ) {
	ob_start();
	mystyle_get_template( $template_name, $args, $template_path, $default_path );
	return ob_get_clean();
}
