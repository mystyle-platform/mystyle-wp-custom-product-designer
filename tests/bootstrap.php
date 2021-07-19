<?php
/**
 * Bootstrap file for testing the plugin.
 *
 * @package MyStyle
 * @since 0.1.0
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

// Mock the wp_mail function into a global variable.
$mail_message = null;

/**
 * Mock the wp_mail function.
 *
 * @global type $mail_message
 * @param string $to Who you want to send the message to.
 * @param string $subject The message subject.
 * @param string $message The message body.
 */
function wp_mail( $to, $subject, $message ) {
	global $mail_message;
	$mail_message = array(
		'to'      => $to,
		'subject' => $subject,
		'message' => $message,
	);
}
/**
 * Manually loads the plugin.
 */
function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../mystyle.php';
	// Instantiate the MyStyle and MyStyle_WC object.
	MyStyle::get_instance()->set_WC( new MyStyle_WC() );
}

tests_add_filter( 'plugins_loaded', '_manually_load_plugin' );

// Bootstrap woocommerce.
$wc_bootstrap = dirname( __FILE__ ) . '/../../woocommerce/tests/legacy/bootstrap.php';
if ( ! file_exists( $wc_bootstrap ) ) {
	$wc_bootstrap = dirname( __FILE__ ) . '/../../woocommerce/tests/bootstrap.php';
}
require $wc_bootstrap;

require_once $_tests_dir . '/includes/bootstrap.php';

require dirname( __FILE__ ) . '/functions.php';
require dirname( __FILE__ ) . '/class-mystyle-test-util.php';
