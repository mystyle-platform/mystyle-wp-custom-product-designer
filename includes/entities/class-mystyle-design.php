<?php
/**
 * The MyStyle Design class represents a design in the MyStyle system.
 *
 * @package MyStyle
 * @since 0.2.1
 */

/**
 * MyStyle_Design class.
 */
class MyStyle_Design implements MyStyle_Entity {

	/**
	 * The name of the db table where this entity is stored.
	 *
	 * Note: this is without the db prefix.
	 *
	 * @var string
	 */
	const TABLE_NAME = 'mystyle_designs';

	/**
	 * The primary key column for the table.
	 *
	 * @var string
	 */
	const PRIMARY_KEY = 'ms_design_id';

	/**
	 * The primary key.
	 *
	 * @var integer
	 */
	private $design_id;

	/**
	 * The date that the design was created.
	 *
	 * @var number
	 */
	private $created;

	/**
	 *
	 * The date that the design was created (adjusted to the GMT timezone).
	 *
	 * @var number
	 */
	private $created_gmt;

	/**
	 * The date the design was last modified.
	 *
	 * @var number
	 */
	private $modified;

	/**
	 * The date the design was last modified (adjusted to the GMT timezone).
	 *
	 * @var number
	 */
	private $modified_gmt;

	/**
	 * The design description.
	 *
	 * @var string
	 */
	private $description;

	/**
	 * The url to the deisng image for use when printing.
	 *
	 * @var string
	 */
	private $print_url;

	/**
	 * The url to the design image for use on web pages.
	 *
	 * @var string
	 */
	private $web_url;

	/**
	 * The url to the design image for use as a thumbnail.
	 *
	 * @var string
	 */
	private $thumb_url;

	/**
	 * The url to the design spec for the design.
	 *
	 * @var string
	 */
	private $design_url;

	/**
	 * This is the MyStyle product id
	 *
	 * @var integer
	 */
	private $template_id;

	/**
	 * This is the local (WooCommerce) product id.
	 *
	 * @var integer
	 */
	private $product_id;

	/**
	 * This is the local (WordPress) user id (if the user designer has one).
	 *
	 * @var integer
	 */
	private $user_id;

	/**
	 * The mystyle user id.
	 *
	 * @var integer
	 */
	private $designer_id;

	/**
	 * The mystyle plugin's session id.
	 *
	 * @var integer
	 */
	private $session_id;

	/**
	 * The email that was submitted with the design (if any).
	 *
	 * @var email|null
	 */
	private $email;

	/**
	 * The title that was submitted with the design (if any).
	 *
	 * @var title|null
	 */
	private $title;

	/**
	 * A price for the design.
	 *
	 * @var number
	 */
	private $price;

	/**
	 * Whether or not the mobile version of the customizer was used to create
	 * the design.
	 *
	 * @var boolean
	 */
	private $mobile;

	/**
	 * The access visibility for the design.
	 *
	 *  * 0: public (anyone can view the design).
	 *  * 1: private (only the author and the admin can view the design).
	 *  * 2: restricted (only the admin can view the design - primarily for
	 *       inappropriate or offensive designs).
	 *  * 3: hidden (design access is public but the design isn't listed
	 *       anywhere - use when design is upgraded to a product but you don't
	 *       want a design profile page).
	 *
	 * See the MyStyle_Access class for more information.
	 *
	 * @var integer
	 */
	private $access;

	/**
	 * How many times the design page has been viewed.
	 *
	 * @var integer
	 */
	private $view_count;

	/**
	 * How many times the design has been purchased.
	 *
	 * @var integer
	 */
	private $purchase_count;

	/**
	 * The data that was submitted when the Add to Cart ("Customize") button was
	 * clicked.
	 *
	 * @var array
	 */
	private $cart_data;

