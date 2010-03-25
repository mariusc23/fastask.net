<?php if (!isset($quote)): ?>
<h1 class="quote-header">
Unknown quote
</h1>
<?php return;
endif; ?>
<?php include('quote_single.php'); ?>
<div class="list-note<?php if ($quotes): ?> border<?php endif; ?>">
Looking for other great quotes?<br/>
<?php if ($quotes): ?>
Check out related quotes below or <a href="<?php print Url::site('quote/top'); ?>">click to see top rated quotes</a>.
<?php else: ?>
Check out our <a href="<?php print Url::site('quote/top'); ?>">top rated quotes</a>.
<?php endif; // if ($quotes) ?>
</div>
<div class="quote-list quote-list-quote">
<?php foreach ($quotes as $quote): ?>
<?php include('quote_single.php'); ?>
<?php endforeach; ?>
</div><!-- /.quote-list-author -->
<div class="feed-icons">
    <a href="/rss.xml"></a>
</div><!-- /.feed-icons -->
