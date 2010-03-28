<?php
$start = $current_page - PAGES_BEFORE;
$end = PAGES_AFTER;
if ($start < 1) {
    $end -= $start;
    $start = 1;
}
if ($end + $current_page - $total_pages > 0) {
    $start -= $end + $current_page - $total_pages - 1;
}
if ($start < 1) {
    $start = 1;
}

?>
<div class="pager">

    <?php if ($first_page !== FALSE): ?>
        <a href="<?php echo $page->url($first_page) ?>"><?php echo __('&laquo;') ?></a>
    <?php else: ?>
        <span class="first"><?php echo __('&laquo;') ?></span>
    <?php endif ?>

    <?php if ($previous_page !== FALSE): ?>
        <a href="<?php echo $page->url($previous_page) ?>"><?php echo __('&lsaquo;') ?></a>
    <?php else: ?>
        <span class="previous"><?php echo __('&lsaquo;') ?></span>
    <?php endif ?>

    <?php if ($start > 1): ?>
        <span class="omit">...</span>
    <?php endif ?>

    <?php for ($i = $start; $i < $current_page; $i++): ?>
        <a href="<?php echo $page->url($i) ?>"><?php echo $i ?></a>
    <?php endfor ?>

        <a class="current" href="<?php echo $page->url($i) ?>"><?php echo $i ?></a>

    <?php for ($i = $current_page + 1; ($i <= $total_pages) && ($i < $current_page + $end); $i++): ?>
        <a href="<?php echo $page->url($i) ?>"><?php echo $i ?></a>
    <?php endfor ?>

    <?php if ($i < $total_pages + 1): ?>
        <span class="omit">...</span>
    <?php endif ?>

    <?php if ($next_page !== FALSE): ?>
        <a href="<?php echo $page->url($next_page) ?>"><?php echo __('&rsaquo;') ?></a>
    <?php else: ?>
        <span class="next"><?php echo __('&rsaquo;') ?></span>
    <?php endif ?>

    <?php if ($last_page !== FALSE): ?>
        <a href="<?php echo $page->url($last_page) ?>"><?php echo __('&raquo;') ?></a>
    <?php else: ?>
        <span class="last"><?php echo __('&raquo;') ?></span>
    <?php endif ?>

</div><!-- .pager -->