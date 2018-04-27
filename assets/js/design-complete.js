/**
 * Script for the MyStyle Design Complete functionality.
 * @package MyStyle
 * @since 3.4.0
 */

MyStyleDesignComplete = function() {

    /**
     * Private object for attaching private properties and methods
     * @var {object}
     */
    var self = {};
    
    self.designId = null;

    /**
     * EXPOSED (as 'init')
     * 
     * Initializes the design complete page.
     * 
     * Does the following:
     *  * Finds any forms and adds the design_id as a hidden field.
     *  
     */
    self._init = function() {
        
        // Validate the query string vars before continuing
        // This will throw an exception if the vars are missing or invalid.
        self._validateQueryVars();
            
        // Pull the designId from the current url
        self.designId = self._getQueryVariable( 'design_id' );

        // Add hidden fields to the forms.
        self._addHiddenFields();
    };

    /**
     * Private helper method that gets a variable from the current query string
     * (via window.location).
     * Source: https://css-tricks.com/snippets/javascript/get-url-variables/
     * @param {string} variable The name of the variable that you want to get.
     * @returns {mixed} Returns the variable value or false if the variable isn't
     * found.
     */
    self._getQueryVariable = function( variable ) {
        var query = window.location.search.substring( 1 );
        var vars = query.split( '&' );
        for ( var i=0; i < vars.length; i++ ) {
            var pair = vars[i].split( '=' );
            if( pair[0] === variable ) {
                return pair[1];
            }
        }

        return( false );
    };
    
    /**
     * Private helper method that validates the query args.
     * @returns {boolean} Returns true if the query args are valid, otherwise,
     * returns false.
     */
    self._validateQueryVars = function() { 
    
        // Validate the design_complete query var
        if ( self._getQueryVariable( 'design_complete' ) !== '1' ) {
            throw 'MyStyleDesignComplete: Query vars are not valid (must include "design_complete=1").';
        }
        
        // Validate the design_id query var
        var designId = self._getQueryVariable( 'design_id' );
        if ( ! designId ) {
            throw 'MyStyleDesignComplete: Query vars are not valid (must include design_id).';
        }
        if ( isNaN( designId ) ) {
            throw 'MyStyleDesignComplete: Query vars are not valid (design_id must be a number).';
        }
        
        return true;
    };
    
    /**
     * Private helper method that sets the design_id as a hidden field on any
     * forms on the page.
     */
    self._addHiddenFields = function() { 
        jQuery( 'form' ).each( function ( index, value ) { 
            jQuery( this ).append( '<input type="hidden" name="design_id" value="' + self.designId + '" />' );
        });
    };
    
    /**
     * Declare the publicly exposed return object.
     */
    self.public = {
        init: function() { return self._init(); }
    };
    
    return self.public;
    
}(); 
// End MyStyleDesignComplete class.

jQuery( window ).ready( function() {
    MyStyleDesignComplete.init();
});

