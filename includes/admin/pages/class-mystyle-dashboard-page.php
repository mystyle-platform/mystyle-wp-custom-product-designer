<?php
/**
 * MyStyle Admin Dashboard Page. Renders the MyStyle Dashboard page within the WordPress
 * Administrator.
 *
 * @package MyStyle
 * @since 3.14.5
 */

/**
 * MyStyle_Dashboard_Page class.
 */
class MyStyle_Dashboard_Page {

	/**
	 * Singleton instance.
	 *
	 * @var MyStyle_Dashboard_Page
	 */
	private static $instance;

	/**
	 * Constructor, constructs the addons page and adds it to the Settings
	 * menu.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'add_page_to_menu' ) );
        add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array( &$this, 'handle_custom_query_var' ), 10, 2 );
	}

	
    /** 
     * Add Design Tags link to admin menu
     *
     */
    public function add_page_to_menu() {
        global $mystyle_hook;
		$mystyle_hook = 'mystyle';

		add_menu_page(
			'MyStyle',
			'MyStyle',
			'manage_options',
			$mystyle_hook,
			array( &$this, 'render_page' ),
			MYSTYLE_ASSETS_URL . '/images/mystyle-icon.png',
			'56'
		);
        
		add_submenu_page(
            $mystyle_hook,
            'Dashboard', 
            'Dashboard',  
            'manage_options',  
            $mystyle_hook,
            array( &$this, 'render_page' ),
            99
        );
    
    }
    
    /**
	 * Function to render the MyStyle Dashboard page.
	 */
	public function render_page() {
        $admin_user = wp_get_current_user() ;
        $design_count = MyStyle_DesignManager::get_total_design_count( $admin_user ) ;
        $design_product_count = $this->get_total_customizable_products_count() ;
        $options = get_option( MYSTYLE_OPTIONS_NAME, array() );
		$api_key_status = ( array_key_exists( 'api_key', $options ) ) ? true : false;
        $settings_link = '<a class="alert" href="options-general.php?page=mystyle_settings">INVALID</a>';
		?>
		<div class="wrap">
			<h2 class="mystyle-admin-title">
				<span id="mystyle-icon-general" class="icon100"></span>
				MyStyle Dashboard
			</h2>

			<div class="mystyle-admin-box">
                <ul class="products">
				<li>
                    <div class="design-count">
                        <h3>Total Number of Designs</h3>
                        <p><?php print $design_count ; ?></p>
                    </div>
                </li>
                <li>
                    <div class="design-products">
                        <h3>Total Customizable Products</h3>
                        <p><?php print $design_product_count ; ?></p>
                    </div>
                </li>
                <li>
                    <div class="license-status">
                        <h3>MyStyle License Status</h3>
                        <p><?php print ( $api_key_status ? '<span class="green">VALID<spam>' : $settings_link) ; ?></p>
                    </div>
                </li>
            </div>

			<div class="mystyle-notice">
				<p><i>Need more add-ons, UIs, products, website upgrades, or services?  We have even more great things available in the <a href="http://www.mystyleplatform.com/marketplace" style="font-weight: bold;">MyStyle Marketplace</a>.</i></p>
			</div>
		</div>

		<?php
	}
    
    /**
     * Get total customizable products
     */
    public function get_total_customizable_products_count() {
        
        $args = array(
            '_mystyle_enabled'  => 'yes',
            'return' => 'ids',
            'limit' => -1,
        );
        
        $products = wc_get_products( $args );
        
        return count($products) ;
        
    }
    
    /**
     * Handle a custom '_mystyle_enabled' query var to get products with the 'customvar' meta.
     * @param array $query - Args for WP_Query.
     * @param array $query_vars - Query vars from WC_Product_Query.
     * @return array modified $query
     */
    public function handle_custom_query_var( $query, $query_vars ) {
        if ( ! empty( $query_vars['_mystyle_enabled'] ) ) {
            $query['meta_query'][] = array(
                'key' => '_mystyle_enabled',
                'value' => esc_attr( $query_vars['_mystyle_enabled'] ),
            );
        }

        return $query;
    }


	/**
	 * Get the singleton instance.
	 *
	 * @return MyStyle_Addons_Page
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
