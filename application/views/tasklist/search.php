<div class="quote-list">
<?php if (isset($quotes) && $quotes): ?>
<?php foreach ($quotes as $quote): ?>

<div class="quote">
<div class="rating-wrap">
<div class="msg"></div>
<ul class="rating">
    <li class="current" style="width:<?php print $quote->voteaverage->average; ?>%"><?php print $quote->voteaverage->average; ?>%</li>
    <li><a href="#1" title="poor" class="one">1</a></li>
    <li><a href="#2" title="fair" class="two">2</a></li>
    <li><a href="#3" title="okay" class="three">3</a></li>
    <li><a href="#4" title="good" class="four">4</a></li>
    <li><a href="#5" title="awesome!" class="five">5</a></li>
</ul>
</div>
<div class="id">
<a href="<?php print Url::site('quote/id/' . $quote->id); ?>" title="Details about this quote"><?php print $quote->id; ?></a>
</div>
<div class="quote-inner">
<div class="text">
<?php print $quote->text; ?>
</div>
<div class="categories">
<?php if ($categories_list_bolded[$quote->id]) foreach ($categories_list_bolded[$quote->id] as $category) {
    print '<a href="' . Url::site('category/id/' . $category->id) . '">' . $category->name . '</a> ';
} ?>
</div>
<div class="author"><a href="<?php print Url::site('author/id/' . $quote->author->id); ?>" title="See quotes by <?php print $quote->author->name ?>"><?php print $quote->author->short_name ?></a></div>
<br class="after-author" />
<?php if (isset($quote->user)): ?>
<a href="<?php print Url::site('quote/edit/' . $quote->id); ?>" title="Edit this quote">Edit</a>
<?php endif; ?>
</div><!-- /.quote-inner -->
</div><!-- /.quote -->

<?php endforeach; ?>
<?php print $pager; ?>
<?php if (isset($last_page)): ?>
<div class="list-note border">
Looking for other great quotes?<br/>
Check out <a href="<?php print Url::site('quote/top'); ?>">top rated quotes</a> below.
</div>
<?php if ($top_quotes) foreach ($top_quotes as $quote): ?>
<?php include('quote_single.php'); ?>
<?php endforeach; ?>
<?php endif; ?>
<?php else: ?>
<div class="list-note border">
Sorry, couldn't find any quotes.<br/>
Try a broader search by entering fewer words, or check out <a href="<?php print Url::site('quote/top'); ?>">top rated quotes</a> below.
</div>
<?php if ($top_quotes) foreach ($top_quotes as $quote): ?>
<?php include('quote_single.php'); ?>
<?php endforeach; ?>
<?php endif; ?>
</div><!-- /.quote-list -->