	/**
	 * Constructor. Note: see the functions below for additional ways to create
	 * a Design.
	 */
	public function __construct() {
		$this->created        = date( MyStyle::STANDARD_DATE_FORMAT );
		$this->created_gmt    = gmdate( MyStyle::STANDARD_DATE_FORMAT );
		$this->modified       = date( MyStyle::STANDARD_DATE_FORMAT );
		$this->modified_gmt   = date( MyStyle::STANDARD_DATE_FORMAT );
		$this->mobile         = 0;
		$this->access         = 0;
		$this->view_count     = 0;
		$this->purchase_count = 0;
	}

	/**
	 * Static function to create a new Design from POST data. Call using
	 * `MyStyle_Design::create_from_post($post_data);`.
	 *
	 * @param array $post_data POST data to be used to construct the Design.
	 * @return \self Works like a constructor.
	 */
	public static function create_from_post( $post_data ) {
		$instance = new self();

		$passthru      = json_decode( base64_decode( $post_data['h'] ), true );
		$passthru_post = $passthru['post'];
		$product_id    = $passthru_post['add-to-cart'];
		$user_id       = false ;
        
        if( isset( $passthru['user']['user_id'] ) ) {
            $user_id = $passthru['user']['user_id'] ;
        }

		$instance->product_id  = (int) htmlspecialchars( $product_id ); // Mapping local_product_id to product_id.
		$instance->design_id   = (int) htmlspecialchars( $post_data['design_id'] );
		$instance->template_id = (int) htmlspecialchars( $post_data['product_id'] ); // Mapping product_id to template_id.
		
		if ( isset( $post_data['user_id'] ) ) {
			$instance->designer_id = (int) htmlspecialchars( $post_data['user_id'] );
            $instance->user_id = (int) htmlspecialchars( $user_id ) ;
		}
		else {
            $instance->designer_id = (int) htmlspecialchars( $user_id ) ;
            $instance->user_id = (int) htmlspecialchars( $user_id ) ;
        }

		$instance->cart_data = wp_json_encode( $passthru_post );

		// These aren't always passed (or may be deprecated).
		if ( isset( $post_data['description'] ) ) {
			$instance->description = htmlspecialchars( $post_data['description'] );
		}
		if ( isset( $post_data['price'] ) ) {
			$instance->price = (int) htmlspecialchars( $post_data['price'] );
		}

		return $instance;
	}

	/**
	 * Static function to create a Design object from a WP result object. Call
	 * using MyStyle_Design::create_from_result_object($result_object);  This
	 * function should correspond with the get_data_array() function below.
	 *
	 * @param Object $result_object A WP row result object to be used to
	 * construct the Design. This is an object with public fields that
	 * correspond to the column names from the database.
	 * @return \self Works like a constructor.
	 */
	public static function create_from_result_object( $result_object ) {
		$instance = new self();

		$instance->design_id      = (int) htmlspecialchars( $result_object->ms_design_id );
		$instance->template_id    = (int) htmlspecialchars( $result_object->ms_product_id );
		$instance->designer_id    = (int) htmlspecialchars( $result_object->ms_user_id );
		$instance->email          = htmlspecialchars( $result_object->ms_email );
		$instance->title          = ( ! is_null( $result_object->ms_title) ? htmlspecialchars( $result_object->ms_title ) : false );
		$instance->description    = ( ! is_null( $result_object->ms_description) ? htmlspecialchars( $result_object->ms_description ) : false );
		$instance->price          = (int) ( ! is_null( $result_object->ms_price) ? htmlspecialchars( $result_object->ms_price ) : 0 );
		$instance->print_url      = htmlspecialchars( $result_object->ms_print_url );
		$instance->web_url        = htmlspecialchars( $result_object->ms_web_url );
		$instance->thumb_url      = htmlspecialchars( $result_object->ms_thumb_url );
		$instance->design_url     = htmlspecialchars( $result_object->ms_design_url );
		$instance->product_id     = (int) htmlspecialchars( $result_object->product_id );
		$instance->user_id        = (int) htmlspecialchars( $result_object->user_id );
		$instance->session_id     = htmlspecialchars( $result_object->session_id );
		$instance->mobile         = (int) htmlspecialchars( $result_object->ms_mobile );
		$instance->access         = (int) htmlspecialchars( $result_object->ms_access );
		$instance->created        = htmlspecialchars( $result_object->design_created );
		$instance->created_gmt    = htmlspecialchars( $result_object->design_created_gmt );
		$instance->modified       = htmlspecialchars( $result_object->design_modified );
		$instance->modified_gmt   = htmlspecialchars( $result_object->design_modified_gmt );
		$instance->view_count     = (int) htmlspecialchars( $result_object->design_view_count );
		$instance->purchase_count = (int) htmlspecialchars( $result_object->design_purchase_count );
		$instance->cart_data      = $result_object->cart_data;

		return $instance;
	}

