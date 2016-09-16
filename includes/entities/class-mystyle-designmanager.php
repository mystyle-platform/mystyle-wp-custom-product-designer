<?php

/**
 * MyStyle_DesignManager class. 
 * 
 * The MyStyle_DesignManager class has functions for managing MyStyle_Design
 * entities.
 *
 * @package MyStyle
 * @since 0.5
 */
abstract class MyStyle_DesignManager extends \MyStyle_EntityManager {
    
    /**
     * Get the design from the database.
     * @global wpdb $wpdb
     * @param integer $design_id The design id.
     * @param WP_User $user (optional) The current user.
     * @param MyStyle_Session $session 
     * @return \MyStyle_Design Returns the MyStyle_Design entity.
     * @throws MyStyle_Forbidden_Exception Throws a MyStyle_Forbidden_Exception
     * if the requested design is marked as private and the user isn't logged
     * in.
     * @throws MyStyle_Unauthorized_Exception Throws a 
     * MyStyle_Unauthorized_Exception if the design is marked as private and the
     * the passed user is not the owner of the design and the user doesn't have
     * 'read_private_posts' capability.
     */
    public static function get( 
                            $design_id,
                            WP_User $user = null,
                            MyStyle_Session $session = null
                        )
    {
        global $wpdb;
        
        $design = null;
        
        $query = 'SELECT * FROM ' . MyStyle_Design::get_table_name() . ' ' . 
                 'WHERE ' . MyStyle_Design::get_primary_key() . ' = ' . $design_id;
        
        $result_object = $wpdb->get_row($query);
        
        
        if( $result_object != null ) {
            $design = MyStyle_Design::create_from_result_object( $result_object );
        }
        
        //-------------- SECURITY CHECK ------------//
        if( $design != null ) {
            if( $design->get_access() === MyStyle_Access::$PRIVATE ) {
                //check if created by current/passed session
                if( ( $session != null ) &&
                    ( $design->get_session_id() != null ) &&
                    ( $session->get_session_id() == $design->get_session_id() )
                ) {
                    //design was created by the passed session, continue.
                } else {
                    //check for wp user match
                    if( $design->get_user_id() != null ) {
                        if( ( $user == null ) || ( $user->ID == 0 ) ) {
                            throw new MyStyle_Unauthorized_Exception( 'This design is private, you must log in to view it.');
                        }
                        if( $design->get_user_id() != $user->ID ) {
                            if( ! $user->has_cap( 'read_private_posts' ) ) {
                                throw new MyStyle_Forbidden_Exception( 'You are not authorized to access this design.' );
                            }
                        }
                    }
                }
            }
        }
        //------------ END SECURITY CHECK ------------//
        
        return $design;
    }
    
    /**
     * Deletes the passed design from the database.
     * @param MyStyle_Design $design
     * @global \wpdb $wpdb
     * @return boolean Returns true is the Design was successfully deleted,
     * otherwise, returns false.
     * @todo Add unit testing
     */
    public static function delete( MyStyle_Design $design ) {
        global $wpdb;
        
        $ret = $wpdb->delete( 
                        MyStyle_Design::get_table_name(), 
                        array( MyStyle_Design::get_primary_key() => $design->get_design_id() ),
                        array( '%d' )
                    );
        
        $deleted = ($ret != false);
        
        return $deleted;
    }
    
    /**
     * Get the previous design from the database.
     * @global wpdb $wpdb
     * @param int $current_design_id The design_id that you want to use as
     * the base for retrieving the previous design.
     * @param WP_User $user (optional) The current user.
     * @return \MyStyle_Design Returns the MyStyle_Design entity.
     * @todo Add unit testing
     */
    public static function get_previous_design( 
                                $current_design_id, 
                                WP_User $user = null 
                            ) 
    {
        global $wpdb;
        
        $design = null;
        
        $select = 'SELECT * FROM ' . MyStyle_Design::get_table_name() . ' ';
        $where  = 'WHERE ' . MyStyle_Design::get_primary_key() . ' < ' . $current_design_id . ' ';
        
        if( ( $user == null ) || ( $user->ID == 0 ) ) {
            //no user, get the next public design
            $where .= 'AND ms_access = 0 '; 
        } else {
            if( ! $user->has_cap( 'read_private_posts' ) ) {
                //user isn't admin, show public and their own private designs.
                $where .= 'AND ms_access = 0 OR ( ( ms_access = 1 ) AND ( user_id = ' . $user->ID . ' ) ) ';   
            }
        }
        //note: admin sees all designs.
        
        $order = 'ORDER BY ' . MyStyle_Design::get_primary_key() . ' DESC ';
        
        $limit = 'LIMIT 1 ';
        
        $query = $select . $where . $order . $limit;
        
        $result_object = $wpdb->get_row($query);
        
        if( $result_object != null ) {
            $design = MyStyle_Design::create_from_result_object( $result_object );
        }
        
        return $design;
    }
    
