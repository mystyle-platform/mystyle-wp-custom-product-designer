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
abstract class MyStyle_Design_Tag_Shortcode {

	/**
	 * Output the design tag shortcode.
	 *
	 * @param array $atts The attributes set on the shortcode.
	 */
	public static function output( $atts ) {

		$wp_user = wp_get_current_user();

		$session = MyStyle()->get_session();
        
        $show_designs = true ;
        $sort_by = "qty" ;
        $tags_per_page = 25 ;
        $per_tag = 3 ;
        
        if(isset($atts['show_designs']) && $atts['show_designs'] == 'false') {
            $show_designs = false ;    
        }
        
        if(isset($atts['per_tag'])) {
            $per_tag = $atts['per_tag'] ;    
        }
        
        if(isset($atts['tags_per_page'])) {
            $tags_per_page = $atts['tags_per_page'] ;    
        } 
        
        if( isset( $_GET['sort_by'] ) ) {
            $sort_by = sanitize_text_field( $_GET['sort_by'] ) ;
        }
        
		$pager  = 0;
		$limit  = $tags_per_page ;
		$offset = 0;

		// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		if ( isset( $_GET['pager'] ) ) {
			$pager  = intval( $_GET['pager'] );
			$offset = ( $pager * $limit );
		}
        elseif(get_query_var('paged')) {
            $pager = ( get_query_var('paged') - 1 ) ;
			$offset = ( $pager * $limit );
        }
		// phpcs:enable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.SuperGlobalInputUsage.AccessDetected
        
        
        $sort_by_slug = 'count' ;
        $sort_by_order = 'DESC' ; 
        
        if( $sort_by === 'alpha' ) {
            $sort_by_slug = 'name' ; 
            $sort_by_order = 'ASC' ;
        }
        
		$terms = get_terms(
			array(
				'taxonomy'   => MYSTYLE_TAXONOMY_NAME,
				'hide_empty' => false,
                'orderby'    => $sort_by_slug,
                'order'      => $sort_by_order,
				'number'     => $limit,
				'offset'     => $offset,
			)
		);
        
        $total_terms = get_terms(
			array(
				'taxonomy'   => MYSTYLE_TAXONOMY_NAME,
				'hide_empty' => false,
                'orderby'    => $sort_by_slug,
                'order'      => $sort_by_order,
                'fields'     => 'tt_ids'
			)
		);

		$terms_count = count( $terms );
        $total_terms_count = count( $total_terms ) ;
        
        if( $show_designs ){
            
            for ( $i = 0; $i < $terms_count; $i++ ) {
                $designs = MyStyle_DesignManager::get_designs_by_term_id(
                    $terms[ $i ]->term_taxonomy_id,
                    $wp_user,
                    $session,
                    $per_tag,
                    1
                );
                
                $design_count = count( $designs ) ;
                
                if ( 0 === $design_count ) {
                    unset( $terms[ $i ] );
                } else {
                    $terms[ $i ]->designs = $designs ; 
                }
            }
            
            for ( $i = 0; $i < $total_terms_count; $i++ ) {
                $designs = MyStyle_DesignManager::get_designs_by_term_id(
                    $total_terms[ $i ],
                    $wp_user,
                    $session,
                    $per_tag,
                    1
                );
                
                $design_count = count( $designs ) ;
                
                if ( 0 === $design_count ) {
                    unset( $total_terms[ $i ] );
                }
            }
            
            $total_terms_count = count( $total_terms ) ;
            
        }
		
        $mystyle_pager = new MyStyle_Pager();
        
        $mystyle_pager->set_items_per_page( ( is_null( $tags_per_page ) ? 1000 : $tags_per_page ) ) ;
        
        $mystyle_pager->set_current_page_number( ( $pager + 1 ) );
        
        //$mystyle_pager->set_items( $terms );

		// Total items.
		$mystyle_pager->set_total_item_count(
			$total_terms_count
		);

		$pager_array = self::pager( $pager, $limit, $terms_count );
		$next        = $pager_array['next'];
		$prev        = $pager_array['prev'];

		ob_start();
		require MYSTYLE_TEMPLATES . 'design-tag-index.php';
		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}

	/**
	 * Helper method that returns the pager array.
	 *
	 * @param int $pager The current page.
	 * @param int $limit The limit.
	 * @param int $term_count The total number of terms.
	 * @return array Returns the pager array.
	 */
	private static function pager( $pager, $limit, $term_count ) {
		$next = $pager + 1;
		$prev = $pager - 1;

		if ( $term_count < $limit ) {
			$next = null;
		}

		if ( 0 === $pager ) {
			$prev = null;
		}

		return array(
			'prev' => $prev,
			'next' => $next,
		);
	}

}
