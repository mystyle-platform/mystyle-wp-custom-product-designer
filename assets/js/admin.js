/**
 * Toggles a panel so that it's contents can be seen or hidden.
 * 
 * @param {integer} id The id of the panel being toggled.
 * @returns {Boolean} Returns false;
 */
function mystyleTogglePanelVis( id ) {
	var panelObj = document.getElementById( 'mystyle-panel-' + id );
	var toggleObj = document.getElementById( 'mystyle-toggle-handle-' + id );

	if ( panelObj.style.display == 'none' ) { //open the data
		panelObj.style.display = 'block';
		toggleObj.className += ' mystyle-closed';
	} else { //close the data
		panelObj.style.display = 'none';
		toggleObj.className = toggleObj.className.replace( /(?:^|\s)mystyle\-closed(?!\S)/g, '' );
	}

	return false;
}
