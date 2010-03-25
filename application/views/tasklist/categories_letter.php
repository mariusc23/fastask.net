<div class="category-row">
    <a class="last" href="<?php print Url::site('category'); ?>">see alphabet</a>
    <h2><?php print $letter; ?></h2>
    <ul>
    <?php $count = 0; $lists = 0;
    foreach ($categories as $category):
        $count++; ?>
        <li><a href="<?php print Url::site('category/id/' . $category->id); ?>"><?php print $category->name ?></a></li>
        <?php if ($count % $count_split == 0): $lists++; ?>
        </ul><?php if ($lists % 4 == 0): ?>
        <div class="hr"></div>
        <?php endif; ?>

        <ul><?php endif;
    endforeach; ?>
    </ul>
    </div>
</div>