	/**
	 * Static function to create a Design object from a WP result array. Call
	 * using MyStyle_Design::create_from_result_array($result_array);  This
	 * function should correspond with the get_data_array() function below.
	 *
	 * This is used for features such as the Design Manager's design list.
	 *
	 * @param array $result_array A WP row result array to be used to
	 * construct the Design. This is an associative array with keys that
	 * correspond to the column names from the database.
	 * @return \self Works like a constructor.
	 */
	public static function create_from_result_array( $result_array ) {
		$instance = new self();

		$instance->design_id      = (int) htmlspecialchars( $result_array['ms_design_id'] );
		$instance->template_id    = (int) htmlspecialchars( $result_array['ms_product_id'] );
		$instance->designer_id    = (int) htmlspecialchars( $result_array['ms_user_id'] );
		$instance->email          = htmlspecialchars( $result_array['ms_email'] );
		$instance->title          = htmlspecialchars( $result_array['ms_title'] );
		$instance->description    = htmlspecialchars( $result_array['ms_description'] );
		$instance->price          = (int) htmlspecialchars( $result_array['ms_price'] );
		$instance->print_url      = htmlspecialchars( $result_array['ms_print_url'] );
		$instance->web_url        = htmlspecialchars( $result_array['ms_web_url'] );
		$instance->thumb_url      = htmlspecialchars( $result_array['ms_thumb_url'] );
		$instance->design_url     = htmlspecialchars( $result_array['ms_design_url'] );
		$instance->product_id     = (int) htmlspecialchars( $result_array['product_id'] );
		$instance->user_id        = (int) htmlspecialchars( $result_array['user_id'] );
		$instance->session_id     = htmlspecialchars( $result_array['session_id'] );
		$instance->mobile         = (int) htmlspecialchars( $result_array['ms_mobile'] );
		$instance->access         = (int) htmlspecialchars( $result_array['ms_access'] );
		$instance->created        = htmlspecialchars( $result_array['design_created'] );
		$instance->created_gmt    = htmlspecialchars( $result_array['design_created_gmt'] );
		$instance->modified       = htmlspecialchars( $result_array['design_modified'] );
		$instance->modified_gmt   = htmlspecialchars( $result_array['design_modified_gmt'] );
		$instance->view_count     = (int) htmlspecialchars( $result_array['design_view_count'] );
		$instance->purchase_count = (int) htmlspecialchars( $result_array['design_purchase_count'] );
		$instance->cart_data      = $result_array['cart_data'];

		return $instance;
	}

