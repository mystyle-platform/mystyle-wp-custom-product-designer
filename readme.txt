=== MyStyle Custom Product Designer ===
Contributors: mystyle
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: woocommerce, customization, personalization, product-preview, designer
Requires at least: 3.3
Tested up to: 4.2.2
Stable tag: 0.5.0

The MyStyle Custom Product Designer allows your website visitors to design, customize & personalize, and purchase your WooCommerce products.  Print files provided.

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

== Installation ==

The MyStyle Custom Product Designer requires that you have WordPress with the WooCommerce plugin activated. The plugin is very easy to install and can be set up in just a few minutes.  This is a serviceware plugin, meaning that once installed, it will load the Custom Product Designer app remotely from a hosted service, and it will function with all features. However, when in Demo Mode, it will function without access to Print Files. To enable Full Mode, with access to print files, you will need to obtain your MyStyle Developer API Key and Secret, and enter them in your MyStyle settings.

1. Install the Plugin:  Upload the mystyle folder to your website's `/wp-content/plugins/` directory
2. Activate the plugin:  Find MyStyle in your 'Plugins' menu in the WordPress admin and press 'Activate'.  This will enable the plugin and also automatically create a "customize" page where the Product Designer will load when someone goes to design their own product. This new Customize page will be created complete with the Customizer Shortcode already in the content. You do not have to manually create your Customize App page or use the shortcode anywhere.  When a user clicks 'customize' on any product, they will be taken to this automatically created page.  You may change the title of this page in your page list, or add your own content to it before or after the shortcode.
3. Follow the links in the Settings > MyStyle admin to obtain your Developer account, API Key and Secret, and enter them in the settings page.  When you register for your Developer account, you'll be given a temporary demo ID to test with until we can review your account and provide you with your own credentials.
4. In the WooCommerce product settings, go to the MyStyle tab (beneath product data), check the box 'Make Customizable' box and enter a corresponding Template Id.  If you are using Demo mode, try just using the demo Template Id (Template Id: 70) for an example 12x16 canvas print template.  When you create a Developer account, you will be given a list of template ids.

== Screenshots ==

1. Example of a phone case in the customizer with text added that says "Your Design Here" with different fonts, dropshadow colors, and a Blue-to-Pink gradient as the background
2. Example of someone customizing text on the side of a BMW
3. Example of someone who has uploaded a number of images into a phone case design and is adding some stylized text with glow and shadow
4. Example of a Skateboard being customized in the customizer
5. Example of a Smart Car with a background image applied

== Changelog ==

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