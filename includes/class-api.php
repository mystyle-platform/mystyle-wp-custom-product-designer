<?php

/**
 * MyStyle API class. 
 * 
 * The MyStyle API class works with the MyStyle API.
 *
 * @package MyStyle
 * @since 0.2.1
 */
abstract class MyStyle_API {
    
    
    /**
     * Retrieves design data from the API and adds it to the passed design
     * object.
     * @param MyStyle_Design $design
     * @return \MyStyle_Design
     */
    public static function add_api_data_to_design(MyStyle_Design $design) {
            
        //TODO: Get rid of all of these constants.
        $api_endpoint_url = "http://api.ogmystyle.com/";
        $api_key = 72;
        $secret = "SqXHiNTaD5TC0Y908tC9nEqP6";
        $action = "design";
        $method = "get";
        $data = '{"design_id":[' . $design->get_design_id() . ']}';
        $ts = time();

        $toHash = $action . $method . $api_key . $data . $ts;
        $sig = base64_encode(hash_hmac('sha1', $toHash, $secret, true));

        $post_data = array();
        $post_data['action'] = $action;
        $post_data['method'] = $method;
        $post_data['app_id'] = $api_key;
        $post_data['data'] = $data;
        $post_data['sig'] = $sig;
        $post_data['ts'] = $ts;
        //$post_data['session'] = //not currently being used
        //$post_data['user_id'] = //not currently being used

        $response = wp_remote_post( $api_endpoint_url, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => $post_data,
                'cookies' => array()
            )
        );

        if ( is_wp_error( $response ) ) {
            //TODO: Handle this error
            $error_message = $response->get_error_message();
            //$body = "Something went wrong: $error_message";
        } else {
            $response_data = json_decode($response['body'], true); //['data'][$design_id]);
            $design_data = $response_data['data'][$design->get_design_id()];            
            //var_dump($design_data);

            $design->add_api_data($design_data);
        }
        
        return $design;
    }

}


