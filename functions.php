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
		_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'mystyle' ), '<code>' . $located . '</code>' ), '2.1' ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
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

if ( ! function_exists( 'et_divi_post_meta' ) ) {
	/**
	 * Divi theme modification. If we are on the designpage, don't include the
	 * postinfo_meta (post date, comments, etc).
	 *
	 * @return string Returns the post-meta or an empty string if we are on the
	 * designpage.
	 */
	function et_divi_post_meta() {
		// If we are on the design page, return ''.
		if (
			( false !== get_query_var( 'designpage' ) )
			|| ( '' !== get_query_var( 'designpage' ) )
		) {
			return '';
		}

		// This block is all copy/paste from the Divi theme.
		// phpcs:disable
		$postinfo = is_single() ? et_get_option( 'divi_postinfo2' ) : et_get_option( 'divi_postinfo1' );

		if ( $postinfo ) :
			echo '<p class="post-meta">';
			echo et_pb_postinfo_meta( $postinfo, et_get_option( 'divi_date_format', 'M j, Y' ), esc_html__( '0 comments', 'Divi' ), esc_html__( '1 comment', 'Divi' ), '% ' . esc_html__( 'comments', 'Divi' ) );
			echo '</p>';
		endif;
		// phpcs:enable
	}
}

register_activation_hook(__FILE__, 'myplugin_activate');

add_action('wp', 'schedule_daily_cron');
function schedule_daily_cron()
{
	if ( ! wp_next_scheduled('mystyle_update_credentials_status')) {
		// Set the cron job to run every day at 3:00 AM (adjust the time if needed)
		$timestamp = strtotime('3:00 AM');
		wp_schedule_event($timestamp, 'daily', 'mystyle_update_credentials_status');
	}
}


// Hook for the scheduled cron job
add_action('mystyle_update_credentials_status', 'update_credentials_status_callback');
function update_credentials_status_callback()
{
	$stored_license_status = get_option('mystyle_license_status_');
	$has_valid_credentials = false;
	$design_id = 1; // An arbitrary design id.

	// Set up the API call variables.
	$api_key = MyStyle_Options::get_api_key();
	$secret = MyStyle_Options::get_secret();
	$action = 'design';
	$method = 'get';
	$data = '{"design_id":[' . $design_id . ']}';
	$ts = time();

	$to_hash = $action . $method . $api_key . $data . $ts;
	$sig = base64_encode(hash_hmac('sha256', $to_hash, $secret, true));

	$post_data = array(
		'action' => $action,
		'method' => $method,
		'app_id' => $api_key,
		'data' => $data,
		'sig' => $sig,
		'ts' => $ts,
	);
	include_once(dirname(__FILE__) . '/includes/api/class-mystyle-api.php');
	$api_endpoint_url = 'http://api.ogmystyle.com/';
	$response = wp_remote_post(
		$api_endpoint_url,
		array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => $post_data,
			'cookies' => array(),
		)
	);

	if (!is_wp_error($response)) {
		$response_data = json_decode($response['body'], true);

		if (!isset($response_data['error'])) {
			$has_valid_credentials = true;
		}
	}

	if ($stored_license_status !== ($has_valid_credentials ? 'valid' : 'invalid')) {
		update_option('mystyle_license_status_', $has_valid_credentials ? 'valid' : 'invalid');

		// Store the last update date and time
		update_option('mystyle_last_update_datetime', current_time('mysql'));
	}
	update_option('mystyle_last_license_update', time());
}