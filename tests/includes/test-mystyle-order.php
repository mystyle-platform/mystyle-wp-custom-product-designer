<?php

require_once( MYSTYLE_PATH . '../woocommerce/woocommerce.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-woocommerce-cart.php' );
require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php' );

/**
 * The MyStyle_OrderTest class includes tests for testing the MyStyle_Order class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleOrderTest extends WP_UnitTestCase {
    
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
        $mystyle_order = new MyStyle_Order();
        
        global $wp_filter;
        
        //Assert that the filter_cart_item_product function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_order_item_product'] );
        $this->assertContains( 'filter_order_item_product', $function_names );
        
        //Assert that the add_mystyle_order_item_meta function is registered.
        $function_names = get_function_names( $wp_filter['woocommerce_add_order_item_meta'] );
        $this->assertContains( 'add_mystyle_order_item_meta', $function_names );
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
     * Test the filter_order_item_product function.
     */
    public function test_filter_order_item_product( ) {
        //Create a design
        $result_object = new MyStyle_MockDesignQueryResult( 1 );
        $design = MyStyle_Design::create_from_result_object( $result_object );
        
        //Persist the design
        MyStyle_DesignManager::persist( $design );
        
        //Create a VARIABLE product
        $product = new WC_Product_Variable( 1 );
        
        //create the cart item data
        $cart_item = array(
            'mystyle_data' => serialize( 
                    array(
                        'design_id' => $design->get_design_id()
                    )
                )
        );
        
        //call the function
        $ret_product = MyStyle_Order::get_instance()->filter_order_item_product( 
                                        $product, 
                                        $cart_item
                                    );
        
        $this->assertEquals( 'MyStyle_Product', get_class( $ret_product ) );
    }
    
    /**
     * Test the add_mystyle_order_item_meta function.
     * @todo Get this test to work.  See the notes in Google Docs.
     */
    public function test_add_mystyle_order_item_meta() {
         //TODO
    }
    
}
