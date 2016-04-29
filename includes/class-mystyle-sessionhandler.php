<?php

/**
 * MyStyle Session Hanlder class. 
 * 
 * The MyStyle Session Handler class handles MyStyle Sessions.
 *
 * @package MyStyle
 * @since 1.3.0
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
        if(session_id() == '') {
            session_start();
        }
        
        $session = null;
        
        //first look in the session variables
        if( isset( $_SESSION[MyStyle_Session::$SESSION_KEY] ) ) {    
            $session = $_SESSION[MyStyle_Session::$SESSION_KEY];
        } else {
            //next look in their cookies
            if( isset( $_COOKIE[MyStyle_Session::$COOKIE_NAME] ) ) {
                $session_id = $_COOKIE[MyStyle_Session::$COOKIE_NAME];
                $session = MyStyle_SessionManager::get( $session_id );
            }
        }
        
        //If no session is found, create a new one and set the cookie.
        if( $session == null ) {
            $session = MyStyle_Session::create();
            $_SESSION[MyStyle_Session::$SESSION_KEY] = $session;
            setcookie( 
                MyStyle_Session::$COOKIE_NAME, 
                $session->get_session_id(), 
                time() + (60*60*24*365*10) );
        }
        MyStyle_SessionManager::update( $session );
        
        return $session;
    }
    
    
}
