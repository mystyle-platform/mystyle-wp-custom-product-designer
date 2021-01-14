/**
 * Tests the MyStyleCustomize class (customize.js).
 */
describe( 'MyStyleCustomize.', function() {

    /**
     * Test the MyStyleCustomize.init function.
     */
    describe( 'init', function() {

        it( 'should return as expected for valid params', function() {

            var ret = MyStyleCustomize.init({
				'disableViewportRewrite': false,
				'enableFlash': false,
				'flashCustomizerUrl': 'http://customizer.ogmystyle.com/?app_id=72&amp;product_id=1',
				'html5CustomizerUrl': '//customizer-js.ogmystyle.com/?app_id=72&amp;product_id=1&'
			});

			( typeof ret ).should.equal( 'undefined' );
        });

    });

});
