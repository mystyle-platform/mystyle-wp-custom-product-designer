<?php

/**
 * MyStyle Session Hanlder class. 
 * 
 * The MyStyle Session Handler class handles MyStyle Sessions.
 *
 * @package MyStyle
 * @since 1.2.0
 * @todo Add unit testing
 */
class MyStyle_SessionHandler {
    
    /**
     * Static function to get the current MyStyle Session. This function does
     * the following:
     * * Looks for the session in the session variables
     * * Looks for the session in the cookies.
     * * If no session is found, it creates one.
     * * Updates the modified date of the session and persists it to the
     *   database.
     * @return \MyStyle_Session Returns the current mystyle session.
     */
    public static function get() {
        
        if(isset($_SESSION['mystyle'])) {
            $session = $_SESSION['mystyle'];
        } else {
            $session = MyStyle_Session::create();
        }
        
        MyStyle_SessionManager::update( $session );
        
        return $session;
    }
    
    
}
