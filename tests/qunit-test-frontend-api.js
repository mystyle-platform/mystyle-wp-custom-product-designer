/**
 * QUnit tests for the MyStyle plugin API.
 *
 * Note: that you will need to use the 'mock' api key and secret in order for
 * these to pass.
 *
 * @package MyStyle
 * @since 0.1.0
 */

/**
 * Assert that mystyle was rendered.
 */
QUnit.test(
	'Test mystyle rendered', function (assert ) {
		var mystyleTags = $( '#mystyle' );
		assert.equal( mystyleTags.length, 1, 'Mystyle was rendered' );
	}
);