	/**
	 * Static function to create a new Design from JSON data. Call using
	 * `MyStyle_Design::create_from_json( $json );`.
	 *
	 * This is used by the REST API.
	 *
	 * @param array $json_str JSON string to use to create the design.
	 * @return \self Works like a constructor.
	 */
	public static function create_from_json( $json_str ) {
		$json_arr = json_decode( $json_str, true );

		$instance = new self();

		$instance->design_id      = (int) htmlspecialchars( $json_arr['design_id'] );
		$instance->template_id    = (int) htmlspecialchars( $json_arr['template_id'] );
		$instance->designer_id    = (int) htmlspecialchars( $json_arr['designer_id'] );
		$instance->email          = htmlspecialchars( $json_arr['email'] );
		$instance->title          = htmlspecialchars( $json_arr['title'] );
		$instance->description    = htmlspecialchars( $json_arr['description'] );
		$instance->price          = (int) htmlspecialchars( $json_arr['price'] );
		$instance->print_url      = htmlspecialchars( $json_arr['print_url'] );
		$instance->web_url        = htmlspecialchars( $json_arr['web_url'] );
		$instance->thumb_url      = htmlspecialchars( $json_arr['thumb_url'] );
		$instance->design_url     = htmlspecialchars( $json_arr['design_url'] );
		$instance->product_id     = (int) htmlspecialchars( $json_arr['product_id'] );
		$instance->user_id        = (int) htmlspecialchars( $json_arr['user_id'] );
		$instance->session_id     = htmlspecialchars( $json_arr['session_id'] );
		$instance->mobile         = (int) htmlspecialchars( $json_arr['mobile'] );
		$instance->access         = (int) htmlspecialchars( $json_arr['access'] );
		$instance->created        = htmlspecialchars( $json_arr['created'] );
		$instance->created_gmt    = htmlspecialchars( $json_arr['created_gmt'] );
		$instance->modified       = htmlspecialchars( $json_arr['modified'] );
		$instance->modified_gmt   = htmlspecialchars( $json_arr['modified_gmt'] );
		$instance->view_count     = (int) htmlspecialchars( $json_arr['view_count'] );
		$instance->purchase_count = (int) htmlspecialchars( $json_arr['purchase_count'] );
		$instance->cart_data      = $json_arr['cart_data'];

		return $instance;
	}

	/**
	 * Method to add data received from the api call to the Design.
	 *
	 * @param array $api_data API data to be used to add more data to the
	 * Design. This is an array of fields values (see the API docs for details).
	 */
	public function add_api_data( $api_data ) {
		$this->print_url  = htmlspecialchars( $api_data['print_url'] );
		$this->web_url    = htmlspecialchars( $api_data['web_url'] );
		$this->thumb_url  = htmlspecialchars( $api_data['thumb_url'] );
		$this->design_url = htmlspecialchars( $api_data['design_url'] );
		$this->mobile     = htmlspecialchars( $api_data['mobile'] );
		$this->access     = htmlspecialchars( $api_data['access'] );
	}

	/**
	 * Sets the value of design_id. This is used primarily by our unit tests.
	 *
	 * @param number $design_id The new value for design_id.
	 */
	public function set_design_id( $design_id ) {
		$this->design_id = $design_id;
	}

	/**
	 * Gets the value of design_id.
	 *
	 * @return number Returns the value of design_id.
	 */
	public function get_design_id() {
		return $this->design_id;
	}

	/**
	 * Gets the value of created.
	 *
	 * @return number Returns the value of created.
	 */
	public function get_created() {
		return $this->created;
	}

	/**
	 * Gets the value of created_gmt.
	 *
	 * @return number Returns the value of created_gmt.
	 */
	public function get_created_gmt() {
		return $this->created_gmt;
	}

	/**
	 * Gets the value of modified.
	 *
	 * @return number Returns the value of modified.
	 */
	public function get_modified() {
		return $this->modified;
	}

	/**
	 * Gets the value of modified_gmt.
	 *
	 * @return number Returns the value of modified_gmt.
	 */
	public function get_modified_gmt() {
		return $this->modified_gmt;
	}

	/**
	 * Sets the value of description.
	 *
	 * @param string $description The new value for description.
	 */
	public function set_description( $description ) {
		$this->description = $description;
	}

	/**
	 * Gets the value of description.
	 *
	 * @return string Returns the value of description.
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Sets the value of print_url.
	 *
	 * @param string $print_url The new value for print_url.
	 */
	public function set_print_url( $print_url ) {
		$this->print_url = $print_url;
	}

	/**
	 * Gets the value of print_url.
	 *
	 * @return string Returns the value of print_url.
	 */
	public function get_print_url() {
		return $this->print_url;
	}

	/**
	 * Sets the value of web_url.
	 *
	 * @param string $web_url The new value for web_url.
	 */
	public function set_web_url( $web_url ) {
		$this->web_url = $web_url;
	}

