<div id="mystyle-design-collection-index-wrapper" class="design-collection-index">
    
    <div class="collections-menu">
        <ul>
            <?php foreach($all_terms as $term_menu_item) : ?>
            <li>
                <a href="/design-collections/<?php print $term_menu_item->slug ; ?>" title="<?php print $term_menu_item->name ; ?>"><?php print $term_menu_item->name ; ?></a>
            </li>
            <?php endforeach ; ?>
        </ul>
    </div>
    
    <div class="collections-content">
        <?php foreach($terms as $term) : ?>
            
        <div class="collection-row">
            <?php //if( count($terms) > 1 ) : ?>
            <h3>
                <a href="/design-collections/<?php print $term->slug ; ?>" title="<?php print $term->name ; ?>"><?php print $term->name ; ?></a>
            </h3>
            <?php //endif ; ?>
            <?php $count = count($term->designs) ; ?>
            <?php foreach($term->designs as $design) : ?>
            <?php
			$design_url = MyStyle_Design_Profile_page::get_design_url( $design );
			$user       = get_user_by( 'id', $design->get_user_id() );
			?>
            <div class="design-tile">
                
                <div class="design-img">
                    
                    <a href="<?php echo esc_url( $design_url ); ?>" title="<?php echo esc_attr( ( null !== $design->get_title() ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?>">
                        <img alt="<?php echo esc_html( ( null !== $design->get_title() ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?> Image" src="<?php echo esc_url( $design->get_thumb_url() ); ?>" />
                        <?php echo esc_html( ( null !== $design->get_title() ) ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?>
                    </a>
                    <div>Designed by: <?php echo esc_html( $user->user_nicename ); ?></div>
                    
                </div>
                
            </div>
            <?php endforeach ; ?>
            <?php if( count($terms) > 1 && $count > $limit ) : ?>
            <div class="design-tile view-more">
                <a href="/design-collections/<?php print $term->slug ; ?>" title="<?php print $term->name ; ?>">View More</a>
            </div>
            <?php endif ; ?>
        </div>
        <?php endforeach ; ?>
        <div class="pager">
            <?php if ( ! is_null( $prev ) ) : ?>
            <a href="<?php echo esc_url( '?pager=' . $prev ); ?>" title="Previous page">Previous</a>
            <?php endif; ?>
            <?php if ( ! is_null( $next ) ) : ?>
            <a href="<?php echo esc_url( '?pager=' . $next ); ?>" title="Next page">Next</a>
            <?php endif; ?>
        </div>
    </div>
    
    
</div>