<?php

/**
 * The MyStylePagerTest class includes tests for testing the MyStyle_Pager
 * class.
 *
 * @package MyStyle
 * @since 1.5.0
 */
class MyStylePagerTest extends WP_UnitTestCase {

	/**
	 * Test the constructor.
	 */
	function test_constructor() {
		$pager = new MyStyle_Pager();

		//Assert that the pager is constructed
		$this->assertEquals('MyStyle_Pager', get_class($pager));
	}

	/**
	 * Test the get_start function.
	 */
	function test_get_start() {
		//configure the pager
		$pager = new MyStyle_Pager();
		$pager->set_items_per_page(5);
		$pager->set_current_page_number(2);

		//call the function
		$start = $pager->get_start();

		//Assert that the function returns the expected value
		$this->assertEquals(5, $start);
	}

	/**
	 * Test the get_page_count function.
	 */
	function test_get_page_count() {
		//configure the pager
		$pager = new MyStyle_Pager();
		$pager->set_items_per_page(5);
		$pager->set_total_item_count(50);

		//call the function
		$page_count = $pager->get_page_count();

		//Assert that the function returns the expected value
		$this->assertEquals(10, $page_count);
	}

	/**
	 * Test that the validate function returns without error for a valid page
	 * request.
	 */
	function test_validate_for_valid_page() {
		//configure the pager
		$pager = new MyStyle_Pager();
		$pager->set_items_per_page(5);
		$pager->set_total_item_count(50);
		$pager->set_current_page_number(10);

		$exception_thrown = false;

		try {
			//call the function
			$pager->validate();
			$exception_thrown = false;
		} catch (Exception $ex) {
			$exception_thrown = true;
		}

		$this->assertFalse($exception_thrown);
	}

	/**
	 * Test that the validate function throws a MyStyle_Unauthorized_Exception when
	 * accessing a non existent page.
	 */
	function test_validate_for_invalid_page() {
		$this->setExpectedException('MyStyle_Not_Found_Exception');

		//configure the pager
		$pager = new MyStyle_Pager();
		$pager->set_items_per_page(5);
		$pager->set_total_item_count(50);
		$pager->set_current_page_number(11);

		//call the function
		$pager->validate();
	}

}
