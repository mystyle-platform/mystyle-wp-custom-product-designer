<?php

require_once(MYSTYLE_PATH . 'tests/mocks/mock-mystyle-designerqueryresult.php');

/**
 * The MyStyleDesignerTest class includes tests for testing the MyStyle_Designer
 * class.
 *
 * @package MyStyle
 * @since 1.2.0
 */
class MyStyleDesignerTest extends WP_UnitTestCase {

    /**
     * Test the create_from_post function
     */    
    function test_create() {
        
        $designer_id = 1;
        $email = 'someone@example.com';
        
        
        $designer = MyStyle_Designer::create( 
                                        $designer_id,
                                        $email 
                                    );
        
        //Assert that the designer_id is set
        $this->assertEquals( $designer_id, $designer->get_designer_id() );
    }
    
    /**
     * Test the create_from_result_object function
     */    
    function test_create_from_result_object() {
        
        $designer_id = 1;
        
        //Mock the result object
        $result_object = new MyStyle_MockDesignerQueryResult( $designer_id );
        
        $designer = MyStyle_Designer::create_from_result_object( $result_object );
        
        //Assert that the designer_id is set
        $this->assertEquals( $designer_id, $designer->get_designer_id() );
    }
    
    /**
     * Test the get_schema function
     */    
    function test_get_schema() {
        
        $expected_schema = "
            CREATE TABLE wptests_mystyle_designers (
                ms_user_id bigint(32) NOT NULL,
                designer_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                designer_created_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                designer_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                designer_modified_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                user_id bigint(20) NULL,
                ms_email varchar(255) NULL,
                PRIMARY KEY  (ms_user_id)
            )";
        
        $schema = MyStyle_Designer::get_schema();
        
        //Assert that the expected schema is returned
        $this->assertEquals( $expected_schema, $schema );
    }
    
    /**
     * Test the get_table_name function
     */    
    function test_get_table_name() {
        
        $expected_table_name = 'wptests_mystyle_designers';
        
        $table_name = MyStyle_Designer::get_table_name();
        
        //Assert that the expected table name is returned
        $this->assertEquals( $expected_table_name, $table_name );
    }
    
    /**
     * Test the get_primary_key function
     */    
    function test_get_primary_key() {
        
        $expected_primary_key = 'ms_user_id';
        
        $primary_key = MyStyle_Designer::get_primary_key();
        
        //Assert that the expected primary key is returned
        $this->assertEquals( $expected_primary_key, $primary_key );
    }

    /**
     * Test the get_data_array function
     */    
    function test_get_data_array() {
        
        $designer_id = 1;
        
        //Set up the expected data array
        $expected_data_array = array(
            'ms_user_id' => $designer_id,
            'designer_created' => '2015-08-06 22:35:52',
            'designer_created_gmt' => '2015-08-06 22:35:52',
            'designer_modified' => '2015-08-06 22:35:52',
            'designer_modified_gmt' => '2015-08-06 22:35:52',
            'user_id' => 2,
            'ms_email' => 'someone@example.com',
        );
        
        //Create a designer
        $result_object = new MyStyle_MockDesignerQueryResult( $designer_id );
        $designer = MyStyle_Designer::create_from_result_object( $result_object );
        
        //Run the function
        $data_array = $designer->get_data_array();
        
        //Assert that the expected data array is returned
        $this->assertEquals( $expected_data_array, $data_array );
    }
    
    /**
     * Test the get_insert_format function
     */    
    function test_get_insert_format() {
        
        //Set up the expected formats array
        $expected_formats_arr = array( 
            '%d', //ms_designer_id
            '%s', //designer_created
            '%s', //designer_created_gmt
            '%s', //designer_modified
            '%s', //designer_modified_gmt
            '%d', //user_id
            '%s', //ms_email
	);
        
        //Create a designer
        $result_object = new MyStyle_MockDesignerQueryResult( 1 );
        $designer = MyStyle_Designer::create_from_result_object( $result_object );
        
        //Assert that the expected data array is returned
        $this->assertEquals( $expected_formats_arr, $designer->get_insert_format() );
    }
    
}
