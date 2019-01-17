/**
 * QUnit tests for the plugin's admin options page.
 *
 * @package MyStyle
 * @since 0.1.0
 */


/**
 * Assert that the page was rendered (by checking for the header).
 */
QUnit.test(
	'Test that the page was rendered', function (assert ) {
		var header = $( '#wpbody-content .wrap h2' ).html();
		assert.equal( header, 'MyStyle Settings', 'Settings page rendered' );
	}
);

/**
 * Assert that the help is rendered.
 */
QUnit.test(
	'Test that the help was rendered', function (assert ) {
		var header = $( '#tab-panel-mystyle_overview h1' ).html();
		assert.equal( header, 'MyStyle Custom Product Designer Help', 'Help rendered' );
	}
);
