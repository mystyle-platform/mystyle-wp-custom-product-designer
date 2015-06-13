<?php

/**
 * The MyStyleDesignTest class includes tests for testing the MyStyle_Design
 * class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleDesignTest extends WP_UnitTestCase {

    /**
     * Test the create_from_post function
     */    
    function test_create_from_post() {
        
        $design_id = 1;
        
        //Mock the POST
        $post = array();
        $post['description'] = 'test description';
        $post['design_id'] = $design_id;
        $post['product_id'] = 0;
        $post['local_product_id'] = 0;
        $post['user_id'] = 0;
        $post['price'] = 0;
        
        $design = MyStyle_Design::create_from_post($post);
        
        //Assert that the design_id is set
        $this->assertEquals( $design_id, $design->get_design_id() );
    }
    
    /**
     * Test the create_from_meta function
     */    
    function test_add_api_data() {
        
        $design = new MyStyle_Design();
        
        //Mock the api_data
        $api_data = array();
        $api_data['print_url'] = 'http://testhost/test_print_url.jpg';
        $api_data['web_url'] = 'http://testhost/test_web_url.jpg';
        $api_data['thumb_url'] = 'http://testhost/test_thumb_url.jpg';
        $api_data['design_url'] = 'http://testhost/test_design_url.jpg';
        
        $design->add_api_data($api_data);
        
        //Assert that the fields were set
        $this->assertEquals( $api_data['print_url'], $design->get_print_url() );
        $this->assertEquals( $api_data['web_url'], $design->get_web_url() );
        $this->assertEquals( $api_data['thumb_url'], $design->get_thumb_url() );
        $this->assertEquals( $api_data['design_url'], $design->get_design_url() );
    }
    
    /**
     * Test the get_meta function
     * @todo Rewrite this now that create_from_meta no longer exists
     */    
    function test_get_meta() {
        
        //Mock the meta
        /*
        $meta = array();
        $meta['description'] = 'test description';
        $meta['print_url'] = 'http://testhost/test_print_url.jpg';
        $meta['web_url'] = 'http://testhost/test_web_url.jpg';
        $meta['thumb_url'] = 'http://testhost/test_thumb_url.jpg';
        $meta['design_url'] = 'http://testhost/test_design_url.jpg';
        $meta['design_id'] = 1;
        $meta['template_id'] = 2;
        $meta['product_id'] = 3;
        $meta['user_id'] = 4;
        $meta['price'] = 5;
        
        $serialized_meta = serialize($meta);
        
        $design = MyStyle_Design::create_from_meta($meta);
        
        $export = $design->get_meta();
        
        $serialized_export = serialize($export);
        
        //Assert that the expected meta is returned
        $this->assertEquals( $serialized_meta, $serialized_export );
         */
    }

}
