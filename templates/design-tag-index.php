<div id="mystyle-design-tag-index-wrapper" class="woocommerce design-tags">
	<?php foreach ( $terms as $term ) : ?>
	<h3 class="mystyle-design-id"><a href="<?php print get_term_link( $term ); ?>" title="<?php print $term->name; ?> Gallery"><?php print $term->name; ?></a></h3>
	<ul>
		<?php foreach ( $term->designs as $design ) : ?>
			<?php
			$design_url = MyStyle_Design_Profile_page::get_design_url( $design );
			$user       = get_user_by( 'id', $design->get_user_id() );
			?>
		<li>
			<a href="<?php print $design_url; ?>" title="<?php print ( $design->get_title() != '' ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?>">
				<img alt="<?php print ( $design->get_title() != '' ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?> Image" src="<?php print $design->get_thumb_url(); ?>" />
				<?php print ( $design->get_title() != '' ? $design->get_title() : 'Custom Design ' . $design->get_design_id() ); ?>
			</a>
			<div>Designed by: <?php print $user->user_nicename; ?></div>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php endforeach; ?>
	<div class="pager">
		<?php if ( ! is_null( $prev ) ) : ?>
		<a href="?pager=<?php print $prev; ?>" title="Previous page">Previous</a>
		<?php endif; ?>
		<?php if ( ! is_null( $next ) ) : ?>
		<a href="?pager=<?php print $next; ?>" title="Next page">Next</a>
		<?php endif; ?>
	</div>
</div>
