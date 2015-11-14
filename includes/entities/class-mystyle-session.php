<?php

/**
 * MyStyle Session class. 
 * 
 * The MyStyle Session class represents a user session for users of MyStyle
 * Customizer.
 *
 * @package MyStyle
 * @since 1.2.0
 */
class MyStyle_Session implements MyStyle_Entity {
    
    public static $SESSION_KEY = 'mystyle';
    public static $COOKIE_NAME = 'mystyle_session_id';
    
    private static $TABLE_NAME = 'mystyle_sessions'; //Note: this is without the db prefix;
    private static $PRIMARY_KEY = 'session_id';
    
    private $session_id; //the primary key. An alphanumeric string. Also the cookie.
    private $created; //the date the session was created
    private $created_gmt; //the date the session was created (adjusted to the GMT timezone).
    private $modified; //the date the session was last modified/used 
    private $modified_gmt; //the date the session was last modified/used (adjusted to the GMT timezone).
    
    /**
     * Constructor. Note: see the functions below for additional ways to create
     * a Design.
     */
    public function __construct() {
        $this->created = date( MyStyle::$STANDARD_DATE_FORMAT );
        $this->created_gmt = gmdate( MyStyle::$STANDARD_DATE_FORMAT );
        $this->modified = date( MyStyle::$STANDARD_DATE_FORMAT );
        $this->modified_gmt = gmdate( MyStyle::$STANDARD_DATE_FORMAT );
    }
    
    /**
     * Static function to create a new Session. Call using 
     * MyStyle_Session::create();
     * @return \self Works like a constructor.
     */
    public static function create( $session_id = null ) {
        $instance = new self();
        
        if( $session_id != null ) {
            $instance->session_id = $session_id;
        } else {
            $instance->session_id = self::generate_session_id();
        }
        
        return $instance;
    }
    
    /**
     * Static function to create a Session from a WP result object. Call 
     * using MyStyle_Session::create_from_result_object($result_object);  This
     * function should correspond with the get_data_array() function below.
     * @param array $result_object A WP row result object to be used to 
     * construct the Session. This is an object with public fields that
     * correspond to the column names from the database.
     * @return \self Works like a constructor.
     */
    public static function create_from_result_object( $result_object ) {
        $instance = new self();
        
        var_dump( $result_object );
        
        $instance->session_id = htmlspecialchars( $result_object->session_id );
        $instance->created = htmlspecialchars( $result_object->session_created );
        $instance->created_gmt = htmlspecialchars( $result_object->session_created_gmt );
        $instance->modified = htmlspecialchars( $result_object->session_modified );
        $instance->modified_gmt = htmlspecialchars( $result_object->session_modified );
        
        return $instance;
    }
    
    /**
     * Gets the value of session_id.
     * @return number Returns the value of session_id.
     */
    public function get_session_id() {
        return $this->session_id;
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
     * Sets the value of modified.
     * @param number $modified The new value for modified.
     */
    public function set_modified( $modified ) {
        $this->modified = $modified;
    }
    
    /**
     * Gets the value of modified.
     * @return number Returns the value of modified.
     */
    public function get_modified() {
        return $this->modified;
    }
    
    /**
     * Sets the value of modified_gmt.
     * @param number $modified_gmt The new value for modified_gmt.
     */
    public function set_modified_gmt( $modified_gmt ) {
        $this->modified_gmt = $modified_gmt;
    }
    
    /**
     * Gets the value of modified_gmt.
     * @return number Returns the value of modified_gmt.
     */
    public function get_modified_gmt() {
        return $this->modified_gmt;
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
                session_id varchar(100) NOT NULL,
                session_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                session_created_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                session_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                session_modified_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY  (session_id)
            )";
    }
    
    /**
     * Returns the table name for storing the entity.
     * @global wpdb $wpdb
     * @return string Returns the table name for storing the entity.
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
        
        $data['session_id'] = $this->session_id;
        $data['session_created'] = $this->created;
        $data['session_created_gmt'] = $this->created_gmt;
        $data['session_modified'] = $this->modified;
        $data['session_modified_gmt'] = $this->modified_gmt;
        
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
            '%s', //session_id
            '%s', //session_created
            '%s', //session_created_gmt
            '%s', //session_modified
            '%s', //session_modified_gmt
	);
                
        return $formats_arr;  
    }
    
    /**
     * Generates a session id.
     * @return string Returns the generated session id.
     */
    public static function generate_session_id()
    {
        $bytes = openssl_random_pseudo_bytes( 55 );
        $data = uniqid( mt_rand(), TRUE ) . $bytes;
        $hash = base64_encode( hash( 'sha256', $data, TRUE ) );
        $safe_hash = strtr( $hash, array('+' => '-', '/' => '_', '=' => '') );
        $session_id = $safe_hash;
        
        return $session_id;
    }

}
