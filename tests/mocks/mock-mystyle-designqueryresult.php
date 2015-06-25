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
    public $ms_description;
    public $ms_price;
    public $ms_print_url;
    public $ms_web_url;
    public $ms_thumb_url;
    public $ms_design_url;
    public $product_id;
    
    public function __construct( $design_id ) {
        $this->ms_design_id = $design_id;
        $this->ms_product_id = 0;
        $this->ms_user_id = 0;
        $this->ms_description = 'test description';
        $this->ms_price = 0;
        $this->ms_print_url = 'http://www.example.com/example.jpg';
        $this->ms_web_url = 'http://www.example.com/example.jpg';
        $this->ms_thumb_url = 'http://www.example.com/example.jpg';
        $this->ms_design_url = 'http://www.example.com/example.jpg';
        $this->product_id = 0;
    }

}