	/**
	 * Gets the value of web_url.
	 *
	 * @return string Returns the value of web_url.
	 */
	public function get_web_url() {
		return $this->web_url;
	}

	/**
	 * Sets the value of thumb_url.
	 *
	 * @param string $thumb_url The new value for thumb_url.
	 */
	public function set_thumb_url( $thumb_url ) {
		$this->thumb_url = $thumb_url;
	}

	/**
	 * Gets the value of thumb_url.
	 *
	 * @return string Returns the value of thumb_url.
	 */
	public function get_thumb_url() {
		return $this->thumb_url;
	}

	/**
	 * Sets the value of design.
	 *
	 * @param string $design_url The new value for design_url.
	 */
	public function set_design_url( $design_url ) {
		$this->design_url = $design_url;
	}

	/**
	 * Gets the value of design_url.
	 *
	 * @return string Returns the value of design_url.
	 */
	public function get_design_url() {
		return $this->design_url;
	}

	/**
	 * Sets the value of template_id.
	 *
	 * @param number $template_id The new value for template_id.
	 */
	public function set_template_id( $template_id ) {
		$this->template_id = $template_id;
	}

	function mystyle_design_Url( $image_type = false )
	{
		$options = get_option(MYSTYLE_OPTIONS_NAME, array());

		$enable_cdn_image = (array_key_exists('enable_cdn_images', $options)) ? $options['enable_cdn_images'] : 0;
		$custom_url = (array_key_exists('cdn_base_url', $options)) ? $options['cdn_base_url'] : '';

		if ($enable_cdn_image == 1 && !empty($custom_url)) {
			$image_url = $this->getImageUrl( $image_type ); 
			$updated_url = preg_replace('/http.*\.s3\.amazonaws\.com/', $custom_url, $image_url ) ;
		} else {
			$updated_url = $this->getImageUrl( $image_type ); 
		}

		return $updated_url;
	}




	/**
	 * Gets the value of image_url.
	 * using license status.
	 */
	public function getImageUrl( $image_type = false )
	{
		$license_status = get_option('mystyle_license_status_');

		if( ! $image_type ) {
			$options = get_option(MYSTYLE_OPTIONS_NAME, array());
			$image_type = (array_key_exists('image_type', $options)) ? $options['image_type'] : 'thumbnail';
		}
		
		if ($image_type === 'web' && $license_status == "valid") {
			$image_url = $this->get_web_url();
		} else {
			$image_url = $this->get_thumb_url();
		}
		return $image_url;
	}

	/**
	 * Gets the value of template_id.
	 *
	 * @return number Returns the value of template_id.
	 */
	public function get_template_id() {
		return $this->template_id;
	}

	/**
	 * Sets the value of product_id.
	 *
	 * @param number $product_id The new value for product_id.
	 */
	public function set_product_id( $product_id ) {
		$this->product_id = $product_id;
	}

	/**
	 * Gets the value of product_id.
	 *
	 * @return number Returns the value of product_id.
	 */
	public function get_product_id() {
		return $this->product_id;
	}

	/**
	 * Sets the value of user_id. This is the local (WordPress) user id (if the
	 * user/designer has one).
	 *
	 * @param number $user_id The new value for user_id.
	 */
	public function set_user_id( $user_id ) {
		$this->user_id = $user_id;
	}

	/**
	 * Gets the value of user_id. This is the local (WordPress) user id (if the
	 * user/designer has one).
	 *
	 * @return number Returns the value of user_id.
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Sets the value of designer_id. This is the MyStyle user id (as
	 * communicated via the MyStyle API).
	 *
	 * @param number $designer_id The new value for designer_id.
	 */
	public function set_designer_id( $designer_id ) {
		$this->designer_id = $designer_id;
	}

	/**
	 * Gets the value of designer_id. This is the MyStyle user id (as
	 * communicated via the MyStyle API).
	 *
	 * @return number Returns the value of designer_id.
	 */
	public function get_designer_id() {
		return $this->designer_id;
	}

