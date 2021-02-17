/**
 * Toggles a panel so that its contents can be seen or hidden.
 *
 * @param {integer} id The id of the panel being toggled.
 * @returns {boolean} Returns false.
 */
function mystyleTogglePanelVis( id ) {
	var panelObj  = document.getElementById( 'mystyle-panel-' + id );
	var toggleObj = document.getElementById( 'mystyle-toggle-handle-' + id );

	if ( 'none' == panelObj.style.display ) { // Open the data.
		panelObj.style.display = 'block';
		toggleObj.className   += ' mystyle-closed';
	} else { // Close the data.
		panelObj.style.display = 'none';
		toggleObj.className    = toggleObj.className.replace( /(?:^|\s)mystyle\-closed(?!\S)/g, '' );
	}

	return false;
}
