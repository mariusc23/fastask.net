<div class="errors">
<h2>Not Found</h2>
<p>
We could not find the requested page.<br/>
<?php if ($user && $user->id): ?>
<a href="<?php print Url::site('/') ?>">Go to the main page</a>
<?php else: ?>
<a href="<?php print Url::site('user/register') ?>">Register</a>
<?php endif ?>
</p>
</div>