	/**
	 * Sets the value of session_id.
	 *
	 * @param string $session_id The new value for session_id.
	 */
	public function set_session_id( $session_id ) {
		$this->session_id = $session_id;
	}

	/**
	 * Gets the value of session_id.
	 *
	 * @return string Returns the value of session_id.
	 */
	public function get_session_id() {
		return $this->session_id;
	}

	/**
	 * Sets the value of email.
	 *
	 * @param string $email The new value for email.
	 */
	public function set_email( $email ) {
		$this->email = $email;
	}

	/**
	 * Gets the value of email.
	 *
	 * @return string Returns the value of email.
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Sets the title.
	 *
	 * @param string|null $title The new title.
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Gets the value of title.
	 *
	 * @return string Returns the value of title.
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Sets the value of price.
	 *
	 * @param number $price The new value for price.
	 */
	public function set_price( $price ) {
		$this->price = $price;
	}

	/**
	 * Gets the value of price.
	 *
	 * @return number Returns the value of price.
	 */
	public function get_price() {
		return $this->price;
	}

	/**
	 * Gets the value of access.
	 *
	 * @return number Returns the value of access.
	 */
	public function get_access() {
		return $this->access;
	}

	/**
	 * Sets the value of mobile.
	 *
	 * @param number $mobile The new value for mobile.
	 */
	public function set_mobile( $mobile ) {
		$this->mobile = $mobile;
	}

	/**
	 * Gets the value of mobile.
	 *
	 * @return number Returns the value of mobile.
	 */
	public function is_mobile() {
		return $this->mobile;
	}

	/**
	 * Sets the value of access.
	 *
	 * @param number $access The new value for access.
	 */
	public function set_access( $access ) {
		$this->access = $access;
	}

	/**
	 * Sets the value of view_count.
	 *
	 * @param number $view_count The new value for view_count.
	 */
	public function set_view_count( $view_count ) {
		$this->view_count = $view_count;
	}

	/**
	 * Gets the value of view_count.
	 *
	 * @return number Returns the value of view_count.
	 */
	public function get_view_count() {
		return $this->view_count;
	}

	/**
	 * Sets the value of purchase_count.
	 *
	 * @param number $purchase_count The new value for purchase_count.
	 */
	public function set_purchase_count( $purchase_count ) {
		$this->purchase_count = $purchase_count;
	}

	/**
	 * Increment the purchase_count.
	 */
	public function increment_purchase_count() {
		$this->purchase_count++;
	}

	/**
	 * Gets the value of purchase_count.
	 *
	 * @return number Returns the value of purchase_count.
	 */
	public function get_purchase_count() {
		return $this->purchase_count;
	}

	/**
	 * Sets the value of cart_data.
	 *
	 * @param string $cart_data The new value for cart_data.
	 */
	public function set_cart_data( $cart_data ) {
		$this->cart_data = $cart_data;
	}

	/**
	 * Gets the value of cart_data.
	 *
	 * @return string Returns the value of cart_data. cart_data is a json
	 * encoded string of the cart_item data from when the design was created.
	 */
	public function get_cart_data() {
		return $this->cart_data;
	}

	/**
	 * Function for converting the object into an array for use with WP meta
	 * storage.
	 *
	 * @return array Returns an array for storage as WP meta data.
	 */
	public function get_meta() {
		$meta = array();

		$meta['design_id'] = $this->design_id;

		return $meta;
	}

