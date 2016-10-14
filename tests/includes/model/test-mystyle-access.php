<?php

/**
 * The MyStyleAccessTest class includes tests for testing the MyStyle_Access
 * class.
 *
 * @package MyStyle
 * @since 1.5.0
 */
class MyStyleAccessTest extends WP_UnitTestCase {

    /**
     * Test the properties of the class
     */    
    function test_public_properties() {
        //Assert that public access property exists, and is as expected
        $this->assertEquals( 0, MyStyle_Access::$PUBLIC );
        
        //Assert that private access property exists, and is as expected
        $this->assertEquals( 1, MyStyle_Access::$PRIVATE );
        
        //Assert that restricted access property exists, and is as expected
        $this->assertEquals( 2, MyStyle_Access::$RESTRICTED );
    }
    
}
