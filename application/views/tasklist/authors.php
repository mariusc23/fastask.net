<?php
$auts_by_initial = array();
foreach ($authors as $author) {
    $current_initial = mb_strtoupper(mb_substr($author->last_name, 0, 1));

    if (!isset($auts_by_initial[ord($current_initial)])) $auts_by_initial[ord($current_initial)] = array();
    $auts_by_initial[ord($current_initial)][] = $author;
}
$ord_Z = ord('Z');
?>

<!-- column #1 -->
<div class="author-row">
<?php $total = 0; for ($i = ord('A'); $i <= $ord_Z; $i++):
    if (!isset($auts_by_initial[$i])) continue; ?>
    <div class="letter">
    <h2><?php print chr($i); ?></h2>

    <ul>
    <?php $count = 0;
    foreach ($auts_by_initial[$i] as $author):
        $count++;
        if ($count > AUTHORS_LIST_COUNT): ?>
        <li class="last"><a href="<?php print Url::site('author/letter/' . chr($i)); ?>">more</a></li>
    <?php break;
        endif; ?>
        <li><a href="<?php print Url::site('author/id/' . $author->id); ?>" title="See quotes by <?php print $author->name ?>"><?php print $author->short_name ?></a></li>
    <?php endforeach; ?>
    </ul>

    </div>
    <?php $total++; if ($total % 4 == 0): ?>
</div><div class="author-row">
    <?php endif;
endfor; ?>
</div>
