<div class="share">
<?php if ($valid): ?>
<h1>Thank you, <?php print $name ?></h1>
<div>You are now able to share tasks with <?php print $name_with; ?>. <a href="/">Go to main page</a></div>
<?php else: //if (isset($message)): ?>
<h1>Oops</h1>
<div>The code is invalid or your invitation was already processed.
Please <a href="http://craciunoiu.net/contact">contact us</a> if you have problems.
</div>
<?php endif; ?>
</div>
