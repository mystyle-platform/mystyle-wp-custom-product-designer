<?php
/**
 * MyStyle Notice.
 *
 * The MyStyle Notice class represents an MyStyle notice.
 *
 * @package MyStyle
 * @since 1.2
 */

/**
 * MyStyle_Notice class.
 */
class MyStyle_Notice {

	/**
	 * The notice_key is a key such as "nag_configure".
	 *
	 * @var string
	 */
	private $notice_key;

	/**
	 * The message that you want to display to the user.
	 *
	 * @var string
	 */
	private $message;

	/**
	 * The notice type ('error', 'updated', or 'update-nag') See:
	 * https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices
	 *
	 * @var string The type of notice this is.
	 */
	private $type;

	/**
	 * Whether or not the message is dismissible.
	 *
	 * @var boolean
	 */
	private $dismissible;

	/**
	 * A string (such as '+30 days') for use in php's strtotime function.
	 *
	 * @var string
	 */
	private $remind_when;

	/**
	 * Constructor.
	 */
	public function __construct() {

	}

	/**
	 * Static function to create a new Notice. Call using
	 * `MyStyle_Notice::create('nag_configure', 'some message', true/false );`.
	 *
	 * @param string  $notice_key The notice_key is a key such as "nag_configure".
	 * @param string  $message The message that you want to display to the user.
	 * @param string  $type (optional) The notice type ('error', 'updated', or
	 * 'update-nag').
	 * @param boolean $dismissible (optional) Whether or not the message is
	 * dismissible.
	 * @param string  $remind_when A string (such as '+30 days') for use in php's
	 * strtotime function.
	 * @return \self Works like a constructor.
	 */
	public static function create( $notice_key, $message, $type = 'updated', $dismissible = false, $remind_when = null ) {
		$instance = new self();

		$instance->notice_key  = $notice_key;
		$instance->message     = $message;
		$instance->type        = $type;
		$instance->dismissible = $dismissible;
		$instance->remind_when = $remind_when;

		return $instance;
	}

	/**
	 * Static function to recreate a Notice. `Call using
	 * MyStyle_Notice::recreate($data_array);`. See the to_array function below
	 * for the expected array structure.
	 *
	 * @param array $array An array containing the Notice data.
	 * @return \self Works like a constructor.
	 */
	public static function recreate( $array ) {
		$instance = new self();

		$instance->notice_key  = $array['notice_key'];
		$instance->message     = $array['message'];
		$instance->type        = $array['type'];
		$instance->dismissible = $array['dismissible'];
		if ( isset( $array['remind_when'] ) ) {
			$instance->remind_when = $array['remind_when'];
		}

		return $instance;
	}

	/**
	 * Gets the value of notice_key.
	 *
	 * @return string Returns the notice_key.
	 */
	public function get_notice_key() {
		return $this->notice_key;
	}

	/**
	 * Gets the value of message.
	 *
	 * @return string Returns the Notice message.
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * Gets the value of type.
	 *
	 * @return string Returns the Notice type.
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Gets the value of dismissible.
	 *
	 * @return string Returns whether or not the Notice is dismissible.
	 */
	public function is_dismissible() {
		return $this->dismissible;
	}

	/**
	 * Converts the class into an array for inserting into a WordPress option.
	 *
	 * @returns array An array representation of the object.
	 */
	public function to_array() {
		$data_array = array(
			'notice_key'  => $this->notice_key,
			'message'     => $this->message,
			'type'        => $this->type,
			'dismissible' => $this->dismissible,
			'remind_when' => $this->remind_when,
		);

		return $data_array;
	}

	/**
	 * Returns whether or not the notice is dismissed.
	 *
	 * @return boolean Whether or not the notice is dismissed.
	 */
	public function is_dismissed() {
		$ret        = false;
		$dismissals = get_option( MYSTYLE_NOTICES_DISMISSED_NAME, array() );
		if ( array_key_exists( $this->notice_key, $dismissals ) ) {
			$remind_on = $dismissals[ $this->notice_key ];
			if ( null !== $remind_on ) {
				if ( $remind_on > time() ) {
					$ret = true;
				}
			} else {
				$ret = true;
			}
		}

		return $ret;
	}

}
