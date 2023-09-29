<?php

/**
 * The template for displaying the MyStyle design tag index.
 *
 * NOTE: THIS FILE IS NOT YET THEMEABLE.
 *
 * @package MyStyle
 * @since 3.17.5
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
?>

<div id="mystyle-design-tag-index-wrapper" class="mystyle-design-tag-index woocommerce design-tags<?php print($show_designs ? ' show-designs' : ''); ?>">
    <?php if (!$term) : ?>
        <div class="mystyle-sort">
            <form name="mystyle-sort-form" method="get" class="mystyle-sort-form" action="<?php print get_permalink(get_the_ID()); ?>">
                <label for="mystyle-sort-select">Sort tags by:</label>
                <select name="sort_by" class="mystyle-sort-select">
                    <option value="qty" <?php print($sort_by == 'qty' ? ' selected' : ''); ?>>Quantity</option>
                    <option value="alpha" <?php print($sort_by == 'alpha' ? ' selected' : ''); ?>>Alphabetical</option>
                </select>
            </form>
        </div>
    <?php endif; ?>

    <?php
    // Determine the number of design tags to display per page and the current page number.
    $designs_per_page = 50; 
    $current_page = get_query_var('paged') ? get_query_var('paged') : 1; // Get the current page.

    // Calculate the offset to start displaying the tags for the current page.
    $offset = ($current_page - 1) * $designs_per_page;

    // Slice the array to get only the tags for the current page.
    $page_terms = array_slice($terms, $offset, $designs_per_page);

    foreach ($page_terms as $term) :
    ?>

        <?php if ($show_designs) : ?>
            <?php $term_name = preg_replace('/\-/', ' ', $term->name); ?>
            <h3 class="mystyle-tag-id"><a href="<?php echo esc_url(get_term_link($term)); ?>" title="<?php echo esc_attr($term_name); ?> Gallery"><?php echo esc_html($term_name); ?></a></h3>
            <ul>
                <?php
                foreach ($term->designs as $design) :
                    $design_url = MyStyle_Design_Profile_page::get_design_url($design);
                    $user = get_user_by('id', $design->get_user_id());
                    $options = get_option(MYSTYLE_OPTIONS_NAME, array()); // Get WP Options table Key of this option.
                    $product_phrase = (array_key_exists('alternate_design_tag_collection_title', $options)) ? $options['alternate_design_tag_collection_title'] : '';

                    if (empty($design->get_title())) {
                        $title = 'Design' . ' ' . $design->get_design_id() . ' ' . $product_phrase;
                    } else {
                        $title = $design->get_title() . ' ' . $product_phrase;
                    }
                ?>
                    <li>
                        <a href="<?php echo esc_url($design_url); ?>" title="<?php echo $title; ?>">
                            <img alt="<?php echo $title; ?> Image" src="<?php echo esc_url($design->mystyle_design_Url()); ?>" />
                            <?php echo esc_html((null !== $design->get_title()) ? $design->get_title() : 'Custom Design ' . $design->get_design_id()); ?>
                        </a>
                        <div class="mystyle-design-author">Designed by: <?php echo esc_html($user->user_nicename); ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <a href="<?php echo esc_url(get_term_link($term)); ?>" title="<?php echo esc_attr($term->name); ?> Gallery"><?php echo esc_html($term->name); ?></a>
            <?php endif; ?>&nbsp;
        <?php endforeach; ?>

        <nav class="woocommerce-pagination">
            <?php
            // Add the following code to set up pagination.
            $pagination_args = array(
                'base'      => esc_url(add_query_arg('paged', '%#%')),
                'format'    => '?paged=%#%', // URL format for pagination.
                'total'     => ceil(count($terms) / $designs_per_page), // Calculate the total number of pages.
                'current'   => $current_page, // Set the current page.
                'prev_text' => '&larr;',
                'next_text' => '&rarr;',
                'type'      => 'list',
                'end_size'  => 3,
                'mid_size'  => 3,
            );

            echo paginate_links($pagination_args); // Display pagination.
            ?>
        </nav>
</div>