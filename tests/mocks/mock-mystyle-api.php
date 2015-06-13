<?php

/**
 * Mocks the MyStyle API
 *
 * @package MyStyle
 * @since 0.2.1
 */
class MyStyleMockAPI {

    
    /**
     * Returns a mocked response from the MyStyle API 
     * @wp-hook pre_http_request
     * @param bool $preempt Whether to preempt an HTTP request return. Default
     * false.
     * @param array $args HTTP request arguments.
     * @param string $url The request URL.
     * @return Returns a mocked response from the MyStyle API.
     */
   function mock_api_call( $preempt, $args, $url )
   {
        // Get an instance of WP_Http.
        $http = _wp_http_get_object();

        // Mock response
        $result = array();
        $result['headers'] = array();
        $result['headers']['server'] = 'Apache';
        $result['headers']['vary'] = 'Accept-Encoding';
        $result['headers']['content-type'] = "text/html";
        $result['headers']['date'] = "Wed, 27 May 2015 20:59:04 GMT";
        $result['headers']['keep-alive'] = "timeout=5, max=100";
        $result['headers']['access-control-allow-origin'] = "*";
        $result['headers']['connection"'] = "close";
        $result['headers']['x-powered-by'] = "PHP/5.5.9-1ubuntu4.9";
        $result['headers']['access-control-allow-headers'] = "Origin, X-Requested-With, Content-Type, Accept";

        $design_id = 1;

        $design = array();
        $design['thumb_url'] = 'http://testhost/test_thumb_url.jpg';
        $design['web_url'] = 'http://testhost/test_web_url.jpg';
        $design['print_url'] = 'http://testhost/test_print_url.jpg';
        $design['design_url'] = 'http://testhost/test_design_url.jpg';
        $design['access'] = 0;
        $design['design_id'] = $design_id;
        $design['user_id'] = 1;
        $design['app_id'] = 0;
        $design['product_id'] = 0;
        $design['created'] = 0;

        $json = array();
        $json['data'] = array();
        $json['data'][ $design_id ] = $design;

        $result['body'] = json_encode($json);

        $result['headers']['content-length'] = strlen( $result['body'] );

        $result['response'] = array();
        $result['response']['code'] = 200;
        $result['response']['message'] = "OK";
        $result['response']['cookies'] = array();
        $result['response']['filename'] = null;

        return $result;
   }

}
