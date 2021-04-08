<?php
/**
 * Class for the MyStyle Design Tag Shortcode.
 *
 * @package MyStyle
 * @since 3.17.4
 */

/**
 * MyStyle_Design_Tag_Shortcode class.
 */
abstract class MyStyle_Design_Tag_Shortcode {

	/**
	 * Output the design tag shortcode.
	 */
	public static function output( $atts ) {
        
        $wp_user = wp_get_current_user();
        
        $session = MyStyle()->get_session();

        $pager = 0 ;
        $limit = 4 ;
        
        if(isset($_GET['pager'])) {
            $pager = $_GET['pager'] ;
            $offset = ($pager * $limit) ;
        }
        
		$terms = get_terms( array(
            'taxonomy' => MYSTYLE_TAXONOMY_NAME,
            'hide_empty' => false,
            'number' => $limit,
            'offset' => $offset
        ) );
        
        $terms_count = count($terms) ;
        
        for($i=0;$i < $terms_count;$i++) {
            $design_objs = MyStyle_DesignManager::get_designs_by_term_id(
                $terms[$i]->term_id,
                $wp_user,
                $session,
                6,
                1
            );
            
            if(count($design_objs) == 0) {
                unset($terms[$i]) ;
            }
            else {
                $terms[$i]->designs = $design_objs ;
            }
        }
        
        //echo '<pre>' ; var_dump($terms) ; die() ;
        
        $pager_array = self::pager($pager, $limit, $terms_count) ;
        $next = $pager_array['next'] ;
        $prev = $pager_array['prev'] ;
        
        
        ob_start();
        require MYSTYLE_TEMPLATES . 'design-tag-index.php' ;
        $out = ob_get_contents();
        ob_end_clean();

		return $out;
	}
    
    
    public function pager($pager, $limit, $term_count) {
        $next = $pager+1 ;
        $prev = $pager-1 ;
        
        if($term_count < $limit) {
            $next = null ;
        }
        
        if($pager == 0) {
            $prev = null ;
        }
        
        return array( 'prev' => $prev, 'next' => $next ) ;
    }

	

}
