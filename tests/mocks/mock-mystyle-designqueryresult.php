<?php

/**
 * Mocks the result of a mystyle design query.
 *
 * @package MyStyle
 * @since 1.0.0
 */
class MyStyle_MockDesignQueryResult {

	public $ms_design_id;
	public $ms_product_id;
	public $ms_user_id;
	public $ms_email;
	public $ms_description;
	public $ms_price;
	public $ms_print_url;
	public $ms_web_url;
	public $ms_thumb_url;
	public $ms_design_url;
	public $product_id;
	public $user_id;
	public $design_created;
	public $design_created_gmt;
	public $design_modified;
	public $design_modified_gmt;
	public $ms_mobile;
	public $ms_access;
	public $design_view_count;
	public $design_purchase_count;
	public $session_id;
	public $cart_data;

	/**
	 * Constructor
	 * @param integer $design_id An id for the design.
	 */
	public function __construct($design_id) {
		$this->ms_design_id = $design_id;
		$this->ms_product_id = 0;
		$this->ms_user_id = 0;
		$this->ms_email = 'someone@example.com';
		$this->ms_description = 'test description';
		$this->ms_price = 0;
		$this->ms_print_url = 'http://www.example.com/example.jpg';
		$this->ms_web_url = 'http://www.example.com/example.jpg';
		$this->ms_thumb_url = 'http://www.example.com/example.jpg';
		$this->ms_design_url = 'http://www.example.com/example.jpg';
		$this->product_id = 0;
		$this->user_id = 0;
		$this->design_created = '2015-08-06 22:35:52';
		$this->design_created_gmt = '2015-08-06 22:35:52';
		$this->design_modified = '2015-08-06 22:35:52';
		$this->design_modified_gmt = '2015-08-06 22:35:52';
		$this->ms_mobile = 0;
		$this->ms_access = 0;
		$this->design_view_count = 0;
		$this->design_purchase_count = 0;
		$this->session_id = 'testsessionid';
		$this->cart_data = null;
	}

	/**
	 * Returns an array representation of the result object.
	 * @return Returns an array representation of the result object.
	 */
	public function to_array() {
		$arr = array();

		$arr['ms_design_id'] = $this->ms_design_id;
		$arr['ms_product_id'] = $this->ms_product_id;
		$arr['ms_user_id'] = $this->ms_user_id;
		$arr['ms_email'] = $this->ms_email;
		$arr['ms_description'] = $this->ms_description;
		$arr['ms_price'] = $this->ms_price;
		$arr['ms_print_url'] = $this->ms_print_url;
		$arr['ms_web_url'] = $this->ms_web_url;
		$arr['ms_thumb_url'] = $this->ms_thumb_url;
		$arr['ms_design_url'] = $this->ms_design_url;
		$arr['product_id'] = $this->product_id;
		$arr['user_id'] = $this->user_id;
		$arr['design_created'] = $this->design_created;
		$arr['design_created_gmt'] = $this->design_created_gmt;
		$arr['design_modified'] = $this->design_modified;
		$arr['design_modified_gmt'] = $this->design_modified_gmt;
		$arr['ms_mobile'] = $this->ms_mobile;
		$arr['ms_access'] = $this->ms_access;
		$arr['design_view_count'] = $this->design_view_count = 0;
		$arr['design_purchase_count'] = $this->design_purchase_count;
		$arr['session_id'] = $this->session_id;
		$arr['cart_data'] = $this->cart_data;

		return $arr;
	}

}
