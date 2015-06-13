<?php

/**
 * MyStyle Design class. 
 * 
 * The MyStyle Design class represents a design in the MyStyle system.
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyle_Design implements MyStyle_Entity {
    
    private static $TABLE_NAME = 'mystyle_designs'; //Note: this is without the db prefix;
    private static $PRIMARY_KEY = 'ms_design_id';
    
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
    public static function create_from_post( $post_data ) {
        $instance = new self();
        
        $instance->description = htmlspecialchars( $post_data['description'] );
        $instance->design_id = (int) htmlspecialchars( $post_data['design_id'] );
        $instance->template_id = (int) htmlspecialchars( $post_data['product_id'] ); //mapping product_id to template_id
        $instance->product_id = (int) htmlspecialchars( $post_data['local_product_id'] ); //mapping local_product_id to product_id
        $instance->user_id = (int) htmlspecialchars( $post_data['user_id'] );
        $instance->price = (int) htmlspecialchars( $post_data['price'] );
        
        return $instance;
    }
    
    /**
     * Static function to create a new Design from a WP result object. Call 
     * using MyStyle_Design::create_from_result_object($result_object);  This
     * function should correspond with the get_data_array() function below.
     * @param array $result_object A WP row result object to be used to 
     * construct the Design. This is an object with public fields that
     * correspond to the column names from the database.
     * @return \self Works like a constructor.
     */
    public static function create_from_result_object( $result_object ) {
        $instance = new self();
        
        //var_dump( $result_object );
        
        $instance->design_id = (int) htmlspecialchars( $result_object->ms_design_id );
        $instance->template_id = (int) htmlspecialchars( $result_object->ms_product_id );
        $instance->user_id = (int) htmlspecialchars( $result_object->ms_user_id );
        $instance->description = htmlspecialchars( $result_object->ms_description );
        $instance->price = (int) htmlspecialchars( $result_object->ms_price );
        $instance->print_url = htmlspecialchars( $result_object->ms_print_url );
        $instance->web_url = htmlspecialchars( $result_object->ms_web_url );
        $instance->thumb_url = htmlspecialchars( $result_object->ms_thumb_url );
        $instance->design_url = htmlspecialchars( $result_object->ms_design_url );
        $instance->product_id = (int) htmlspecialchars( $result_object->product_id );
        
        return $instance;
    }
    
    /**
     * Method to add data received from the database to the Design.
     * @param array $api_data API data to be used to add more data to the 
     * Design. This is an array of fields values (see the API docs for details).
     */
    public function add_query_data( $query_data ) {
        $this->print_url = htmlspecialchars( $api_data['print_url'] );
        $this->web_url = htmlspecialchars( $api_data['web_url'] );
        $this->thumb_url = htmlspecialchars( $api_data['thumb_url'] );
        $this->design_url = htmlspecialchars( $api_data['design_url'] );
    }
    
    
    /**
     * Method to add data received from the api call to the Design.
     * @param array $api_data API data to be used to add more data to the 
     * Design. This is an array of fields values (see the API docs for details).
     */
    public function add_api_data( $api_data ) {
        $this->print_url = htmlspecialchars( $api_data['print_url'] );
        $this->web_url = htmlspecialchars( $api_data['web_url'] );
        $this->thumb_url = htmlspecialchars( $api_data['thumb_url'] );
        $this->design_url = htmlspecialchars( $api_data['design_url'] );
    }
    
    /**
     * Sets the value of description.
     * @param string $description The new value for description.
     */
    public function set_description( $description ) {
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
    public function set_print_url( $print_url ) {
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
    public function set_web_url( $web_url ) {
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
    public function set_thumb_url( $thumb_url ) {
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
    public function set_design_url( $design_url ) {
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
    public function set_design_id( $design_id ) {
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
    public function set_template_id( $template_id ) {
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
    public function set_product_id( $product_id ) {
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
    public function set_user_id( $user_id ) {
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
    public function set_price( $price ) {
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
     * @return array Returns an array for storage as WP meta data.
     */
    public function get_meta() {
        $meta = array();
        
        $meta['design_id'] = $this->design_id;
        
        return $meta;
    }
    
    /**
     * Gets the SQL schema for creating the datbase table
     * @global wpdb $wpdb
     * @return string Returns a string containing SQL schema for creating the
     * table.
     * @todo Add unit testing
     */
    public static function get_schema() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$TABLE_NAME;
        return "
            CREATE TABLE $table_name (
                ms_design_id bigint(32) NOT NULL,
                ms_product_id bigint(20) NOT NULL,
                ms_user_id bigint(20) NULL,
                ms_description text NULL,
                ms_price numeric(15,2) NULL,
                ms_print_url varchar(255) NULL,
                ms_web_url varchar(255) NULL,
                ms_thumb_url varchar(255) NULL,
                ms_design_url varchar(255) NULL,
                product_id bigint(20) NULL,
                PRIMARY KEY  (ms_design_id)
            )";
    }
    
    /**
     * Returns the table name for storing designs.
     * @global type $wpdb
     * @return string Returns the table name for storing designs.
     * @todo Add unit testing
     */
    public static function get_table_name() {
        global $wpdb;
        
        return $wpdb->prefix . self::$TABLE_NAME;
    }
    
    /**
     * Gets the name of the primary key column.
     * @return string Returns the name of the primary key column for the table.
     */
    public static function get_primary_key() {
        return self::$PRIMARY_KEY;
    }
    
    /**
     * Gets the entity data to insert into the table.
     * @return array Data to insert (in column => value pairs)
     * @todo Add unit testing
     */
    public function get_data_array() {
        $data = array();
        
        $data['ms_design_id'] = $this->design_id;
        $data['ms_product_id'] = $this->template_id;
        $data['ms_user_id'] = $this->user_id;
        $data['ms_description'] = $this->description;
        $data['ms_price'] = $this->price;
        $data['ms_print_url'] = $this->print_url;
        $data['ms_web_url'] = $this->web_url;
        $data['ms_thumb_url'] = $this->thumb_url;
        $data['ms_design_url'] = $this->design_url;
        $data['product_id'] = $this->product_id;
        
        return $data;
    }
    
    /**
     * Gets the insert format for the entity. This matches up with the 
     * get_data_array() function.
     * See https://codex.wordpress.org/Class_Reference/wpdb#INSERT_rows
     * @return (array|string)
     * @todo Add unit testing
     */
    public function get_insert_format() {
        
        $formats_arr = array( 
            '%d', 
            '%d',
            '%d',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
	);
                
        return $formats_arr;  
    }

}
