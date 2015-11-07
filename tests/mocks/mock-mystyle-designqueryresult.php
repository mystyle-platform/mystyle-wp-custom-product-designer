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
    
    /**
     * Constructor
     * @param integer $design_id An id for the design.
     */
    public function __construct( $design_id ) {
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
    }

}
