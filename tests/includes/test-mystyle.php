<?php

require_once( MYSTYLE_PATH . '../woocommerce/woocommerce.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-design.php' );

/**
 * The MyStyleClassTest class includes tests for testing the MyStyle
 * class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleClassTest extends WP_UnitTestCase {

    /**
     * Overrwrite the setUp function so that our custom tables will be persisted
     * to the test database.
     */
    function setUp() {
        // Perform the actual task according to parent class.
        parent::setUp();
        // Remove filters that will create temporary tables. So that permanent tables will be created.
        remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
        remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
        
        //Create the tables
        MyStyle_Install::create_tables();
    }
    
    /**
     * Overrwrite the tearDown function to remove our custom tables.
     */
    function tearDown() {
        global $wpdb;
        // Perform the actual task according to parent class.
        parent::tearDown();
        
        //Drop the tables that we created
        $wpdb->query("DROP TABLE IF EXISTS " . MyStyle_Design::get_table_name());
    }
    
    /**
     * Mock the mystyle_metadata
     * @param type $metadata
     * @param type $object_id
     * @param type $meta_key
     * @param type $single
     * @return string
     */
    function mock_mystyle_metadata( $metadata, $object_id, $meta_key, $single ){
        return 'yes';
    }
    
    /**
     * Test the constructor
     */    
    public function test_constructor() {
        $mystyle = new MyStyle();
        
        global $wp_filter;
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['init'] );
        $this->assertContains( 'init', $function_names );
    }
    
    /**
     * Test the site_has_customizable_products function returns true when
     * customizable products exist.
     */
    public function test_site_has_customizable_products_returns_false_when_customizable_products_dont_exist() {
        //create a regular test product
        $product_id = create_wc_test_product('Test Product');
        
        //call the function
        $result = MyStyle::site_has_customizable_products();
        
        $this->assertFalse( $result );
    }
    
    
    /**
     * Test the site_has_customizable_products function returns true when
     * customizable products exist.
     */
    public function test_site_has_customizable_products_returns_true_when_customizable_products_exist() {
        //create a regular test product
        $product_id = create_wc_test_product('Test Product');
        
        //enable mystyle for the product
        update_post_meta( $product_id, '_mystyle_enabled', 'yes' );
        
        //call the function
        $result = MyStyle::site_has_customizable_products();
        
        $this->assertTrue( $result );
    }
    
    /**
     * Test the product_is_customizable function when product isn't mystyle
     * enabled.
     */    
    public function test_product_is_customizable_returns_false_when_product_not_mystyle_enabled() {
        global $product;
        
        //Create a mock product using the mock Post
        $product = create_test_product();
        $GLOBALS['post'] = $product;
        
        $is_customizable = MyStyle::product_is_customizable( $product->id );
        
        //Assert that is_customizable is false
        $this->assertFalse( $is_customizable );
    }
    
    /**
     * Test the product_is_customizable function when product is mystyle
     * enabled.
     */    
    public function test_product_is_customizable_returns_true_when_product_is_mystyle_enabled() {
        global $product;
        
        //Mock the global $post variable
        $product = create_test_product();
        $GLOBALS['post'] = $product;
        
        //Create a mock product using the mock Post
        $product = new WC_Product_Simple($GLOBALS['post']);
        
        //Mock the mystyle_metadata
        add_filter('get_post_metadata', array( &$this, 'mock_mystyle_metadata' ), true, 4);
        
        $is_customizable = MyStyle::product_is_customizable( $product->id );
        
        //Assert that is_customizable is true
        $this->assertTrue( $is_customizable );
    }
    
}
