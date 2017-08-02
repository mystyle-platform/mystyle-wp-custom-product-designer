=== MyStyle Custom Product Designer ===
Contributors: mystyleplatform
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: customization, designer, personalization, product-preview, woocommerce, custom product, product designer, Post, plugin, admin, posts, shortcode, images, page, image
Requires at least: 3.3
Tested up to: 4.8.0
WC tested up to: 3.1.1
Stable tag: 2.1.1

The MyStyle Custom Product Designer allows your website visitors to design, customize & personalize, and purchase your WooCommerce products.

== Description ==

You can enable any product in WooCommerce for personalization / customization using the MyStyle Custom Product Designer.  This allows any user to design their own graphics with a photo-realistic live product preview, and can generate the print file for the order to exact high-res specs (Full-Mode only).  The Customizer itself complete with graphics, uploaded images, and high-res print images are hosted remotely in the cloud by the MyStyle Platform and Amazon s3.  Users have a live product preview throughout the design experience.  Popular products to personalize include phone cases, t-shirts, canvas prints, etc.

= Requirements =

Please note that the MyStyle Custom Product Designer is a serviceware plugin, and requires an active MyStyle Developer account to use in Full-Mode and for access to the high-res, cloud-hosted print files.  The MyStyle Custom Product Designer works in conjunction with the [MyStylePlatform.com](http://www.mystyleplatform.com/?ref=wprm) customization service.

= Benefits =

* Awesome User Experience
* User-designed products saves time and eliminates redundant design work
* High design-completion and sell-thru ratios
* World-class design tools in the MyStyle Customizer
* Easy / Quick to install
* Print-ready images streamline production with any fulfillment
 * Use our catalog of products and network of manufacturers, or use your own!
* Experienced San Diego based development team for support or custom feature development
* 100% American Made in the USA! No outsourcing!

= Plugin Features =

* Users can design their own products right on your website and:
 * Upload photos
 * Add custom text (vector)
 * Add custom patterns with custom colors
 * Apply cool effects (dropshadow, glow, bevel)
 * Design multi-side products
 * Design multiple sides simultaneously
 * Add gradients (color fades)
* Integrates with WooCommerce products easily
* Adds custom products directly to the user's WooCommerce shopping cart
* Product prices and description content controlled by WooCommerce as normal
* Thumbnail of user's custom design shows in the shopping cart for each customized item
* Flash AND HTML5 Mobile versions
 * Mobile auto-detection
* Print-ready image file generation (can be made to match your exact print specs)
 * Print files can be retrieved in the normal WooCommerce order history in the admin (Full-Mode only)
 * Print files are available with a paid license (see [MyStyle Platform website](http://www.mystyleplatform.com/?ref=wprm2) for pricing)
* New products can be added to our system upon request
* New backgrounds, foregrounds or fonts can be added upon request

= Examples =

You can see some examples of the MyStyle Custom Product Designer in use (and try it out) at the following sites:

* [Custom skateboards and longboards](http://www.whateverskateboards.com/?ref=wprm2) (Whatever Skateboards)
* [Custom canvas prints](http://www.makecanvasprints.com/?ref=wprm2) (Make Canvas Prints)
* [Disc golf discs and ultimate frisbee discs](http://www.flydiscs.com/?ref=wprm2) (Fly Discs)
* [iPhone cases and phone cases](http://www.case-monkey.com/?ref=wprm2) (Case Monkey)

== Installation ==

The MyStyle Custom Product Designer requires that you have WordPress with the WooCommerce plugin activated. The plugin is very easy to install and can be set up in just a few minutes.  This is a serviceware plugin, meaning that once installed, it will load the Custom Product Designer app remotely from a hosted service, and it will function with all features. However, when in Demo Mode, it will function without access to Print Files. To enable Full Mode, with access to print files, you will need to obtain your MyStyle Developer API Key and Secret, and enter them in your MyStyle settings.

1. Install the Plugin:  Upload the mystyle folder to your website's `/wp-content/plugins/` directory
2. Activate the plugin:  Find MyStyle in your 'Plugins' menu in the WordPress admin and press 'Activate'.  This will enable the plugin and also automatically create a "customize" page where the Product Designer will load when someone goes to design their own product. This new Customize page will be created complete with the Customizer Shortcode already in the content. You do not have to manually create your Customize App page or use the shortcode anywhere.  When a user clicks 'customize' on any product, they will be taken to this automatically created page.  You may change the title of this page in your page list, or add your own content to it before or after the shortcode.
3. Follow the links in the Settings > MyStyle admin to obtain your Developer account, API Key and Secret, and enter them in the settings page.  When you register for your Developer account, you'll be given a temporary demo ID to test with until we can review your account and provide you with your own credentials.
4. In the WooCommerce product settings, go to the MyStyle tab (beneath product data), check the box 'Make Customizable' box and enter a corresponding Template Id. Try using the Template Id 70 for a 12x16 canvas print template.  You will receive a list of template ids once you have an active license.

== Screenshots ==

1. Example of a phone case in the customizer with text added that says "Your Design Here" with different fonts, dropshadow colors, and a Blue-to-Pink gradient as the background
2. Example of someone customizing text on the side of a BMW
3. Example of someone who has uploaded a number of images into a phone case design and is adding some stylized text with glow and shadow
4. Example of a Skateboard being customized in the customizer
5. Example of a Smart Car with a background image applied

== Changelog ==

= 2.1.1 =
* Fixed an issue with the Design Profile Page Fix tool.
* Updated readme.txt to reflect that the plugin has been tested with up to WP 4.8.0.
* Updated readme.txt to reflect that the plugin has been tested with up to WooCommerce 3.1.1.

= 2.1.0 =
* Added a system for theming the output of the plugin.
* Added a themable template file for the output of the cart item thumbnail for customized products.

= 2.0.3 =
* Now using actual passthru data from the handoff in the design complete email reload url.
* Now sending actual passthru data from the handoff in the mystyle_send_design_complete_email hook (used by the Email Manager add-on).
* Reception of the design description and price is now optional in the handoff (fixes an error message briefly displayed before redirecting to the cart).

= 2.0.2 =
* Fixed a bug with setting the quantity when adding to the cart from the design profile page.
* Now goes to the cart and displays a message when adding to the cart from the design profile page.

= 2.0.1 =
* Fixed an issue with the retrieving order item designs when the design is private.

= 2.0.0 =
* Added support for WC 3.0
* Added support for PHP 7.0
* Added support for HTTPS for the HTML5 customizer.

= 1.7.0 =
* Now only storing sessions after design save.
* Automatically purges all abandoned sessions.

= 1.6.3 =
* Now catching session errors (and starting a new session).

= 1.6.2 =
* Fixed an integration issue with the woocommerce-dynamic-pricing plugin.

= 1.6.1 =
* Hotfix to fix errors caused by vcs merge issue in v1.6.0 release.

= 1.6.0 =
* Now able to add variation data to the cart from the design profile page.
* Now recalculating the variation_id based on the selected attributes in the post data during the handoff.
* Fixed bug where the 'wc' property in the MyStyle class was erroneously marked as 'static'.

= 1.5.2 =
* Renderer link.

= 1.5.1 =
* Fixed a bug causing a MyStyle_Unauthorized_Exception when an anonymous user attempted to create a private design.

= 1.5.0 =
* Added link to the cart to edit the design.
* Fixed a bug with the price showing incorrectly for products with variations.
* Fixed a bug with that was occurring when the design_id somehow ended up as an empty string.
* Significant improvements to the testing system (fixed isolation bugs, fixed dependency issues).
* Significant refactoring (reorganized much of the code by function, additional use of singletons).

= 1.4.9 =
* Updated the user facing order info to use the design profile url.
* Added style for an admin warning box. Added delete function to the DesignManager class.
* Fixed the notice system to allow for different colors for different notice types.

= 1.4.8 =
* Added function for instantiating a design from a result array (for use by add-ons at this point).

= 1.4.7 =
* Changed the default title for the Design Profile Page to 'Community Design Gallery'.
* Changed the page title for individual design profile pages to 'Design ####'.

= 1.4.6 =
* Added support for editing designs that are in the cart.
* Updated the readme.txt to reflect that the plugin is fully tested and working with WP 4.6.1.

= 1.4.5 =
* Now supports /customize urls that are missing the "h" parameter (falls back to quantity 1 with no options).

= 1.4.4 =
* Fixed a bug with creating and purchasing private designs while not logged in.
* Updated the readme.txt to reflect that the plugin is fully tested and working with WP 4.6.0.

= 1.4.3 =
* Added pagination to the design profile index.
* Listed the Email Manager in the add-ons directory.

= 1.4.2 =
* Added a design index where you can view and page through saved public designs.
* Now storing cart data with the design.
* Design profile page now supports custom slugs.
* Fixed bug where customize page title was being hidden from menus.

= 1.4.1 =
* Fixed a bug where the upgrader wasn't properly creating the design profile page.

= 1.4.0 =
* Added design profile pages.

= 1.3.7 =
* Fixed a bug that occurred when an order was marked as completed.

= 1.3.6 =
* Added code to recreate invalid session ids.

= 1.3.5 =
* Fixed a bug with the function that generates session ids.

= 1.3.4 =
* Updated the session id generation function to add support for servers that don't have openssl.
* Updated the instructions in the readme.txt

= 1.3.3 =
* Bumping the version to try to fix vcs merge issue.

= 1.3.2 =
* Removed duplicate upgrade code from the MyStyle class constructor, to try to fix upgrade issue.

= 1.3.1 =
* Now setting the design complete email 'from' address using the admin email and blog name.
* Fixed a bug with the boolean options on the main settings page.
* Now passing through the print_type.
* Fixed an issue where the save/validation messages weren't showing on the main settings page.
* Added a 'mystyle_send_design_complete_email' action hook to allow for custom design complete emails.

= 1.3.0 =
* Now storing additional data including the designer's email address.
* Now tracking sessions.
* Fully tested and working with WP 4.5.1.

= 1.2.10 =
* Now displaying the design id in the cart, orders admin and Design Created email.
* Fixed an issue with passing product addons through the customizer into the cart.

= 1.2.9 =
* Added a field to allow the admin to optionally hide the page title on the Customize page.
* Added a Product field for optionally passing ux variables into the customizer.
* Added a Product field for optionally passing the print type into the customizer.
* Added a Product field for optionally passing a design id into the customizer.

= 1.2.8 =
* Now passing attributes through.
* WP 4.5 fully tested and working.

= 1.2.7 =
* Removed some forgotten debug messages.

= 1.2.6 =
* Fixed an issue with the frontend when the Customize page is deleted.
* Fixed an issue with activating the plugin from wp-cli.

= 1.2.5 =
* Fixed some issues with the examples in the readme.txt file.
* Updated the readme.txt to reflect that WP 4.4.2 is tested and working.

= 1.2.4 =
* Fixed a bug with the Fix Customize Page tool.
* Added example sites to the readme.txt file.

= 1.2.3 =
* Fixed a bug with the Fix Customize Page tool.

= 1.2.2 =
* Plugin is fully tested and working with WooCommerce 2.5.1
* Fixed an issue with the unit tests when running WooCommerce 2.5

= 1.2.1 =
* Changed the author from mystyle to mystyleplatform.
* Fixed bug with the thumbnail image in latest WP (caused by srcset attribute).
* Now validating the mystyle product options.
* Fixed some WP Coding Standards issues with some of the test files.
* Added more sophisticated notices system.
* Fixed some CSS issues with the admin screens in the latest WP and fixed a typo in a CSS name.
* Updated the readme.txt to reflect that WP 4.4.1 is fully tested and working.

= 1.2.0 =
* Add-ons directory added.
* Now sends design-saved email to user.

= 1.1.15 =
* Fixed bug with loading front-end on non-post pages.

= 1.1.14 =
* Now supports reloading designs through the Design Manager add-on.

= 1.1.13 =
* Modified the form/field text on the settings page.

= 1.1.12 =
* Added field allowing you to choose to always load the HTML5 customizer.

= 1.1.11 =
* Fixed bug with the passthru data when customizer accessed from product listing.

= 1.1.10 =
* Modified the css to set the width and height of the customizer-wrapper.

= 1.1.9 =
* Added frontend stylesheet.
* Added tool for fixing the Customize page.
* Fixed a bug with the cart thumbnail.

= 1.1.8 =
* Added support for product attributes/variations.
* Fixed bug with add to cart handler on WooCommerce < 2.3.

= 1.1.7 =
* Added support for multiple print files.
* Fixed bug with add to cart hook.

= 1.1.6 =
* Fixed bug causing customize button to show on non-customizable products.

= 1.1.5 =
* Added support for quantities.

= 1.1.4 =
* Added listing of customizable products to the Customize page.

= 1.1.3 =
* Fixed a bug with the help system.
* Updated the readme.

= 1.1.2 =
* Fixed cart item data issue with WooCommerce 2.2.
* Added link to product catalog.

= 1.1.1 =
* Fixed force_mobile bug.

= 1.1.0 =
* Added support for mobile.
* Admin UI enhancements.

= 1.0.1 =
* Basic PHPUnit test coverage complete.

= 1.0.0 =
* Added PHPUnit tests for the MyStyle_Handoff class.
* Added PHPUnit tests for the MyStyle_Design class.
* Added PHPUnit tests for the MyStyle_DesignManager class.
* Added PHPUnit tests for the MyStyle_EntityManager class.

= 0.5.3 =
* Added PHPUnit tests for the MyStyle_Install class.

= 0.5.2 =
* Updated the README.md file.

= 0.5.1 =
* Fixed truncated plugin description
* Fixed default download directory for easier install
* Fixed marketplace banner graphic for WP page title text

= 0.5.0 =
* Beta release
* Added Designs table

= 0.4.2 =
* Updated the readme.txt
* Updated the help

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
