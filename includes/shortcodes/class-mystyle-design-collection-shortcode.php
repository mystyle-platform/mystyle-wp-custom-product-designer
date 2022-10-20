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
abstract class MyStyle_Design_Collection_Shortcode {

	/**
	 * Output the design collection shortcode.
	 *
	 * @param array $atts The attributes set on the shortcode.
	 */
	public static function output( $atts ) {
        global $wp_query ;
        
        $term       = false ;
        $page_num   = 1 ;
        
        if( isset($wp_query->query['collection_term']) ) {
            $term = $wp_query->query['collection_term'] ;
            if( preg_match( '/\//', $term) ) {
                $url_array  = explode('/', $term ) ;
                if($url_array[0] == 'page' ) {
                    $page_num   = $url_array[1] ;
                    $term       = false ;
                }
                else {
                    $term       = $url_array[0] ;
                    $page_num   = $url_array[2] ;
                }
                
            }
        }
        
        $mystyle_pager = new MyStyle_Pager();
        
		$wp_user = wp_get_current_user();

		//$session = MyStyle()->get_session();
		$session = null; // No longer using sessions for design-collection shortcode.
        
        $show_designs           = true ;
        $collections_per_page   = 24 ;
        $per_collection         = 4 ;
        $sort_by                = "qty" ;
        
        if(isset($atts['show_designs']) && $atts['show_designs'] == 'false') {
            $show_designs = false ;    
        }
        
        if(isset($atts['per_collection'])) {
            $per_collection = $atts['per_collection'] ;    
        }
        
        if(isset($atts['collections_per_page'])) {
            $collections_per_page = $atts['collections_per_page'] ;    
        }
        
		$pager        = 0 ;
		$limit        = $per_collection ;
		$term_limit   = $collections_per_page ;
		$offset       = 0;

		// phpcs:enable WordPress.CSRF.NonceVerification.NoNonceVerification, WordPress.VIP.SuperGlobalInputUsage.AccessDetected
        
        $all_terms = get_terms(
			array(
				'taxonomy'   => MYSTYLE_COLLECTION_NAME,
				'hide_empty' => true
			)
		);
        
        if( $term ) {
            $terms = array() ;
            $terms[] = get_term_by( 'slug', $term, MYSTYLE_COLLECTION_NAME) ;
        }
        else {
            if ( isset( $_GET['pager'] ) && $_GET['pager'] != 0 ) {
                $pager  = intval( $_GET['pager'] );
                $offset = ( $pager * $term_limit );
            }
            elseif( $page_num ) {
                $pager = ( $page_num - 1 ) ;
                $offset = ( $pager * $term_limit );
            }
            
        
            $mystyle_pager->set_current_page_number( ( $pager + 1 ) );
            
            $terms = get_terms(
                array(
                    'taxonomy'   => MYSTYLE_COLLECTION_NAME,
                    'hide_empty' => true,
                    'number'     => $term_limit,
                    'offset'     => $offset,
                )
            );
            
            $total_terms_count = count( $all_terms );
            
        }
        
        $terms_count = count( $terms );

        if( $show_designs ){
            
            if($terms_count == 1) {
                
                if ( ( isset( $_GET['pager'] ) ) && ( null !== $_GET['pager'] ) ) {
                    $pager  = intval( $_GET['pager'] );
                    $page_num = $_GET['pager'] + 1 ;
                }
                elseif( $page_num ) {
                    $pager = ( $page_num - 1 ) ;
                    $offset = ( $pager * $term_limit );
                }
                
                $limit = 50 ; //increase number of collections on term pages
                
                $total_design_count = MyStyle_DesignManager::get_total_term_design_count( $terms[0]->term_taxonomy_id, $wp_user, $session ) ;
                
                $mystyle_pager->set_items_per_page( $limit ) ;
        
                // Total items.
                $mystyle_pager->set_total_item_count(
                    $total_design_count
                );
                
                $pager_array = self::pager( $pager, $limit, $total_design_count ); 
            }
            elseif( count($all_terms) > $term_limit ) {
                $mystyle_pager->set_items_per_page( $term_limit ) ;
                
                $total_terms_count = count( $all_terms ) ;

                // Total items.
                $mystyle_pager->set_total_item_count(
                    $total_terms_count
                );
                
                
            }
            else {
                $total_terms_count = count( $all_terms ) ;
                
                $mystyle_pager->set_items_per_page( $term_limit ) ;
                
                // Total items.
                $mystyle_pager->set_total_item_count(
                    $total_terms_count
                );
            }
            
            for ( $i = 0; $i < $terms_count; $i++ ) {
                
                $designs = MyStyle_DesignManager::get_designs_by_term_id(
                    $terms[ $i ]->term_taxonomy_id,
                    $wp_user,
                    $session,
                    $limit,
                    ( !$term ? 1 : $page_num )
                );

                
                if ( 0 === count( $designs ) ) {
                    $terms[ $i ]->designs = array() ;
                } else {
                    $terms[ $i ]->designs = $designs;
                }
            }

            $mystyle_pager->set_current_page_number( $page_num ) ;
            
        }
        
		ob_start();
		require MYSTYLE_TEMPLATES . 'design-collection-index.php';
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
        
        $total_shown = ( $pager * $limit ) ;
        
        if( ($term_count - $total_shown) <= $limit ) {
            $next = null ;
        }

		return array(
			'prev' => $prev,
			'next' => $next,
		);
	}

}
