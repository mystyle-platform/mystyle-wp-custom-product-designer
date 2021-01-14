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

			// Add the close button.
			jQuery( '#customizer-wrapper' ).prepend(
				jQuery( '<a id="customizer-close-button" onclick="MyStyleCustomize.toggleFullscreen();" class="button"><span class="dashicons dashicons-no"></span></a>' )
			);

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

		var minAppWidthPortrait,
			minAppWidthLandscape,
			orientation,
			currentViewportTag$,
			screenWidthPx,
			zoomInToFit,
			appMinWidth,
			scale,
			finalScale,
			newViewportTagHTML,
			hasViewport;

		if ( self.config.disableViewportRewrite ) {
			console.log( 'Note: Not setting viewport. Mystyle viewport page zooming disabled.' );
			return;
		}

		// Defaults for landscape.
		minAppWidthPortrait  = 550;
		minAppWidthLandscape = 1000;
		orientation          = self._calculateOrientation();
		currentViewportTag$  = jQuery( 'meta[name="viewport"]' );
		screenWidthPx        = screen.width;

		// Set min size requirement for orientation.
		appMinWidth        = ( 'portrait' === orientation ) ?
								minAppWidthPortrait :
								minAppWidthLandscape; // Landscape or portrait app min page width.
		scale              = screenWidthPx / appMinWidth; // Scale to minimum size requirement.
		finalScale         = scale;
		if ( zoomInToFit ) {
			finalScale = Math.min( 1, scale );
		}
		finalScale         = Math.min( 1, scale ); // Don't zoom in (zoom out only) if it's not under lanscape size.
		viewportSettings   = 'initial-scale=' + finalScale + ', maximum-scale=' + finalScale;// New viewport settings.
		newViewportTagHTML = '<meta name="viewport" content="' + viewportSettings + '">'; // New viewport html.

		console.log( 'MyStyle customize page setting viewport: (' + orientation + ') final scale: ' + finalScale + ' ( orig: ' + scale + ') screen width: ' + screenWidthPx );

		hasViewport = (
			( 'undefined' !== typeof currentViewportTag$ ) &&
			( currentViewportTag$ ) &&
			( 0 < currentViewportTag$.length )
		);

		// Set the viewport.
		if ( hasViewport ) {
			jQuery( 'meta[name="viewport"]' ).remove(); // Removal (remove and re-add seems to trigger viewport update better).
		}
		jQuery( 'head' ).append( newViewportTagHTML ); // Add new.
	};


	/**
	 * EXPOSED (as 'renderCustomizer').
	 *
	 * Renders the customizer (in an iframe).
	 */
	self._renderCustomizer = function() {

		var flashSupported = false;
		var showFlashCustomizer = false;
		var iframeCustomizer = '';
		var testFlash, elem;

		// Does the browser support Flash?
		testFlash = swfobject.getFlashPlayerVersion();

		if ( testFlash && testFlash.hasOwnProperty( 'major' ) && 0 < testFlash.major ) {
			flashSupported = true;
		}

		// Show Flash customizer?
		if ( flashSupported && self.config.enableFlash ) {
			showFlashCustomizer = true;
		}

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

		elem = document.getElementById( 'customizer-wrapper' );
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
