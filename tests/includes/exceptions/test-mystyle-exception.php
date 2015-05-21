<?php

require_once(MYSTYLE_INCLUDES . 'exceptions/class-mystyle-exception.php');

/**
 * The MyStyleExceptionTest class includes tests for testing the
 * MyStyle_Exception class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleExceptionTest extends WP_UnitTestCase {
    
    /**
     * Test throwing an exception
     */    
    public function test_throw_exception() {
        $this->setExpectedException('MyStyle_Exception');
        
        throw new MyStyle_Exception( 'MyStyle Exception Message', 500 );
    }
    
    /**
     * Test exception content
     */    
    public function test_exception_content() {
        $message = 'MyStyle Exception Message';
        $code = 500;
        
        try {
            throw new MyStyle_Exception( $message, $code );
        } catch( MyStyle_Exception $e ) {
            //assert that the expected code is returned
            $this->assertEquals( $code, $e->getCode() );
            
            //assert that the expected message is returned
            $this->assertEquals( $message, $e->getMessage() );
        }
    }
    
}
