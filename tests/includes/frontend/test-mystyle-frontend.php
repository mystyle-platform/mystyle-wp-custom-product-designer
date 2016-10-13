<?php

require_once( MYSTYLE_PATH . '../woocommerce/woocommerce.php' );
require_once( MYSTYLE_INCLUDES . 'frontend/class-mystyle-frontend.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce-cart.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-wc-product-variable.php' );

/**
 * The FrontEndTest class includes tests for testing the MyStyle_FrontEnd class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleFrontEndTest extends WP_UnitTestCase {
    
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
     * Test the constructor
     */    
    public function test_constructor() {
        $mystyle_frontend = new MyStyle_FrontEnd();
        
        global $wp_filter;
        
        //Assert that the init function is registered.
        $function_names = get_function_names( $wp_filter['init'] );
        $this->assertContains( 'init', $function_names );
    }
    
    /**
     * Test the mystyle_frontend_init function.
     */    
    public function test_mystyle_frontend_init() {
        global $wp_scripts;
        global $wp_styles;
        
        define( 'MYSTYLE_DESIGNS_PER_PAGE', 25 );
        
        //call the function
        MyStyle_Frontend::get_instance()->init();
        
        //Assert that our scripts are registered
        $this->assertContains( 'swfobject', serialize( $wp_scripts ) );
        
        //Assert that our stylesheets are registered
        $this->assertContains( 'myStyleFrontendStylesheet', serialize( $wp_styles ) );
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
     * Test the add_query_vars_filter function.
     */
    public function test_add_query_vars_filter( ) {
        $mystyle_frontend = new MyStyle_FrontEnd();
        
        $vars[] = array();
        
        //call the function
        $ret_vars = MyStyle_FrontEnd::get_instance()->add_query_vars_filter( $vars );
        
        $this->assertTrue( in_array( 'design_id', $ret_vars ) );
    }
    
    /**
     * Test the filter_order_item_product function.
     */
    public function test_filter_order_item_product( ) {
        //Create a design
        $result_object = new MyStyle_MockDesignQueryResult( 1 );
        $design = MyStyle_Design::create_from_result_object( $result_object );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Create a mock product (this class is mocked above).
        $product = new WC_Product_Variable();
        
        //create the cart item data
        $cart_item = array(
            'mystyle_data' => serialize( 
                    array(
                        'design_id' => $design->get_design_id()
                    )
                )
        );
        
        //call the function
        $ret_product = MyStyle_FrontEnd::get_instance()->filter_order_item_product( 
                                        $product, 
                                        $cart_item
                                    );
        
        $this->assertEquals( 'MyStyle_Product', get_class( $ret_product ) );
    }
    
    /**
     * Disable the wp_redirect function so that it returns false and doesn't
     * perform the redirect. This is used by the 
     * test_mystyle_add_to_cart_handler function below.
     * @param string $location
     * @param integer $status
     * @return type
     */
    function filter_wp_redirect( $location, $status ){
        global $filter_wp_redirect_called;
        
        $filter_wp_redirect_called = true;
    }
}
