/**
 * Tests the admin.js script.
 */
describe( 'admin.js ', function() {

    /**
     * Test the mystyleTogglePanelVis function.
     */
    describe( 'mystyleTogglePanelVis', function() {

        it( 'should toggle the visibility off', function() {

			// Add a mock tag to the DOM.
            jQuery( 'body' ).append( '<div id="mystyle-panel-1"><div id="mystyle-toggle-handle-1"></div></div>' );

			// Assert that the panel is visable.
            jQuery( '#mystyle-panel-1' ).css( 'display' ).should.equal( 'block' );

			// Call the function.
			mystyleTogglePanelVis( 1 );

			// Assert that the panel is now hidden.
            jQuery( '#mystyle-panel-1' ).css( 'display' ).should.equal( 'none' );
        });

    });

});
