<?php
/**
 * Bootstrap file for testing the plugin
 * @package MyStyle
 * @since 0.1.0
 */

$_tests_dir = getenv('WP_TESTS_DIR');
if ( !$_tests_dir ) $_tests_dir = '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

//Mock the wp_mail function into a global variable
$mail_message = null;
function wp_mail($to, $subject, $message) {
    global $mail_message;
    $mail_message = array(
        'to' => $to,
        'subject' => $subject,
        'message' => $message
    );
}

function _manually_load_plugin() {
    require dirname( __FILE__ ) . '/../mystyle.php';
}
tests_add_filter( 'plugins_loaded', '_manually_load_plugin' );

//bootstrap woocommerce
require dirname( __FILE__ ) . '/../../woocommerce/tests/bootstrap.php';

require_once $_tests_dir . '/includes/bootstrap.php';

require dirname( __FILE__ ) . '/functions.php';
require dirname( __FILE__ ) . '/class-mystyle-test-util.php';

