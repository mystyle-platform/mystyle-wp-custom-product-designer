<?php
/**
 * Class for rendering the MyStyle contextual help within the WordPress
 * Administrator.
 *
 * @package MyStyle
 * @since 0.1.0
 */

/**
 * MyStyle_Help class.
 */
class MyStyle_Help {

	/**
	 * Class instance.
	 *
	 * @var MyStyle_Help
	 */
	private static $instance;

	/**
	 * Constructor, constructs the class and registers filters and actions.
	 */
	public function __construct() {
		add_action( 'current_screen', array( &$this, 'add_help' ), 50 );
	}

	/**
	 * Add help for MyStyle into the WordPress admin help system.
	 *
	 * @global $mystyle_hook
	 */
	public function add_help() {
		global $mystyle_hook;

		$screen = get_current_screen();

		// Return the contextual help unaltered if this isn't one of our pages.
		if (
			( ! $screen )
			|| (
					( substr( $screen->id, 0, strlen( $mystyle_hook ) ) !== $mystyle_hook )
					&& ( 'toplevel_page_' . $mystyle_hook !== $screen->id )
				)
		) {
			return;
		}

		$overview_content = '
		<h1>MyStyle Custom Product Designer Help</h1>
		<p>Need help using the MyStyle Custom Product Designer plugin? Use the tabs
		   to the left to find instructions for installation, use and
		   troubleshooting.
		</p>';

		$installation_content = '
		<h2>Installation/Configuration</h2>
		<p>
			The MyStyle Custom Product Designer is a simple plugin that allows your
			customers to customize your WooCommerce products.
		</p>
		<ol>
			<li>Install the plugin.</li>
			<li>Activate the plugin.</li>
			<li>Create an account at <a href="http://www.mystyleplatform.com/apply-mystyle-license-developer-account-api-key-secret/?ref=wp_plugin_4" target="_blank" title="mystyleplatform.com">mystyleplatform.com</a>.</li>
			<li>Get your MyStyle API Key and Secret and add them to the fields on this page.</li>
			<li>Install the WooCommerce Plugin (if not already installed).</li>
			<li>Create a new WooCommerce product or edit an existing one.</li>
			<li>In the product options, click on the MyStyle tab.</li>
			<li>Enable Customization for the product and enter the product\'s
				MyStyle Template Id.
			</li>
			<li>The product should now have a "Customize" button that takes the
			user to the Customize page.</li>
		</ol>
		';

		$shortcodes_content = '
		<h2>Shortcodes</h2>
		<p>
			The MyStyle Custom Product Designer adds several WordPress
			<a href="https://codex.wordpress.org/shortcode" target="_blank" title="Shortcodes">shortcodes</a>.
			These shortcodes can be inserted within your content, theme files,
			widgets, etc.
		</p>
		<h3>The [mystyle_design] Shortcode</h3>
		<p>
			The [mystyle_design] Shortcode adds a MyStyle design to the page.
		</p>
		<h4>Available attributes</h4>
		<ul>
			<li>
				<strong>gallery:</strong> Set gallery equal to 1
				(ex: [mystyle_design gallery=1]) to have a gallery of designs
				displayed. The gallery is also displayed if no design id is passed.
			</li>
			<li>
				<strong>design_id:</strong> Set the design_id attribute to the id of
				the design that you want to display. This attribute is ignored if
				gallery mode is turned on (see above). Note that the shortcode can
				also retrieve the design_id from the URL (as described below).
			</li>
			<li>
				<strong>count:</strong> Used with gallery mode. Use count to specify
				how many designs to show
				(example: [mystyle-design gallery=1 count=6]). Default is 10.
			</li>
			<li>
				<strong>total:</strong> Synonym for count.
			</li>
			<li>
				<strong>tag:</strong> Used with gallery mode. Pass to only show
				designs with the provided tag
				(example: [mystyle-design gallery=1 tag="anime"]).
			</li>
		</ul>
		<h4>Available Query Params:</h4>
		<ul>
			<li>
				<strong>design_id:</strong> The id of the design to show. The
				design_id can be passed to the shortcode either through a shortcode
				attribute (as described above) or via a query param
				(example: "http://www.example.com/somepage?design_id=123"). Note
				that the design_id parameter is automatically added to the
				Alternate Design Complete URL (see the Advanced Settings).
			</li>
		</ul>
		<h3>The [mystyle_design_tags] Shortcode</h3>
		<p>
			The [mystyle_design_tags] Shortcode adds a pageable list of design
			tags with the designs that have the tag to the page.
		</p>
		<h4>Available attributes</h4>
		<p>(none)</p>
		<h4>Available Query Params:</h4>
		<ul>
			<li>
				<strong>pager:</strong> The page to show
				(example: "http://www.example.com/somepage?pager=2").
			</li>
		</ul>
		';

		$troubleshooting_content = '
		<h2>Troubleshooting</h2>
		<p>If the plugin isn\'t working, please check the following:</p>
		<ul>
			<li>Make sure that the MyStyle plugin is installed and activated.</li>
			<li>Make sure that you have entered your MyStyle API Key and Secret
			on the MyStyle settings page.</li>
			<li>Make sure that WooCommerce is installed.</li>
			<li>Make sure that you have enabled customization on at least one of
			your WooCommerce products and that you have set the product\'s template
			id.</li>
		</ul>
		<p>
		Please <a href="http://www.mystyleplatform.com/contact/" target="_blank" title="contact us">contact us</a> for additional support.
		</p>
		';

		$sidebar_content = '
		<h5>For more Information:</strong></h5>
		<a href="http://www.mystyleplatform.com/mystyle-personalization-plugin-wordpress-woo-commerce/" target="_blank" title="mystyleplatform.com">mystyleplatform.com</a><br/>
		';

		// Overview tab.
		$screen->add_help_tab(
			array(
				'id'      => 'mystyle_overview',
				'title'   => 'Overview',
				'content' => $overview_content,
			)
		);
		// Installation tab.
		$screen->add_help_tab(
			array(
				'id'      => 'mystyle_installation',
				'title'   => 'Installation',
				'content' => $installation_content,
			)
		);
		// Shortcodes tab.
		$screen->add_help_tab(
			array(
				'id'      => 'mystyle_shortcodes',
				'title'   => 'Shortcodes',
				'content' => $shortcodes_content,
			)
		);
		// Troubleshooting tab.
		$screen->add_help_tab(
			array(
				'id'      => 'mystyle_troubleshooting',
				'title'   => 'Troubleshooting',
				'content' => $troubleshooting_content,
			)
		);

		$screen->set_help_sidebar( $sidebar_content );
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return MyStyle_Help Returns the singleton instance of this
	 * class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