	/**
	 * Gets the SQL schema for creating the database table.
	 *
	 * @global wpdb $wpdb
	 * @return string Returns a string containing SQL schema for creating the
	 * table.
	 */
	public static function get_schema() {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_NAME;
		return "
            CREATE TABLE $table_name (
                ms_design_id bigint(32) NOT NULL,
                ms_product_id bigint(20) NOT NULL,
                ms_user_id bigint(20) NULL,
                ms_email varchar(255) NULL,
                ms_title varchar(255) NULL,
                ms_description text NULL,
                ms_price numeric(15,2) NULL,
                ms_print_url varchar(255) NULL,
                ms_web_url varchar(255) NULL,
                ms_thumb_url varchar(255) NULL,
                ms_design_url varchar(255) NULL,
                product_id bigint(20) NULL,
                user_id bigint(20) NULL DEFAULT NULL,
                design_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                design_created_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                design_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                design_modified_gmt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                ms_mobile int(1) NOT NULL DEFAULT '0',
                ms_access int(1) NOT NULL DEFAULT '0',
                design_view_count bigint(20) NULL DEFAULT '0',
                design_purchase_count bigint(20) NULL DEFAULT '0',
                session_id varchar(100) NULL DEFAULT NULL,
                cart_data TEXT NULL DEFAULT NULL,
                PRIMARY KEY (ms_design_id)
            )";
	}

	/**
	 * Returns the table name for storing designs.
	 *
	 * @global type $wpdb
	 * @return string Returns the table name for storing designs.
	 */
	public static function get_table_name() {
		global $wpdb;

		return $wpdb->prefix . self::TABLE_NAME;
	}

	/**
	 * Gets the name of the primary key column.
	 *
	 * @return string Returns the name of the primary key column for the table.
	 */
	public static function get_primary_key() {
		return self::PRIMARY_KEY;
	}

	/**
	 * Gets the entity data to insert into the table.
	 *
	 * @return array Data to insert (in column => value pairs)
	 */
	public function get_data_array() {
		$data = array();

		$data['ms_design_id']          = $this->design_id;
		$data['ms_product_id']         = $this->template_id;
		$data['ms_user_id']            = $this->designer_id;
		$data['ms_email']              = $this->email;
		$data['ms_title']              = $this->title;
		$data['ms_description']        = $this->description;
		$data['ms_price']              = $this->price;
		$data['ms_print_url']          = $this->print_url;
		$data['ms_web_url']            = $this->web_url;
		$data['ms_thumb_url']          = $this->thumb_url;
		$data['ms_design_url']         = $this->design_url;
		$data['product_id']            = $this->product_id;
		$data['user_id']               = $this->user_id;
		$data['design_created']        = $this->created;
		$data['design_created_gmt']    = $this->created_gmt;
		$data['design_modified']       = $this->modified;
		$data['design_modified_gmt']   = $this->modified_gmt;
		$data['ms_mobile']             = $this->mobile;
		$data['ms_access']             = $this->access;
		$data['design_view_count']     = $this->view_count;
		$data['design_purchase_count'] = $this->purchase_count;
		$data['session_id']            = $this->session_id;
		$data['cart_data']             = $this->cart_data;

		return $data;
	}

	/**
	 * Gets the insert format for the entity. This matches up with the
	 * get_data_array() function.
	 *
	 * See https://codex.wordpress.org/Class_Reference/wpdb#INSERT_rows
	 *
	 * @return (array|string)
	 */
	public function get_insert_format() {

		$formats_arr = array(
			'%d', // ms_design_id.
			'%d', // ms_product_id.
			'%d', // ms_user_id.
			'%s', // ms_email.
			'%s', // ms_title.
			'%s', // ms_description.
			'%d', // ms_price.
			'%s', // ms_print_url.
			'%s', // ms_web_url.
			'%s', // ms_thumb_url.
			'%s', // ms_design_url.
			'%d', // product_id.
			'%d', // user_id.
			'%s', // design_created.
			'%s', // design_created_gmt.
			'%s', // design_modified.
			'%s', // design_modified_gmt.
			'%d', // ms_mobile.
			'%d', // ms_access.
			'%d', // design_view_count.
			'%d', // design_purchase_count.
			'%s', // session_id.
			'%s', // cart_data.
		);

		return $formats_arr;
	}

	/**
	 * Build the reload url to the customizer for the design.
	 */
	public function get_reload_url() {
		$customizer_url = MyStyle_Customize_Page::get_design_url( $this );

		return $customizer_url;
	}

