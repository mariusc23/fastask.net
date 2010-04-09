<div class="share">
<?php if ($valid): ?>
    <?php if ($block): ?>
    <h1><?php print $name_with ?> blocked</h1>
    <div><?php print $name; ?>, you will not receive emails about <?php print $name_with; ?> again. <a href="/">Go to main page</a></div>
    <?php else: ?>
    <h1>Thank you, <?php print $name ?></h1>
    <div>You are now able to share tasks with <?php print $name_with; ?>. <a href="/">Go to main page</a></div>
    <?php endif; ?>
<?php else: ?>
<h1>Oops</h1>
<div>The code is invalid or your invitation was already processed.
Please <a href="http://craciunoiu.net/contact">contact us</a> if you have problems.
</div>
<?php endif; ?>
</div>
