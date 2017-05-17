<?php

//require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designqueryresult.php' );
//require_once( MYSTYLE_PATH . 'tests/mocks/mock-mystyle-design.php' );

/**
 * The MyStyleProductTest class includes tests for testing the MyStyle_Product
 * class.
 *
 * @package MyStyle
 * @since 2.0
 */
class MyStyleProductTest extends WP_UnitTestCase {

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
        
        //Instantiate the MyStyle and MyStyle_WC object.
        MyStyle::get_instance()->set_WC( new MyStyle_WC() );
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
     * Test the product_is_customizable function when product isn't mystyle
     * enabled.
     */    
    public function test_product_is_customizable_returns_false_when_product_not_mystyle_enabled() {
        
        //Create a product
        $product_id = create_wc_test_product();
        $product = new \WC_Product_Simple( $product_id );
        $mystyle_product = new \MyStyle_Product( $product );
        
        //call the function
        $is_customizable = $mystyle_product->is_customizable();
        
        //Assert that is_customizable is false
        $this->assertFalse( $is_customizable );
    }
    
    /**
     * Test the product_is_customizable function when product is mystyle
     * enabled.
     */    
    public function test_product_is_customizable_returns_true_when_product_is_mystyle_enabled() {
        
        //Create a product
        $product_id = create_wc_test_product();
        $product = new \WC_Product_Simple( $product_id );
        $mystyle_product = new \MyStyle_Product( $product );
        
        add_post_meta($product_id, "_mystyle_enabled", "yes");
        
        //call the function
        $is_customizable = $mystyle_product->is_customizable();
        
        //Assert that is_customizable is true
        $this->assertTrue( $is_customizable );
    }
    
}
