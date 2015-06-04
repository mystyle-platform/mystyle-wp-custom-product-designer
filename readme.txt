=== MyStyle WordPress Plugin ===
Contributors: mystyle
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: woocommerce, customization, personalization, product-preview, designer
Requires at least: 3.3
Tested up to: 4.2.1
Stable tag: 0.4.1

The MyStyle WordPress Plugin is a simple plugin that allows your customers to design, customize, and personalize the products you can control in WooCommerce. Customized products are added directly to the Woo Commerce shopping cart.

== Description ==

You can enable any product in Woo Commerce for personalization / customization using MyStyle.  This allows any user to design their own graphics with a photo-realistic live product preview, and can generate the print file for the order to exact high-res specs (full-mode only).  All Customizers, images, uploaded images, and high-res print images are hosted remotely in the cloud by the MyStyle Platform and Amazon s3.  Users have a live product preview throughout the design experience.  Popular products include custom phone cases, t-shirts, skateboards, canvas prints, etc.

= Requirements =

Please note the MyStyle WordPress Woo Commerce Plugin is a serviceware plugin, and currently free for demo use only (no print files) and requires an active MyStyle Developer account to use in Full Mode and have access to the high-res, cloud-hosted print files.  The MyStyle Woo Commerce plugin works in conjunction with the [mystyleplatform.com](http://www.mystyleplatform.com/?ref=wprm) customization service.

= Benefits =

* Awesome User Experience
* User-designed products saves time and eliminates redundant design work
* High design-completion and sell-thru ratios
* World-class design tools in the MyStyle Customizer
* Easy / Quick to install
* Print-ready images streamline production with any fulfillment
* Use our catalog of products and network of manufacturers, or use your own!
* Experienced San Diego based development team for support or custom feature development
* 100% American Made in the USA!  No outsourcing!

= Plugin Features =

* Users can design their own products right on your website and:
 * Upload photos
 * Custom text tools (vector)
 * Custom Patterns with custom colors
 * Cool effects (dropshadow, glow, bevel)
 * Multi-side products
 * Multi-side design simultaneously
 * Gradients (color fades)
* Adds custom products directly to user's Woo Commerce shopping cart
* Prices and description content controlled by Woo Commerce as normal
* Thumbnail of user's design shows in the shopping cart for each customized item
* Flash AND HTML5 Mobile versions
 * Mobile auto-detection
* Print-ready image file generation (can be made to match your exact print specs)
 * Print files can be retrieved in the normal Woo Commerce order history in the admin (Full Mode Only)
* Cloud hosting for app and print files provided!
* Integrated with Woo Commerce products easily
* Free demo mode, Paid license full mode (see MyStyle Platform website for pricing)
* New products can be added to our system upon request
* New backgrounds or foregrounds or fonts can be added upon request

== Installation ==

The MyStyle Plugin for Wordpress + WooCommerce requires that you have Wordpress with the Woo Commerce plugin activated.  It is very easy to install the plugin and set up in a few minutes.  This is a service-ware plugin, meaning that Once installed it will load the Customizer app remotely from a hosted service, and it will function with all features but it in Demo Mode without access to Print Files.  Demo Mode is meant to test out the integration and basic features of designing a custom product.  For Full Mode, with access to print files, you will need to obtain your MyStyle Developer API Key and Secret, and enter them in your settings.

1. Install the Plugin:  Upload the mystyle-worpress-plugin folder to your website's `/wp-content/plugins/` directory
1. Activate the plugin:  Find MyStyle in your 'Plugins' menu in the WordPress admin and press 'Activate'.  This will enable the plugin and also automatically create a "customize" page where the Customizer itself will load when someone designs their own product. This new Customize page will be created complete with the shortcode already in the content.  You do not have to manually create your Customize App page or use the shortcode anywhere.  When a user clicks "customize" on any product, they will be taken to this automatically created page.  You may change the title of this page in your page list, or add your own content to it before or after the shortcode, if you want.
1. Follow the links in the Settings > MyStyle admin to obtain your Developer account, API Key and Secret, and enter them in the settings page.  When you register for your developer account, you'll be given a temporary demo ID to test with until we can review your accoun and provide your own credentials.
1. In the Woo Commerce product admin, test a product by looking for the MyStyle tab and enter a corresponding template ID#.  If you are using Demo mode, try just using the demo product ID 70 for an example 12x16 canvas print template.

== Screenshots ==

1. Example of a phone case in the customizer with the text that has been added that says "Your Design Here" with different fonts and dropshadow colors, and a  Blue-to-Pink gradient as the background.
2. Example of someone customizing text on the side of a BMW
3. Example of someone who has uploaded a number of images into a phone case design and is adding some stylized text with glow and shadow
4. Example of a Skateboard being customized in the customizer
5. Example of a Smart Car with a background image applied.

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
* Initial Alpha release.