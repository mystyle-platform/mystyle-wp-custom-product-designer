<?php

/**
 * MyStyle Designer class. 
 * 
 * The MyStyle Designer class represents a designer in the MyStyle system. A
 * designer is someone who created a design.  They may also be a WordPress user
 * but don't have to be. If we know their WordPress user id, we try to include
 * it here.
 *
 * @package MyStyle
 * @since 1.2.0
 * @todo add unit testing
 */
class MyStyle_Designer implements MyStyle_Entity {
    
    private static $TABLE_NAME = 'mystyle_designers'; //Note: this is without the db prefix;
    private static $PRIMARY_KEY = 'ms_user_id';
    
    private $designer_id; //the primary key (the mystyle user id from the API)
    private $created; //the date the design was created
    private $created_gmt; //the date the design was created (adjusted to the GMT timezone).
    private $modified; //the date the design was last modified 
    private $modified_gmt; //the date the design was last modified (adjusted to the GMT timezone).
    private $user_id; //(optional) the wordpress user id (if we know it)
    private $email; //the email address of the user from the MyStyle API
    
    /**
     * Constructor. Note: see the functions below for additional ways to create
     * a Design.
     */
    public function __construct() {
        $this->created = date(MyStyle::$STANDARD_DATE_FORMAT);
        $this->created_gmt = gmdate(MyStyle::$STANDARD_DATE_FORMAT);
        $this->modified = date(MyStyle::$STANDARD_DATE_FORMAT);
        $this->modified_gmt = date(MyStyle::$STANDARD_DATE_FORMAT);
    }
    
    /**
     * Method to create a Designer.
     * @param array $api_data API data to be used to add more data to the 
     * Designer. This is an array of fields values (see the API docs for details).
     */
    public function create( $designer_id, $email, $user_id = null ) {
        $instance = new self();
        
        $instance->designer_id = htmlspecialchars( $designer_id );
        $instance->email = htmlspecialchars( $email );
        $instance->user_id = htmlspecialchars( $user_id );
        
        return $instance;
    }
    
    /**
     * Static function to create a new Designer from a WP result object. Call 
     * using MyStyle_Designer::create_from_result_object($result_object);  This
     * function should correspond with the get_data_array() function below.
     * @param array $result_object A WP row result object to be used to 
     * construct the Designer. This is an object with public fields that
     * correspond to the column names from the database.
     * @return \self Works like a constructor.
     */
    public static function create_from_result_object( $result_object ) {
        $instance = new self();
        
        //var_dump( $result_object );
        
        $instance->designer_id = (int) htmlspecialchars( $result_object->ms_user_id );
        $instance->created = htmlspecialchars( $result_object->designer_created );
        $instance->created_gmt = htmlspecialchars( $result_object->designer_created_gmt );
        $instance->modified = htmlspecialchars( $result_object->designer_modified );
        $instance->modified_gmt = htmlspecialchars( $result_object->designer_modified_gmt );
        $instance->user_id = (int) htmlspecialchars( $result_object->user_id );
        $instance->email = htmlspecialchars( $result_object->ms_email );
        
        return $instance;
    }
    
    /**
     * Sets the value of designer_id. This is used primarily by our unit tests.
     * @param number $designer_id The new value for design_id.
     */
    public function set_designer_id( $designer_id ) {
        $this->designer_id = $designer_id;
    }
    
    /**
     * Gets the value of designer_id.
     * @return number Returns the value of designer_id.
     */
    public function get_designer_id() {
        return $this->designer_id;
    }
    
    /**
     * Gets the value of created.
     * @return number Returns the value of created.
     */
    public function get_created() {
        return $this->created;
    }
    
    /**
     * Gets the value of created_gmt.
     * @return number Returns the value of created_gmt.
     */
    public function get_created_gmt() {
        return $this->created_gmt;
    }
    
    /**
     * Gets the value of modified.
     * @return number Returns the value of modified.
     */
    public function get_modified() {
        return $this->modified;
    }
    
    /**
     * Gets the value of modified_gmt.
     * @return number Returns the value of modified_gmt.
     */
    public function get_modified_gmt() {
        return $this->modified_gmt;
    }
    
    /**
     * Sets the value of user_id.
     * @param string $user_id The new value for user_id.
     */
    public function set_user_id( $user_id ) {
        $this->user_id = $user_id;
    }
    
    /**
     * Gets the value of user_id.
     * @return string Returns the value of user_id.
     */
    public function get_user_id() {
        return $this->user_id;
    }
    
    /**
     * Sets the value of email.
     * @param string $email The new value for email.
     */
    public function set_email( $email ) {
        $this->email = $email;
    }
    
    /**
     * Gets the value of email.
     * @return string Returns the value of email.
     */
    public function get_email() {
        return $this->email;
    }
    
    /**
     * Gets the SQL schema for creating the datbase table
     * @global wpdb $wpdb
     * @return string Returns a string containing SQL schema for creating the
     * table.
     */
    public static function get_schema() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$TABLE_NAME;
        return "
            CREATE TABLE $table_name (
                ms_user_id bigint(32) NOT NULL,
                designer_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                designer_created_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                designer_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                designer_modified_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                user_id bigint(20) NULL,
                ms_email varchar(255) NULL,
                PRIMARY KEY  (ms_user_id)
            )";
    }
    
    /**
     * Returns the table name for storing designs.
     * @global type $wpdb
     * @return string Returns the table name for storing designs.
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
     */
    public function get_data_array() {
        $data = array();
        
        $data['ms_user_id'] = $this->designer_id;
        $data['designer_created'] = $this->created;
        $data['designer_created_gmt'] = $this->created_gmt;
        $data['designer_modified'] = $this->modified;
        $data['designer_modified_gmt'] = $this->modified_gmt;
        $data['user_id'] = $this->user_id;
        $data['ms_email'] = $this->email;
        
        return $data;
    }
    
    /**
     * Gets the insert format for the entity. This matches up with the 
     * get_data_array() function.
     * See https://codex.wordpress.org/Class_Reference/wpdb#INSERT_rows
     * @return (array|string)
     */
    public function get_insert_format() {
        
        $formats_arr = array( 
            '%d', //ms_user_id
            '%s', //designer_created
            '%s', //designer_created_gmt
            '%s', //designer_modified
            '%s', //designer_modified_gmt
            '%d', //user_id
            '%s', //email
	);
                
        return $formats_arr;  
    }

}
