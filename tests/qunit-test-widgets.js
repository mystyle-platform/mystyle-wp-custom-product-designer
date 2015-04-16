/**
 * QUnit tests for the MyStyle WordPress Plugin's admin options page.
 * 
 * @package MyStyle
 * @since 0.1.0
 */

//Custom Assertions
QUnit.assert.contains = function( needle, haystack, message ) {
    var actual = haystack.indexOf(needle) > -1;
    this.push(actual, actual, needle, message);
};

/**
* Assert that the widget was rendered (by checking for the header).
*/
QUnit.test( "Test that the MyStyle widget is available", function( assert ) {
    var header = $("div#widget-list div.widget-title h4").html();
    assert.ok(header.indexOf("MyStyle") !== -1, "Widget Available");
});

/**
* Assert that the MyStyle widget help is rendered.
*/
QUnit.test( "Test that the help was rendered", function( assert ) {
    var header = $("#tab-panel-mystyle_widget h1").html();
    assert.equal(header, "MyStyle Widget Help", "Help rendered");
});



