<?php
/**
 * Class for the MyStyle Design Tag Shortcode.
 *
 * @package MyStyle
 * @since 3.17.5
 */

/**
 * MyStyle_Design_Tag_Shortcode class.
 */
class MyStyle_Design_Tag_Shortcode extends MyStyle_Design_Term_Shortcode {

	/**
	 * The default number of designs to show at a time when viewing a single
	 * term.
	 *
	 * @var int
	 */
	const DEFAULT_TERM_DESIGN_LIMIT = 250;

	/**
	 * The default number of designs to show at a time when viewing the term
	 * index.
	 *
	 * @var int
	 */
	const DEFAULT_INDEX_DESIGN_LIMIT = 4;

	/**
	 * The default number of terms to show per page when viewing the term
	 * index.
	 *
	 * @var int
	 */
	const DEFAULT_INDEX_TERM_LIMIT = 5;

	/**
	 * Singleton instance.
	 *
	 * @var MyStyle_Design_Tag_Shortcode
	 */
	private static $instance;

	/**
	 * Output the design tag shortcode.
	 *
	 * @param array $atts The attributes set on the shortcode.
	 * @returns string Returns the output for the shortcode as a string of HTML.
	 */
	public static function output( $atts ) {
		$instance = self::get_instance();
		$instance->init( $atts );
		$out = $instance->build_output( $atts );

		return $out;
	}

	/**
	 * Private method that initializes the object.
	 *
	 * @param array $atts The attributes set on the shortcode.
	 */
	private function init( $atts ) {
		$this->term_manager       = new MyStyle_Design_Tag_Manager();
		$this->taxonomy           = MYSTYLE_TAXONOMY_NAME;
		$this->template           = MYSTYLE_TEMPLATES . 'design-tag.php';
		$this->term_query_param   = 'tag_term';
		$this->term_design_limit  = self::DEFAULT_TERM_DESIGN_LIMIT;
		$this->index_design_limit = self::DEFAULT_INDEX_DESIGN_LIMIT;
		$this->index_term_limit   = self::DEFAULT_INDEX_TERM_LIMIT;

		$atts_clean = $this->get_attributes( $atts );
		if ( ! $atts_clean['show_designs'] ) {
			$this->template = MYSTYLE_TEMPLATES . 'design-tag-index.php';
		}
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return MyStyle_Design_Tag_Shortcode
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
