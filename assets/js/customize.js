/**
 * Script for the Customize page.
 *
 * @package MyStyle
 * @since 3.8.1
 */

/**
 * MyStyleCustomize class.
 */
MyStyleCustomize = ( function() {

	/**
	 * Private object for attaching private properties and methods.
	 *
	 * @var {object}
	 */
	var self = {};

	/**
	 * Configuration object for holding the passed settings.
	 */
	self.config = {};
	self.config.disableViewportRewrite = false;
	self.config.enableFlash = false;
	self.config.flashCustomizerUrl = null;
	self.config.html5CustomizerUrl = null;

	/**
	 * Object for keeping track of the state of various components on the page.
	 */
	self.state = {};
	self.state.isFullscreen = false;
	self.state.settingOrientation = false;

	/**
	 * EXPOSED (as 'init').
	 *
	 * Initializes the customize page.
	 */
	self._init = function( params ) {
		self.config.disableViewportRewrite = params.disableViewportRewrite;
		self.config.enableFlash = params.enableFlash;
		self.config.flashCustomizerUrl = params.flashCustomizerUrl;
		self.config.html5CustomizerUrl = params.html5CustomizerUrl;
	};

	/**
	 * EXPOSED (as 'toggleFullscreen').
	 *
	 * Toggles full screen mode.
	 */
	self._toggleFullscreen = function() {
		if ( ! self.state.isFullscreen ) { // Enable full screen mode.
			console.log( 'enabling full screen mode' );
			jQuery( '#customizer-iframe' ).addClass( 'mystyle-fullscreen' );
			jQuery( '#customizer-iframe' ).parents().addClass( 'mystyle-fullscreen' );
			jQuery( ':not(.mystyle-fullscreen )' ).addClass( 'mystyle-fullscreen-hidden' );
			var closeButton = jQuery( '<a id="customizer-close-button" onclick="MyStyleCustomize.toggleFullscreen();" class="button"><span class="dashicons dashicons-no"></span></a>' );
			jQuery( '#customizer-wrapper' ).prepend( closeButton );

			self.state.isFullscreen = true;
		} else { // Disable full screen mode.
			console.log( 'disabling full screen mode' );
			jQuery( '#customizer-iframe' ).removeClass( 'mystyle-fullscreen' );
			jQuery( '#customizer-iframe' ).parents().removeClass( 'mystyle-fullscreen' );
			jQuery( '.mystyle-fullscreen-hidden' ).removeClass( 'mystyle-fullscreen-hidden' );
			jQuery( '#customizer-close-button' ).remove();

			self.state.isFullscreen = false;
		}

		return true;
	};

	/**
	 * EXPOSED (as 'setOrientation').
	 *
	 * Sets the orientation of the iframe and rewrites the viewport meta tag.
	 * This is done to ensure proper scaling and orientation of the MyStyle
	 * Customizer.
	 *
	 * Also includes code for viewport rewriting.
	 *
	 * Note that this only seems to work for mobile browsers and emulators.
	 */
	self._setOrientation = function() {

		if ( self.config.disableViewportRewrite ) /* || self.state.settingOrientation */
		{
			console.log( 'Note: Not setting viewport.  Mystyle viewport page zooming disabled.' );
			return;
		}

		// defaults for landscape
		var minAppWidthPortrait		= 550;
		var minAppWidthLandscape	= 1000;
		var orientation				= self._calculateOrientation();
		var currentViewportTag$		= jQuery( 'meta[name="viewport"]' );
		var screenWidthPx			= screen.width;
		var zoomInToFit				= screenWidthPx < minAppWidthLandscape; // dont zoom in if screen is larger than minimum landscape

		// set min size requirement for orientation
		var appMinWidth = ( 'portrait' === orientation ) ?
						minAppWidthPortrait :
						minAppWidthLandscape;// Landscape or portrait app min page width
		var scale = screenWidthPx / appMinWidth;// scale to minimum size requirement
		var finalScale = Math.min( 1, scale ); // dont zoom in (zoom out only) if its not under lanscape size
		var viewportSettings = 'initial-scale=' + finalScale + ', maximum-scale=' + finalScale;// new viewport settings
		var newViewportTagHTML = '<meta name="viewport" content="' + viewportSettings + '">'; // new viewport html

		console.log( 'mystyle customize page setting viewport: (' + orientation + ') final scale: ' + finalScale + ' ( orig: ' + scale + ') screen width: ' + screenWidthPx );

		// Set the viewport.
		jQuery( 'meta[name="viewport"]' ).remove(); // removal (remove and re-add seems to trigger viewport update better)
		jQuery( 'head' ).append( newViewportTagHTML ); // add new
	};


	/**
	 * EXPOSED (as 'renderCustomizer').
	 *
	 * Renders the customizer (in an iframe).
	 */
	self._renderCustomizer = function() {

		// Does the browser support Flash?
		var testFlash = swfobject.getFlashPlayerVersion();
		var flashSupported = false;
		if ( testFlash && testFlash.hasOwnProperty( 'major' ) && 0 < testFlash.major ) {
			flashSupported = true;
		}

		// Show Flash customizer?
		var showFlashCustomizer = false;
		if ( flashSupported && self.config.enableFlash ) {
			showFlashCustomizer = true;
		}

		var iframeCustomizer = '';
		if ( showFlashCustomizer ) {
			iframeCustomizer = '<iframe' +
					' id="customizer-iframe"' +
					' frameborder="0"' +
					' hspace="0"' +
					' vspace="0"' +
					' scrolling="no"' +
					' src="' + self.config.flashCustomizerUrl + '"' +
					' width="950"' +
					' height="550"></iframe>';
		} else {
			iframeCustomizer = '<iframe' +
					' id="customizer-iframe"' +
					' frameborder="0"' +
					' hspace="0"' +
					' vspace="0"' +
					' scrolling="no"' +
					' src="' + self.config.html5CustomizerUrl + '"' +
					' width="100%"' +
					' height="100%"></iframe>';
		}

		var elem = document.getElementById( 'customizer-wrapper' );
		elem.innerHTML = iframeCustomizer;
	};

	/**
	 * Private helper method that calculates the ideal orientation for the app
	 * (either "portrait" or "landscape").
	 *
	 * @returns {string} Returns the ideal orientation for the app ("portait" or
	 * "landscape").
	 */
	self._calculateOrientation = function() {
		var orientation = 'landscape';
		var winWidth = jQuery( window ).width();
		var winHeight = jQuery( window ).height();

		if ( ( winHeight > winWidth ) ) {
			orientation = 'portrait';
		}

		return orientation;
	};

	/**
	 * Declare the publicly exposed return object.
	 */
	self.public = {
		init: function( params ) {
			return self._init( params );
		},
		toggleFullscreen: function() {
			return self._toggleFullscreen();
		},
		setOrientation: function() {
			return self._setOrientation();
		},
		renderCustomizer: function() {
			return self._renderCustomizer();
		}
	};

	return self.public;

}() );
