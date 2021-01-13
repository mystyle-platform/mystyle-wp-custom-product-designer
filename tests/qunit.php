<?php
/**
 * Functions for testing the MyStyle plugin using QUnit.
 *
 * @package MyStyle
 * @since 0.1.0
 */

/**
 * Loads QUnit and the tests for the passed test suite or if no test suite is
 * passed, loads tests if the current screen has tests (based on the screen_id).
 *
 * @param string $test_suite (optional) The test suite that you want to load.
 */
function mystyle_load_qunit( $test_suite ) {
	$supported_screens = array( 'settings_page_mystyle' );

	if ( null === $test_suite ) {
		$screen    = get_current_screen();
		$screen_id = ( ! empty( $screen ) ? $screen->id : null );
		if ( in_array( $screen_id, $supported_screens, true ) ) {
			$test_suite = $screen_id;
		}
	}

	if ( null !== $test_suite ) {
		?>
		<link rel="stylesheet" href="//code.jquery.com/qunit/qunit-1.15.0.css">

		<div id="qunit"></div>
		<div id="qunit-fixture"></div>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>
		<script src="//code.jquery.com/qunit/qunit-1.15.0.js"></script>
		<script src="<?php echo esc_url( plugins_url( 'qunit-test-' . $test_suite . '.js', __FILE__ ) ); ?>"></script>

		<?php if ( is_admin() ) { ?>
			<style>
				#wpfooter {position: relative;}
				#qunit {margin-left: 160px;}
			</style>
		<?php } else { ?>
			<style>
				#qunit {
					position:fixed;
					bottom:0px;
					width: 100%;
				};
			</style>
		<?php } ?>

		<?php
	}
}
