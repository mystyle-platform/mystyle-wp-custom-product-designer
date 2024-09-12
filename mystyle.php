<?php
/**
 * Plugin Name: MyStyle
 * Plugin URI: http://www.mystyleplatform.com
 * Description: The MyStyle Custom Product Designer is a simple plugin that allows your customers to customize products in WooCommerce.
 * Version: 3.20
 * WC requires at least: 2.2.0
 * WC tested up to: 8.6.1
 * Author: mystyleplatform
 * Author URI: www.mystyleplatform.com
 * License: GPL v3
 *
 * MyStyle Custom Product Designer
 * Copyright (c) 2021 MyStyle <contact@mystyleplatform.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package MyStyle
 * @since 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MyStyle' ) ) :

	/**
	 * Main MyStyle Class.
	 */
	final class MyStyle {

		/**
		 * The standard date format for the plugin.
		 *
		 * @var string
		 */
		const STANDARD_DATE_FORMAT = 'Y-m-d H:i:s';

		/**
		 * Singleton class instance.
		 *
		 * @var MyStyle
		 */
		private static $instance;

		/**
		 * Our WooCommerce interface.
		 *
		 * @var MyStyle_WC_Interface
		 */
		private $wc;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
			$this->init_hooks();
			$this->init_singletons();
		}

		/**
		 * Define MYSTYLE Constants.
		 */
		private function define_constants() {
			define( 'MYSTYLE_PATH', plugin_dir_path( __FILE__ ) );
			define( 'MYSTYLE_INCLUDES', MYSTYLE_PATH . 'includes/' );
			define( 'MYSTYLE_BASENAME', plugin_basename( __FILE__ ) );
			define( 'MYSTYLE_URL', plugins_url( '/', __FILE__ ) );
			define( 'MYSTYLE_ASSETS_URL', MYSTYLE_URL . 'assets/' );
			define( 'MYSTYLE_TEMPLATES', MYSTYLE_PATH . 'templates/' );

			// Include the optional config.php file.
			if ( file_exists( MYSTYLE_PATH . 'config.php' ) ) {
				include_once MYSTYLE_PATH . 'config.php';
			}

			if ( ! defined( 'MYSTYLE_SERVER' ) ) {
				define( 'MYSTYLE_SERVER', 'http://api.ogmystyle.com/' );
			}
			if ( ! defined( 'MYSTYLE_VERSION' ) ) {
				define( 'MYSTYLE_VERSION', '3.20' );
			}
			if ( ! defined( 'MYSTYLE_TEMPLATE_DEBUG_MODE' ) ) {
				define( 'MYSTYLE_TEMPLATE_DEBUG_MODE', false );
			}

			define( 'MYSTYLE_OPTIONS_NAME', 'mystyle_options' );
			define( 'MYSTYLE_NOTICES_NAME', 'mystyle_notices' );
			define( 'MYSTYLE_NOTICES_DISMISSED_NAME', 'mystyle_notices_dismissed' );
			define( 'MYSTYLE_CUSTOMIZE_PAGEID_NAME', 'mystyle_customize_page_id' );
			define( 'MYSTYLE_DESIGN_PROFILE_PAGEID_NAME', 'mystyle_design_profile_page_id' );
			define( 'MYSTYLE_DESIGN_TAG_PAGEID_NAME', 'mystyle_design_tag_page_id' );
			define( 'MYSTYLE_DESIGN_TAG_INDEX_PAGEID_NAME', 'mystyle_design_tag_index_page_id' );
			define( 'MYSTYLE_DESIGN_TAG_INDEX_SEO_PAGEID_NAME', 'mystyle_design_tag_index_seo_page_id' );
			define( 'MYSTYLE_DESIGN_COLLECTION_INDEX_PAGEID_NAME', 'mystyle_design_collection_index_page_id' );
			define( 'MYSTYLE_DESIGN_COLLECTION_PAGEID_NAME', 'mystyle_design_collection_page_id' );
			define( 'MYSTYLE_TAXONOMY_NAME', 'design_tag' );
			define( 'MYSTYLE_COLLECTION_NAME', 'design_collection' );
		}

		/**
		 * Hook into actions and filters.
		 */
		private function init_hooks() {
			// Plugin setup and registrations.
			register_activation_hook( __FILE__, array( 'MyStyle', 'activate' ) );
			register_deactivation_hook( __FILE__, array( 'MyStyle', 'deactivate' ) );
			register_uninstall_hook( __FILE__, array( 'MyStyle', 'uninstall' ) );

			add_action( 'init', array( $this, 'check_version' ), 10, 0 );
			add_action( 'init', array( $this, 'register_shortcodes' ), 10, 0 );
			add_action( 'admin_init', array( $this, 'check_woocommerce' ), 10, 0 );

			// Add the action before_woocommerce_init here
			add_action('before_woocommerce_init', array($this, 'before_woocommerce_init_action'));
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {

			require_once MYSTYLE_PATH . 'tests/qunit.php';
			require_once MYSTYLE_INCLUDES . 'class-mystyle-util.php';
			require_once MYSTYLE_INCLUDES . 'exceptions/class-mystyle-exception.php';
			require_once MYSTYLE_INCLUDES . 'exceptions/class-mystyle-bad-request-exception.php';
			require_once MYSTYLE_INCLUDES . 'exceptions/class-mystyle-forbidden-exception.php';
			require_once MYSTYLE_INCLUDES . 'exceptions/class-mystyle-not-found-exception.php';
			require_once MYSTYLE_INCLUDES . 'exceptions/class-mystyle-unauthorized-exception.php';
			require_once MYSTYLE_INCLUDES . 'woocommerce/class-mystyle-wc-interface.php';
			require_once MYSTYLE_INCLUDES . 'woocommerce/class-mystyle-abstractwc.php';
			require_once MYSTYLE_INCLUDES . 'woocommerce/class-mystyle-wc.php';
			require_once MYSTYLE_INCLUDES . 'model/class-mystyle-access.php';
			require_once MYSTYLE_INCLUDES . 'model/class-mystyle-pager.php';
			require_once MYSTYLE_INCLUDES . 'class-mystyle-options.php';

			// Entities.
			require_once MYSTYLE_INCLUDES . 'db/class-mystyle-entity.php';
			require_once MYSTYLE_INCLUDES . 'db/class-mystyle-entitymanager.php';
			require_once MYSTYLE_INCLUDES . 'entities/class-mystyle-session.php';
			require_once MYSTYLE_INCLUDES . 'entities/class-mystyle-sessionmanager.php';
			require_once MYSTYLE_INCLUDES . 'entities/class-mystyle-design.php';
			require_once MYSTYLE_INCLUDES . 'entities/class-mystyle-designmanager.php';

			require_once MYSTYLE_INCLUDES . 'model/class-mystyle-user.php';
			require_once MYSTYLE_INCLUDES . 'model/class-mystyle-product.php';
			require_once MYSTYLE_INCLUDES . 'model/class-mystyle-order.php';
			require_once MYSTYLE_INCLUDES . 'api/interface-mystyle-api.php';
			require_once MYSTYLE_INCLUDES . 'api/class-mystyle-api.php';
			require_once MYSTYLE_INCLUDES . 'class-mystyle-ajax.php';
			require_once MYSTYLE_INCLUDES . 'pages/class-mystyle-customize-page.php';
			require_once MYSTYLE_INCLUDES . 'pages/class-mystyle-design-profile-page.php';
			require_once MYSTYLE_INCLUDES . 'pages/class-mystyle-my-designs-page.php';
			require_once MYSTYLE_INCLUDES . 'pages/class-mystyle-author-designs-page.php';
			require_once MYSTYLE_INCLUDES . 'pages/class-mystyle-design-tag-page.php';
			require_once MYSTYLE_INCLUDES . 'pages/class-mystyle-design-collection-page.php';
			require_once MYSTYLE_INCLUDES . 'class-mystyle-sessionhandler.php';
			require_once MYSTYLE_INCLUDES . 'class-mystyle-install.php';
			require_once MYSTYLE_INCLUDES . 'admin/notices/class-mystyle-notice.php';
			require_once MYSTYLE_INCLUDES . 'admin/notices/class-mystyle-notice-controller.php';
			require_once MYSTYLE_INCLUDES . 'admin/notices/mystyle-notice-functions.php';
			require_once MYSTYLE_INCLUDES . 'class-mystyle-user-interface.php';
			require_once MYSTYLE_INCLUDES . 'class-mystyle-order-listener.php';
			require_once MYSTYLE_INCLUDES . 'class-mystyle-passthru-codec.php';
			require_once MYSTYLE_INCLUDES . 'wprestapi/class-mystyle-wp-rest-api-design-controller.php';
			require_once MYSTYLE_INCLUDES . 'integrations/tm-extra-product-options/class-mystyle-tm-extra-product-options.php';
			require_once MYSTYLE_INCLUDES . 'integrations/wpml/class-mystyle-wpml.php';

			// We include this frontend class here because it is used by our
			// shortcode classes (which are used both on the frontend and the admin).
			require_once MYSTYLE_INCLUDES . 'frontend/class-mystyle-frontend.php';

			// Taxonomy includes.
			require_once MYSTYLE_INCLUDES . 'taxonomies/class-mystyle-design-tag-taxonomy.php';
			require_once MYSTYLE_INCLUDES . 'taxonomies/class-mystyle-design-collection-taxonomy.php';

			// Shortcode includes.
			require_once MYSTYLE_INCLUDES . 'shortcodes/class-mystyle-design-profile-shortcode.php';
			require_once MYSTYLE_INCLUDES . 'shortcodes/class-mystyle-design-shortcode.php';
			require_once MYSTYLE_INCLUDES . 'shortcodes/class-mystyle-design-tag-shortcode.php';
			require_once MYSTYLE_INCLUDES . 'shortcodes/class-mystyle-design-collection-shortcode.php';
			require_once MYSTYLE_INCLUDES . 'shortcodes/class-mystyle-customizer-shortcode.php';

			require_once MYSTYLE_PATH . 'functions.php';

			if ( $this->is_request( 'admin' ) ) {
				$this->admin_includes();
			}

			if ( $this->is_request( 'frontend' ) ) {
				$this->frontend_includes();
			}
		}

		/**
		 * Include required admin files.
		 */
		private function admin_includes() {
			require_once MYSTYLE_INCLUDES . 'admin/class-mystyle-admin.php';
			require_once MYSTYLE_INCLUDES . 'admin/pages/class-mystyle-options-page.php';
			require_once MYSTYLE_INCLUDES . 'admin/pages/class-mystyle-dashboard-page.php';
			require_once MYSTYLE_INCLUDES . 'admin/pages/class-mystyle-addons-page.php';
			require_once MYSTYLE_INCLUDES . 'admin/pages/class-mystyle-design-tags-page.php';
			require_once MYSTYLE_INCLUDES . 'admin/pages/class-mystyle-design-collections-page.php';
			require_once MYSTYLE_INCLUDES . 'admin/help/class-mystyle-help.php';
			require_once MYSTYLE_INCLUDES . 'admin/class-mystyle-woocommerce-admin-product.php';
			require_once MYSTYLE_INCLUDES . 'admin/class-mystyle-woocommerce-admin-order.php';
		}

		/**
		 * Include required frontend files.
		 */
		private function frontend_includes() {
			require_once MYSTYLE_INCLUDES . 'frontend/class-mystyle-cart.php';
			require_once MYSTYLE_INCLUDES . 'frontend/class-mystyle-design-complete.php';
			require_once MYSTYLE_INCLUDES . 'frontend/endpoints/class-mystyle-handoff.php';
			require_once MYSTYLE_INCLUDES . 'frontend/class-mystyle-configur8.php';
		}

		/**
		 * Init our singletons (registers hooks, etc).
		 */
		private function init_singletons() {

			if ( ! defined( 'DOING_PHPUNIT' ) ) {
				// Set up the third party interfaces.
				$this->set_WC( new MyStyle_WC() );
			}

			$mystyle_api = new MyStyle_API( MYSTYLE_SERVER );
			MyStyle_User_Interface::get_instance();
			MyStyle_Order_Listener::get_instance();
			MyStyle_Passthru_Codec::get_instance();
			MyStyle_Wp_Rest_Api_Design_Controller::get_instance();
			MyStyle_Design_Tag_Taxonomy::get_instance();
			MyStyle_Design_Collection_Taxonomy::get_instance();
			MyStyle_Ajax::get_instance();
			MyStyle_Tm_Extra_Product_Options::get_instance();
			MyStyle_Wpml::get_instance();

			if ( $this->is_request( 'admin' ) ) {
				// ---- ADMIN ---- //
				// Set up the notifications system.
				MyStyle_Notice_Controller::get_instance();

				// Set up the main admin class.
				MyStyle_Admin::get_instance();

				// Set up the Dashboard page.
				$dashboard = MyStyle_Dashboard_Page::get_instance();
				$dashboard->set_mystyle_api( $mystyle_api );

				// Set up the options page.
				MyStyle_Options_Page::get_instance();

				// Set up the addons page.
				MyStyle_Addons_Page::get_instance();

				// Set up the Design Tags page.
				MyStyle_Design_Tags_Page::get_instance();
				MyStyle_Design_Collections_Page::get_instance();

				// Set up the Help.
				MyStyle_Help::get_instance();

				// Hook into the WooCommerce admin.
				MyStyle_WooCommerce_Admin_Product::get_instance();
				MyStyle_WooCommerce_Admin_Order::get_instance();

				// Load qunit.
				if ( ( defined( 'MYSTYLE_LOAD_QUNIT' ) ) && ( true === MYSTYLE_LOAD_QUNIT ) ) {
					add_action( 'admin_footer', 'mystyle_load_qunit' );
				}
			}

			if ( $this->is_request( 'frontend' ) ) {
				// ---- FRONT END ---- //
				if ( ! defined( 'MYSTYLE_DESIGNS_PER_PAGE' ) ) {
					define( 'MYSTYLE_DESIGNS_PER_PAGE', 25 );
				}

				MyStyle_SessionHandler::get_instance();
				MyStyle_FrontEnd::get_instance();
				MyStyle_Cart::get_instance();
				MyStyle_Design_Complete::get_instance();

				/* @var $mystyle_handoff MyStyle_Handoff The MyStyle_Handoff singleton. */
				$mystyle_handoff = MyStyle_Handoff::get_instance();
				$mystyle_handoff->set_mystyle_api( $mystyle_api );

				MyStyle_Customize_Page::get_instance();
				MyStyle_Design_Profile_Page::get_instance();
				MyStyle_Configur8::get_instance();
				MyStyle_My_Designs_Page::get_instance();
				MyStyle_Author_Designs_Page::get_instance();
				MyStyle_Design_Tag_Page::get_instance();
				MyStyle_Design_Collection_Page::get_instance();
			}
		}

		/**
		 * Sets the current version against the version in the db and handles any
		 * updates.
		 *
		 * @todo Add unit testing for this function.
		 */
		public function check_version() {
			$options      = get_option( MYSTYLE_OPTIONS_NAME, array() );
			$data_version = ( array_key_exists( 'version', $options ) ) ? $options['version'] : null;
			if ( MYSTYLE_VERSION !== $data_version ) {
				$options['version'] = MYSTYLE_VERSION;
				update_option( MYSTYLE_OPTIONS_NAME, $options );
				if ( ! is_null( $data_version ) ) {  // Skip if not an upgrade.
					// Run the upgrader.
					MyStyle_Install::upgrade( $data_version, MYSTYLE_VERSION );
				}
			}
		}

		/**
		 * Register our shortcodes.
		 *
		 * This is run during init.
		 *
		 * @todo Add unit testing for this function.
		 */
		public function register_shortcodes() {
			add_shortcode( 'mystyle_customizer', array( 'MyStyle_Customizer_Shortcode', 'output' ) );
			add_shortcode( 'mystyle_design_profile', array( 'MyStyle_Design_Profile_Shortcode', 'output' ) );
			add_shortcode( 'mystyle_design', array( 'MyStyle_Design_Shortcode', 'output' ) );
			add_shortcode( 'mystyle_design_tags', array( 'MyStyle_Design_Tag_Shortcode', 'output' ) );
			add_shortcode( 'mystyle_design_collections', array( 'MyStyle_Design_Collection_Shortcode', 'output' ) );
		}

		/**
		 * Checks for WooCommerce. If it isn't found and we are in the admin,
		 * display a notice.
		 */
		public function check_woocommerce() {
			if ( ! $this->wc->is_installed() ) {
				$wc_missing_notice = MyStyle_Notice::create( 'notify_wc_missing', 'MyStyle requires WooCommerce but WooCommerce wasn\'t found. Please install and activate WooCommerce.' );
				mystyle_notice_add_to_queue( $wc_missing_notice );
			}
		}

		/**
		 * Action to be executed before WooCommerce init.
		 */
		public function before_woocommerce_init_action()
		{
			if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
			}
		}

		/**
		 * Activate function. Activates the plugin.
		 */
		public static function activate() {
			MyStyle_Install::activate();
		}

		/**
		 * Deactivate function. Deactivates the plugin.
		 */
		public static function deactivate() {
			MyStyle_Install::deactivate();
		}

		/**
		 * Uninstall function. Uninstalls the plugin.
		 */
		public static function uninstall() {
			MyStyle_Install::uninstall();
		}

		/**
		 * Function that looks to see if any products are MyStyle enabled.
		 *
		 * @return boolean Returns true if at least one product is customizable.
		 */
		public static function site_has_customizable_products() {
			$args = array(
				'post_type'   => 'product',
				'numberposts' => 1,
				// phpcs:ignore WordPress.VIP.SlowDBQuery.slow_db_query_meta_key
				'meta_key'    => '_mystyle_enabled',
				// phpcs:ignore WordPress.VIP.SlowDBQuery.slow_db_query_meta_value
				'meta_value'  => 'yes',
			);

			$customizable_products = get_posts( $args );

			if ( ! empty( $customizable_products ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Sets the WooCommerce interface.
		 *
		 * @param MyStyle_WC_Interface $mystyle_wc_interface The WooCommerce
		 * interface.
		 * @codingStandardsIgnoreStart (ignoring incorrect case function name).
		 */
		public function set_WC( MyStyle_WC_Interface $mystyle_wc_interface ) {
			// @codingStandardsIgnoreEnd
			$this->wc = $mystyle_wc_interface;
		}

		/**
		 * Gets the WooCommerce interface.
		 *
		 * @return MyStyle_WC_Interface Returns the value of template_id.
		 * @codingStandardsIgnoreStart (ignoring incorrect case function name).
		 */
		public function get_WC() {
			// @codingStandardsIgnoreEnd
			return $this->wc;
		}

		/**
		 * Gets the current MyStyle_Session.
		 *
		 * This is just a shortcut to make it easier to get the current session.
		 *
		 * @return MyStyle_Session Returns the current MyStyle_Session.
		 */
		public function get_session() {
			return MyStyle_SessionHandler::get_instance()->get();
		}

		/**
		 * Gets the singleton instance.
		 *
		 * @return MyStyle Returns the singleton instance of
		 * this class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * What type of request is this?
		 *
		 * @param  string $type admin, ajax, cron or frontend.
		 * @return bool
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return defined( 'DOING_AJAX' );
				case 'cron':
					return defined( 'DOING_CRON' );
				case 'frontend':
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) &&
						( ! defined( 'DOING_CRON' ) ) &&
						( ! defined( 'DOING_PHPUNIT' ) );
			}
		}

	}

	endif;

/**
 * Main instance of MyStyle.
 *
 * Returns the main instance of MyStyle to prevent the need to use globals.
 *
 * @return MyStyle
 * @codingStandardsIgnoreStart (ignoring incorrect case function name).
 */
function MyStyle() {
	// @codingStandardsIgnoreEnd
	return MyStyle::get_instance();
}

// Init the MyStyle singleton.
MyStyle();