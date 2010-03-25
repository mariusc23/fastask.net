<?php
$cats_by_initial = array();
foreach ($categories as $category) {
    $current_initial = mb_strtoupper(mb_substr($category->name, 0, 1));

    if (!isset($cats_by_initial[ord($current_initial)])) $cats_by_initial[ord($current_initial)] = array();
    $cats_by_initial[ord($current_initial)][] = $category;
}
$ord_Z = ord('Z');
?>

<!-- column #1 -->
<div class="category-row">
<?php $total = 0; for ($i = ord('A'); $i <= $ord_Z; $i++):
    if (!isset($cats_by_initial[$i])) continue; ?>
    <div class="letter">
    <h2><?php print chr($i); ?></h2>

    <ul>
    <?php $count = 0;
    foreach ($cats_by_initial[$i] as $category):
        $count++;
        if ($count > CATEGORIES_LIST_COUNT): ?>
        <li class="last"><a href="<?php print Url::site('category/letter/' . chr($i)); ?>">more</a></li>
    <?php break;
        endif; ?>
        <li><a href="<?php print Url::site('category/id/' . $category->id); ?>"><?php print $category->name ?></a></li>
    <?php endforeach; ?>
    </ul>

    </div>
    <?php $total++; if ($total % 4 == 0): ?>
</div><div class="category-row">
    <?php endif;
endfor; ?>
</div>

