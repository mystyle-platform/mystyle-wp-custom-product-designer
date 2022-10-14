<?php
/**
 * Class for the MyStyle Design Collection Shortcode.
 *
 * @package MyStyle
 * @since 3.18.5
 */

/**
 * MyStyle_Design_Collection_Shortcode class.
 */
class MyStyle_Design_Collection_Shortcode extends MyStyle_Design_Term_Shortcode {

	/**
	 * The default number of designs to show at a time when viewing a single
	 * term.
	 *
	 * @var int
	 */
	const DEFAULT_TERM_DESIGN_LIMIT = 20;

	/**
	 * The default number of designs to show at a time when viewing the term
	 * index.
	 *
	 * @var int
	 */
	const DEFAULT_INDEX_DESIGN_LIMIT = 4;

	/**
	 * The default number of terms to show at a time when viewing the term
	 * index.
	 *
	 * @var int
	 */
	const DEFAULT_INDEX_TERM_LIMIT = 10;

	/**
	 * Singleton instance.
	 *
	 * @var MyStyle_Design_Collection_Shortcode
	 */
	private static $instance;

	/**
	 * Output the design collection shortcode.
	 *
	 * @param array $atts The attributes set on the shortcode.
	 * @returns string Returns the output for the shortcode as a string of HTML.
	 */
	public static function output( $atts ) {
		$instance = self::get_instance();
		$instance->init();
		$out = $instance->build_output( $atts );

		return $out;
	}

	/**
	 * Private method that initializes the object.
	 */
	private function init() {
		$this->term_manager       = new MyStyle_Design_Collection_Manager();
		$this->taxonomy           = MYSTYLE_COLLECTION_NAME;
		$this->template           = MYSTYLE_TEMPLATES . 'design-collection.php';
		$this->term_query_param   = 'collection_term';
		$this->term_design_limit  = self::DEFAULT_TERM_DESIGN_LIMIT;
		$this->index_design_limit = self::DEFAULT_INDEX_DESIGN_LIMIT;
		$this->index_term_limit   = self::DEFAULT_INDEX_TERM_LIMIT;
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return MyStyle_Design_Collection_Shortcode
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