	/**
	 * Build the scratch url to the customizer for the design.
	 *
	 * The "scratch" url is a url that loads the customizer with the product
	 * used in the design but without the design (allowing you to create a new
	 * design for the product "from scratch").
	 *
	 * This works the same as the get_reload_url function above except that it
	 * leaves off the 'design_id' URL query arg.
	 *
	 * @returns string Returns the "scratch" url to the customizer for the
	 * design.
	 */
	public function get_scratch_url() {
		$customizer_url = MyStyle_Customize_Page::get_scratch_url( $this );

		return $customizer_url;
	}

	/**
	 * Get URL that will add the design to the cart and then show the cart. This
	 * is used for adding designs from the design profile page to the cart.
	 *
	 * @return string The url to add the design to the cart and show the cart.
	 * @global \WooCommerce $woocommerce
	 */
	public function get_add_to_cart_url() {
		global $woocommerce;

		// Get the woocommerce cart.
		$cart = $woocommerce->cart;

		// Build the url.
		$cart_url = $cart->get_cart_url();
		$cart_url = add_query_arg( 'add-to-cart', $this->product_id, $cart_url );
		$cart_url = add_query_arg( 'design_id', $this->design_id, $cart_url );

		return $cart_url;
	}

	/**
	 * Get the underlying product.
	 *
	 * @returns MyStyle_Product Returns the underlying MyStyle_Product.
	 */
	public function get_product() {
		$wc_product = wc_get_product( $this->product_id );
		
        $product = new \MyStyle_Product( $wc_product );

		return $product->get_product() ;
	}

	/**
	 * Gets the cart_data as an associative array.
	 *
	 * @return array|null Returns the cart data as an associative
	 * ($key => $value) array or returns null if there is no cart_data.
	 */
	public function get_cart_data_array() {
		$cart_data_array = null;

		if ( ! empty( $this->cart_data ) ) {
			$cart_data_array = json_decode( $this->cart_data, true );
		}

		return $cart_data_array;
	}

	/**
	 * Return the MyStyle_Design as an array ready to be encoded to JSON.
	 *
	 * @return array Returns the MyStyle_Design as an array ready to be encoded to
	 * JSON.
	 */
	public function json_encode() {
		$arr = array(
			'design_id'      => MyStyle_Util::prep_rest_val( $this->design_id ),
			'template_id'    => MyStyle_Util::prep_rest_val( $this->template_id ),
			'designer_id'    => MyStyle_Util::prep_rest_val( $this->designer_id ),
			'email'          => MyStyle_Util::prep_rest_val( $this->email ),
			'title'          => MyStyle_Util::prep_rest_val( $this->title ),
			'description'    => MyStyle_Util::prep_rest_val( $this->description ),
			'price'          => MyStyle_Util::prep_rest_val( $this->price ),
			'print_url'      => MyStyle_Util::prep_rest_val( $this->print_url ),
			'web_url'        => MyStyle_Util::prep_rest_val( $this->web_url ),
			'thumb_url'      => MyStyle_Util::prep_rest_val( $this->thumb_url ),
			'design_url'     => MyStyle_Util::prep_rest_val( $this->design_url ),
			'product_id'     => MyStyle_Util::prep_rest_val( $this->product_id ),
			'user_id'        => MyStyle_Util::prep_rest_val( $this->user_id ),
			'created'        => MyStyle_Util::prep_rest_val( $this->created ),
			'created_gmt'    => MyStyle_Util::prep_rest_val( $this->created_gmt ),
			'modified'       => MyStyle_Util::prep_rest_val( $this->modified ),
			'modified_gmt'   => MyStyle_Util::prep_rest_val( $this->modified_gmt ),
			'mobile'         => MyStyle_Util::prep_rest_val( $this->mobile ),
			'access'         => MyStyle_Util::prep_rest_val( $this->access ),
			'view_count'     => MyStyle_Util::prep_rest_val( $this->view_count ),
			'purchase_count' => MyStyle_Util::prep_rest_val( $this->purchase_count ),
			'session_id'     => MyStyle_Util::prep_rest_val( $this->session_id ),
			'cart_data'      => MyStyle_Util::prep_rest_val( $this->cart_data ),
		);

		return $arr;
	}
}