/* WooCommerce Order Admin Page */

/**
 * Toggles an item so that the mystyle data can be seen or hidden.
 * @param {integer} itemId The item id of the item being toggled.
 * @returns {Boolean} Returns false;
 */
function mystyleOrderItemDataToggleVis( itemId ) {
    var itemDataObj = document.getElementById( 'mystyle-item-data-' + itemId );
    var itemToggleObj = document.getElementById( 'mystyle-item-handle-' + itemId );

    if( itemDataObj.style.display == 'none' ) { //open the data
        itemDataObj.style.display = 'block';
        itemToggleObj.className += ' mystyle-closed';
    } else { //close the data
        itemDataObj.style.display = 'none';
        itemToggleObj.className = itemToggleObj.className.replace( /(?:^|\s)mystyle\-closed(?!\S)/g , '' );
    }

    return false;
}

