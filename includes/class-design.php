<?php

/**
 * MyStyle Design class. 
 * 
 * The MyStyle Design class represents a design in the MyStyle system.
 * 
 * TODO: Write tests for this class.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle_Design {
    
    private $description;
    private $print_url;
    private $web_url;
    private $thumb_url;
    private $design_url;
    private $design_id;
    private $template_id; //this is the MyStyle product id
    private $product_id; //this is the local product id
    private $user_id;
    private $price;
    
    /**
     * Constructor. Note: see the functions below for additional ways to create
     * a Design.
     */
    public function __construct() {
        //
    }
    
    /**
     * Static function to create a new Design from POST data. Call using 
     * MyStyle_Design::create_from_post($post_data);
     * @param array $post_data POST data to be used to construct the Design.
     * @return \self Works like a constructor.
     */
    public static function create_from_post($post_data) {
        $instance = new self();
        
        $instance->description = htmlspecialchars($post_data["description"]);
        $instance->design_id = htmlspecialchars($post_data["design_id"]);
        $instance->template_id = htmlspecialchars($post_data["product_id"]); //mapping product_id to template_id
        $instance->product_id = htmlspecialchars($post_data["local_product_id"]); //mapping local_product_id to product_id
        $instance->user_id = htmlspecialchars($post_data["user_id"]);
        $instance->price = htmlspecialchars($post_data["price"]);
        
        return $instance;
    }
    
    /**
     * Static function to create a new Design from meta data. Call using 
     * MyStyle_Design::create_from_meta($meta_data);
     * @param array $meta_data Meta data to be used to construct the Design. This
     * is an array of fields values (see the get_meta() function below).
     * @return \self Works like a constructor.
     */
    public static function create_from_meta($meta_data) {
        $instance = new self();
        
        $instance->description = htmlspecialchars($meta_data["description"]);
        $instance->print_url = htmlspecialchars($meta_data["print_url"]);
        $instance->web_url = htmlspecialchars($meta_data["web_url"]);
        $instance->thumb_url = htmlspecialchars($meta_data["thumb_url"]);
        $instance->design_url = htmlspecialchars($meta_data["design_url"]);
        $instance->design_id = htmlspecialchars($meta_data["design_id"]);
        $instance->template_id = htmlspecialchars($meta_data["template_id"]);
        $instance->product_id = htmlspecialchars($meta_data["product_id"]);
        $instance->user_id = htmlspecialchars($meta_data["user_id"]);
        $instance->price = htmlspecialchars($meta_data["price"]);
        
        return $instance;
    }
    
    /**
     * Method to add data received from the api call to the Design.
     * @param array $api_data API data to be used to add more data to the 
     * Design. This is an array of fields values (see the API docs for details).
     */
    public function add_api_data($api_data) {
        $this->print_url = htmlspecialchars($api_data["print_url"]);
        $this->web_url = htmlspecialchars($api_data["web_url"]);
        $this->thumb_url = htmlspecialchars($api_data["thumb_url"]);
        $this->design_url = htmlspecialchars($api_data["design_url"]);
    }
    
    /**
     * Sets the value of description.
     * @param string $description The new value for description.
     */
    public function set_description($description) {
        $this->description = $description;
    }
    
    /**
     * Gets the value of description.
     * @return string Returns the value of description.
     */
    public function get_description() {
        return $this->description;
    }
    
    /**
     * Sets the value of print_url.
     * @param string $print_url The new value for print_url.
     */
    public function set_print_url($print_url) {
        $this->print_url = $print_url;
    }
    
    /**
     * Gets the value of print_url.
     * @return string Returns the value of print_url.
     */
    public function get_print_url() {
        return $this->print_url;
    }
    
    /**
     * Sets the value of web_url.
     * @param string $web_url The new value for web_url.
     */
    public function set_web_url($web_url) {
        $this->web_url = $web_url;
    }
    
    /**
     * Gets the value of web_url.
     * @return string Returns the value of web_url.
     */
    public function get_web_url() {
        return $this->web_url;
    }
    
    /**
     * Sets the value of thumb_url.
     * @param string $thumb_url The new value for thumb_url.
     */
    public function set_thumb_url($thumb_url) {
        $this->thumb_url = $thumb_url;
    }
    
    /**
     * Gets the value of thumb_url.
     * @return string Returns the value of thumb_url.
     */
    public function get_thumb_url() {
        return $this->thumb_url;
    }
    
    /**
     * Sets the value of design.
     * @param string $design_url The new value for design_url.
     */
    public function set_design_url($design_url) {
        $this->design_url = $design_url;
    }
    
    /**
     * Gets the value of design_url.
     * @return string Returns the value of design_url.
     */
    public function get_design_url() {
        return $this->design_url;
    }
    
    /**
     * Sets the value of design_id.
     * @param number $design_id The new value for design_id.
     */
    public function set_design_id($design_id) {
        $this->design_id = $design_id;
    }
    
    /**
     * Gets the value of design_id.
     * @return number Returns the value of design_id.
     */
    public function get_design_id() {
        return $this->design_id;
    }
    
    /**
     * Sets the value of template_id.
     * @param number $template_id The new value for template_id.
     */
    public function set_template_id($template_id) {
        $this->template_id = $template_id;
    }
    
    /**
     * Gets the value of template_id.
     * @return number Returns the value of template_id.
     */
    public function get_template_id() {
        return $this->template_id;
    }
    
    /**
     * Sets the value of product_id.
     * @param number $product_id The new value for product_id.
     */
    public function set_product_id($product_id) {
        $this->product_id = $product_id;
    }
    
    /**
     * Gets the value of product_id.
     * @return number Returns the value of product_id.
     */
    public function get_product_id() {
        return $this->product_id;
    }
    
    /**
     * Sets the value of user_id.
     * @param number $user_id The new value for user_id.
     */
    public function set_user_id($user_id) {
        $this->user_id = $user_id;
    }
    
    /**
     * Gets the value of user_id.
     * @return number Returns the value of user_id.
     */
    public function get_user_id() {
        return $this->user_id;
    }
    
    /**
     * Sets the value of price.
     * @param number $price The new value for price.
     */
    public function set_price($price) {
        $this->price = $price;
    }
    
    /**
     * Gets the value of price.
     * @return number Returns the value of price.
     */
    public function get_price() {
        return $this->price;
    }
    
    /**
     * Function for converting the object into an array for use with WP meta
     * storage.
     * @return array Returns an array of the data from the class.
     */
    public function get_meta() {
        $meta = array();
        
        $meta['description'] = $this->description;
        $meta['print_url'] = $this->print_url;
        $meta['web_url'] = $this->web_url;
        $meta['thumb_url'] = $this->thumb_url;
        $meta['design_url'] = $this->design_url;
        $meta['design_id'] = $this->design_id;
        $meta['template_id'] = $this->template_id;
        $meta['product_id'] = $this->product_id;
        $meta['user_id'] = $this->user_id;
        $meta['price'] = $this->price;
        
        return $meta;
    }


}


