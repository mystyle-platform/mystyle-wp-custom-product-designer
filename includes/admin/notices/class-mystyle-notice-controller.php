<?php

/**
 * MyStyle Notices class.
 * The MyStyle Notices Controller class sets up and controls the MyStyle
 * notices.
 *
 * @package MyStyle
 * @since 1.2
 */
class MyStyle_Notice_Controller {

	/**
	 * Singleton class instance
	 * @var MyStyle_Notice_Controller
	 */
	private static $instance;

	/**
	 * Constructor, constructs the MyStyle_Notice_Controller and registers
	 * actions.
	 */
	public function __construct() {
		add_action('admin_notices', array(&$this, 'admin_notices'));
		add_action('wp_ajax_mystyle_set_notice_pref', array(&$this, 'set_notice_pref_callback'));
	}

	/**
	 * Add Notices in the administrator. Notices may be stored in the
	 * mystyle_notices. Once the notices have been displayed, delete them from
	 * the database.
	 */
	public function admin_notices() {

		$screen = get_current_screen();
		$screen_id = (!empty($screen) ? $screen->id : null );

		// Start the notices array off with any that are queued.
		$notices = mystyle_notice_pull_all_queued();

		if (!MyStyle_Options::are_keys_installed()) {
			if ($screen_id != 'toplevel_page_mystyle') {
				$notices[] = MyStyle_Notice::create('nag_configure', 'You\'ve activated the MyStyle Plugin! Now let\'s <a href="options-general.php?page=mystyle">configure</a> it!');
			}
		} else {
			if (!MyStyle::site_has_customizable_products()) {
				if ($screen_id == 'toplevel_page_mystyle') {
					$notices[] = MyStyle_Notice::create('nag_no_customizable_product', 'You\'re configured and ready to go but you still need to add a customizable product!');
				}
			}
		}

		//print the notices
		$out = '';
		foreach ($notices as $notice) {
			if (!$notice->is_dismissed()) {
				$out .= '<div id="' . $notice->get_notice_key() . '" class="' . $notice->get_type() . ' notice notice-' . $notice->get_type() . ' is-dismissible mystyle-notice">';
				$out .= '<p>' .
						'<strong>MyStyle:</strong> ' .
						$notice->get_message() .
						'</p>';
				if ($notice->is_dismissible()) {
					$out .= '<p>' .
							'<button type="button" onclick="mystylePostNoticePref(this, \'' . $notice->get_notice_key() . '\', \'+30 days\');">Remind Me Later</button>' .
							'<button type="button" onclick="mystylePostNoticePref(this, \'' . $notice->get_notice_key() . '\', null);">Don\'t Remind Me Again</button>' .
							'</p>';
				}
				$out .= '</div>';
			}
		}
		echo $out;
	}

	/**
	 * Called via ajax to dismiss a notice. Registered in the constructor above.
	 */
	public function set_notice_pref_callback() {
		//Get the variables from the post request
		$notice_key = $_POST['notice_key'];
		$remind_when = $_POST['remind_when'];

		//Determine when to remind on
		$remind_on = null;
		if ($remind_when != null) {
			$remind_on = strtotime($remind_when);
		}

		//Add the dismissal to the database
		$dismissals = get_option(MYSTYLE_NOTICES_DISMISSED_NAME, array());
		$dismissals[$notice_key] = $remind_on;
		update_option(MYSTYLE_NOTICES_DISMISSED_NAME, $dismissals);

		//Build the return array
		$ret = array();
		$ret['notice_key'] = $notice_key;
		$ret['status'] = 'success';

		//return the json
		echo json_encode($ret);
		wp_die(); //terminate immediately and return a proper response
	}

	/**
	 * Gets the singleton instance.
	 * @return MyStyle_Customize_Page Returns the singleton instance of
	 * this class.
	 */
	public static function get_instance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
