=== MyStyle WordPress Plugin ===

Contributors: mystyle

License: GPLv3

License URI: http://www.gnu.org/licenses/gpl.html
Tags: woocommerce, customization 
Requires at least: 3.3

Tested up to: 4.2.1
Stable tag: 0.4.1

The MyStyle WordPress Plugin is a simple plugin that allows your customers to
customize products in WooCommerce.



== Description ==



The MyStyle WordPress Plugin works in conjunction with the 
[mystyleplatform.com](http://www.mystyleplatform.com) customization service.



== Installation ==

The MyStyle Plugin for Wordpress + WooCommerce requires that you have Wordpress with the Woo Commerce plugin activated.  It is very easy to install the plugin and set up in a few minutes.

e.g.

1. Install the Plugin:  Upload the mystyle-worpress-plugin folder to your website's `/wp-content/plugins/` directory
1. Activate the plugin:  Find MyStyle in your 'Plugins' menu in the WordPress admin and press 'Activate'.  This will enable the plugin and also automatically create a "customize" page where the Customizer itself will load when someone designs their own product. This new Customize page will be created complete with the shortcode already in the content.  You do not have to manually create your Customize App page or use the shortcode anywhere.  When a user clicks "customize" on any product, they will be taken to this automatically created page.  You may change the title of this page in your page list, or add your own content to it before or after the shortcode, if you want.
1. Follow the links in the Settings > MyStyle admin to obtain your Developer account, API Key and Secret, and enter them in the settings page.  When you register for your developer account, you'll be given a temporary demo ID to test with until we can review your accoun and provide your own credentials.
1. In the Woo Commerce product admin, test a product by looking for the MyStyle tab and enter a corresponding template ID#.  If you are using Demo mode, try just using the demo product ID 70 for an example 12x16 canvas print template.


== Changelog ==



= 0.4.1 =

* Fixed the help link.

* Updated the registration url.



= 0.4.0 =

* Added customize button to product listing.
* Fixed bug with extra closing div on the front end product page.

* Added help link to options page.
* Now handling no param scenario for Customize page.



= 0.3.0 =

* Added basic customizer functionality.



= 0.2.1 =

* Added Secret field.



= 0.2.0 =

* Now tested with PHPUnit and QUnit and fully compatible with WP 4.2

= 0.1.0 =
* Initial Beta release.
