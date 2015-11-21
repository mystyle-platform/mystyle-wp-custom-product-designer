<?php

/**
 * MyStyle User class. 
 * 
 * The MyStyle User class represents a user of the the MyStyle system. A
 * user is someone who created a design.  They may also be a WordPress user
 * but don't have to be.
 *
 * @package MyStyle
 * @since 1.2.0
 */
class MyStyle_User {
    
    private $id; //the primary key (the mystyle user id from the API)
    private $email; //the email address of the user from the MyStyle API
    
    /**
     * Constructor
     * @param integer $id The user id from the MyStyle API
     * @param string $email The email from the MyStyle API
     */
    public function __construct($id, $email) {
        $this->id = $id;
        $this->email = $email;
    }
    
    /**
     * Gets the user id.
     * @return number Returns the user id.
     */
    public function get_id() {
        return $this->id;
    }
    
    /**
     * Gets the value of email.
     * @return string Returns the value of email.
     */
    public function get_email() {
        return $this->email;
    }

}