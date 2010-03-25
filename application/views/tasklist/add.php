<ul class="messages">
<?php if ($error): ?>
    <?php if ($action == 'add'): ?>
    <li class="error">Error adding quote</li>
    <?php else: ?>
    <li class="error">Error updating quote</li>
    <?php endif; ?>
<?php elseif($_POST): ?>
    <?php if ($action == 'add'): ?>
    <li><a href="<?php print Url::site('quote/id/' . $quote->id) ?>">Added quote <?php print $quote->id; ?></a></li>
    <?php else: ?>
    <li><a href="<?php print Url::site('quote/id/' . $quote->id) ?>">Updated quote <?php print $quote->id; ?></a></li>
    <?php endif; ?>
<?php endif; ?>
</ul>

<form method="post" accept-charset="UTF-8" action="<?php if ($action == 'add') {
    print Url::site('quote/add');
} else {
    print Url::site('quote/edit/' . $id);
} ?>">
<div class="form">
    <div class="text">
        <label><span><?php if ($action == 'add'): ?>Add quote:<?php else: ?>Edit quote <?php print $quote->id; ?>:<?php endif; ?></span>
            <textarea name="text" rows="8" cols="55"><?php print $text ?></textarea>
        </label>
    </div>

    <div class="meta">
        <label><span>Categories (comma separated):</span>
            <textarea type="text" rows="2" cols="34" name="categories" ><?php print $categories ?></textarea>
        </label>

        <label class="author"><span>Author:</span>
            <input type="text" value="<?php print $author ?>" size="24" name="author" autocomplete="off" />
        </label>

        <div class="submit">
            <input type="submit" value="Submit" />
            <?php if ($action == 'edit'): ?>or <a href="<?php print Url::site('quote/delete/' . $quote->id); ?>" title="Delete this quote">delete</a><?php endif; ?>
        </div>
    </div>
    <br class="clear"/>
</div>
</form>

<br class="clear"/>

<div class="search-status">
Type your quote and watch this area for duplicate quotes
</div>
<div class="qresults">
    <div class="qresult" style="display: none;">
        <span class="id"></span> &mdash;
        <span class="text"></span> &mdash;
        <span class="author"></span>
    </div>
</div>