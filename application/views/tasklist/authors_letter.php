<div class="author-row">
    <a class="last" href="<?php print Url::site('author'); ?>">see alphabet</a>
    <h2><?php print $letter; ?></h2>
    <ul>
    <?php $count = 0; $lists = 0;
    foreach ($authors as $author):
        $count++; ?>
        <li><a href="<?php print Url::site('author/id/' . $author->id); ?>" title="See quotes by <?php print $author->name ?>"><?php print $author->short_name ?></a></li>
        <?php if ($count % $count_split == 0): $lists++; ?>
        </ul><?php if ($lists % 4 == 0): ?>
        <div class="hr"></div>
        <?php endif; ?>

        <ul><?php endif;
    endforeach; ?>
    </ul>
    </div>
</div>
