/**
 * Script for the MyStyle Design Complete functionality.
 *
 * @package MyStyle
 * @since 3.4.0
 */

MyStyleDesignComplete = ( function() {

	/**
	 * Private object for attaching private properties and methods.
	 *
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
	 */
	self._init = function() {

		// Validate the query string vars before continuing
		// This will throw an exception if the vars are missing or invalid.
		self._validateQueryVars();

		// Pull the designId from the current url.
		self.designId = self._getQueryVariable( 'design_id' );

		// Set the value of any design_id fields.
		self._setDesignIdFields();
	};

	/**
	 * Private helper method that gets a variable from the current query string
	 * (via window.location).
	 * Source: https://css-tricks.com/snippets/javascript/get-url-variables/
	 *
	 * @param {string} variable The name of the variable that you want to get.
	 * @returns {mixed} Returns the variable value or false if the variable isn't
	 * found.
	 */
	self._getQueryVariable = function( variable ) {
		var query = window.location.search.substring( 1 );
		var vars  = query.split( '&' );
		var i, pair;

		for ( i = 0; i < vars.length; i++ ) {
			pair = vars[i].split( '=' );
			if ( pair[0] === variable ) {
				return pair[1];
			}
		}

		return ( false );
	};

	/**
	 * Private helper method that validates the query args.
	 *
	 * @returns {boolean} Returns true if the query args are valid, otherwise,
	 * returns false.
	 */
	self._validateQueryVars = function() {
		var designId;

		// Validate the design_complete query var.
		if ( '1' !== self._getQueryVariable( 'design_complete' ) ) {
			throw 'MyStyleDesignComplete: Query vars are not valid (must include "design_complete=1").';
		}

		// Validate the design_id query var.
		designId = self._getQueryVariable( 'design_id' );
		if ( ! designId ) {
			throw 'MyStyleDesignComplete: Query vars are not valid (must include design_id).';
		}
		if ( isNaN( designId ) ) {
			throw 'MyStyleDesignComplete: Query vars are not valid (design_id must be a number).';
		}

		return true;
	};

	/**
	 * Private helper method that sets the value of any design_id fields.
	 */
	self._setDesignIdFields = function() {

		jQuery( 'input' ).each(
			function( index, value ) {
				var id, name;

				// Any field with 'design_id' in the id.
				id = jQuery( this ).attr( 'id' );
				if ( ( 'undefined' !== typeof id ) && ( -1 !== id.indexOf( 'design_id' ) ) ) {
					jQuery( this ).val( self.designId );
					return true;
				}

				// Any field with 'design_id' in the name attribute.
				name = jQuery( this ).attr( 'name' );
				if ( ( 'undefined' !== typeof name ) && ( -1 !== name.indexOf( 'design_id' ) ) ) {
					jQuery( this ).val( self.designId );
				}
			}
		);
	};

	/**
	 * Declare the publicly exposed return object.
	 */
	self.public = {
		init: function() {
			return self._init();
		}
	};

	return self.public;

}() );

jQuery( window ).ready(
	function() {
		if ( ! window.hasOwnProperty( '__karma__' ) ) {
			MyStyleDesignComplete.init();
		}
	}
);
