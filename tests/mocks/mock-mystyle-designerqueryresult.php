<?php

/**
 * Mocks the result of a mystyle designer query.
 *
 * @package MyStyle
 * @since 1.2.0
 */
class MyStyle_MockDesignerQueryResult {

    public $ms_user_id;
    public $designer_created;
    public $designer_created_gmt;
    public $designer_modified;
    public $designer_modified_gmt;
    public $user_id;
    public $ms_email;
    
    public function __construct( $designer_id ) {
        $this->ms_user_id = $designer_id;
        $this->designer_created = '2015-08-06 22:35:52';
        $this->designer_created_gmt = '2015-08-06 22:35:52';
        $this->designer_modified = '2015-08-06 22:35:52';
        $this->designer_modified_gmt = '2015-08-06 22:35:52';
        $this->user_id = 2;
        $this->ms_email = 'someone@example.com';
    }

}
