/**
 * Tests the MyStyleDesignComplete class (design-complete.js).
 */
describe( 'MyStyleDesignComplete.', function() {

    /**
     * Test the MyStyleDesignComplete singleton is instantiated as expected.
     */
    describe( 'constructor', function() {

        it( 'should create the global variable', function() {
			( typeof window.MyStyleDesignComplete ).should.equal( 'object' );
        });

    });

});