    /**
     * Get the next design from the database.
     * @global wpdb $wpdb
     * @param int $current_design_id The design_id that you want to use as
     * the base for retrieving the next design.
     * @param WP_User $user (optional) The current user.
     * @return \MyStyle_Design Returns the MyStyle_Design entity.
     * @todo Add unit testing
     */
    public static function get_next_design( 
                                $current_design_id, 
                                WP_User $user = null 
                            ) 
    {
        global $wpdb;
        
        $design = null;
        
        $select = 'SELECT * FROM ' . MyStyle_Design::get_table_name() . ' ';
        $where  = 'WHERE ' . MyStyle_Design::get_primary_key() . ' > ' . $current_design_id . ' ';
        
        if( ( $user == null ) || ( $user->ID == 0 ) ) {
            //no user, get the next public design
            $where .= 'AND ms_access = 0 '; 
        } else {
            if( ! $user->has_cap( 'read_private_posts' ) ) {
                //user isn't admin, show public and their own private designs.
                $where .= 'AND ms_access = 0 OR ( ( ms_access = 1 ) AND ( user_id = ' . $user->ID . ' ) ) ';   
            }
        }
        //note: admin sees all designs.
        
        $limit = 'LIMIT 1 ';
        
        $query = $select . $where . $limit;
        
        $result_object = $wpdb->get_row($query);
        
        if( $result_object != null ) {
            $design = MyStyle_Design::create_from_result_object( $result_object );
        }
        
        return $design;
    }
    
    /**
     * Sets the user_id on designs where it is null (or 0) using the email and
     * the session.
     * @global wpdb $wpdb
     * @param WP_User $user
     * @param MyStyle_Session (optional) $session
     * @return integer Returns the number or designs that were updated or false
     * if no rows were updated.
     */
    public static function set_user_id( $user, $session = null) {
        global $wpdb;

        $query = 'UPDATE ' . MyStyle_Design::get_table_name() . ' ' . 
                 'SET user_id = "' . $user->ID . '" ' .
                 'WHERE ( ( user_id IS NULL ) OR ( user_id = 0 ) ) ';
        $query .='AND ( ';
        
        if( ! empty( $user->user_email ) ) {
            // Where email matches and the session is empty or matches the passed session id.
            $query .=
                 ' ( ms_email = "' . $user->user_email . '" )';
            $query .= 
                 'AND ( ';
            if( $session != null ) {
                $query .=
                       ' ( session_id = "' . $session->get_session_id() . '" ) OR ';
            }
            $query .= 
                       ' ( session_id IS NULL ) OR ( session_id = "" ) ';
            $query .= 
                    ' ) ';
        } else {
            //If the user doesn't have an email address, try to match based on the session id
            if( $session != null ) {
                $query .=
                      ' ( session_id = "' . $session->get_session_id() . '" ) ';
            }
        }
        
        //if the design doesn't have an email set, try to macth just based on the session id.
        $query .= ') OR (ms_email IS NULL AND session_id = "' . $session->get_session_id() . '" ) ';
        
        //echo $query;
        
        $result = $wpdb->query($query);
        
        return $result;
    }
    
    /**
     * Retrieve designs from the database.
     *
     * @param int $per_page 
     * @param int $page_number
     *
     * @return mixed
     * @global $wpdb;
     * @todo add unit testing 
     */
    public static function get_designs( $per_page = 250, $page_number = 1 ) {
        global $wpdb;

        $sql = 'SELECT * FROM ' . MyStyle_Design::get_table_name();

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        } else {
            $sql .= ' ORDER BY ms_design_id DESC';
        }

        $sql .= " LIMIT $per_page";

        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        $results = $wpdb->get_results( $sql, 'OBJECT' );
        
        //transform the result objects (stdClass) into MyStyle_Designs
        $designs = null;
        if( $results != null ) {
            $designs = array();
            foreach($results as $result) {
                $design = MyStyle_Design::create_from_result_object( $result );
                array_push($designs, $design);
            }
        }

        return $designs;
    }
    
    /**
     * Retrieve the total number of designs from the db.
     *
     * @return integer
     * @global $wpdb;
     * @todo add unit testing 
     */
    public static function get_total_design_count( ) {
        global $wpdb;

        $sql = 'SELECT COUNT(' . MyStyle_Design::get_primary_key() . ') ' .
               'FROM ' . MyStyle_Design::get_table_name();

        $count = $wpdb->get_var( $sql );

        return $count;
    }

}


