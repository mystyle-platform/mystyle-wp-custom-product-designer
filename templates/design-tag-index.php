<div class="design-tags">
    <?php foreach($terms as $term): ?>
    <h3><a href="#" title="<?php print $term->name ; ?> Gallery"><?php print $term->name ; ?></a></h3>
    <ul>
        <li>Sample Design</li>
    </ul>
    <?php endforeach ; ?>
    <div class="pager">
        <?php if(!is_null($prev)) : ?>
        <a href="?pager=<?php print $prev ; ?>" title="Previous page">Previous</a>
        <?php endif ; ?>
        <?php if(!is_null($next)) : ?>
        <a href="?pager=<?php print $next ; ?>" title="Next page">Next</a>
        <?php endif ; ?>
    </div>
</div>