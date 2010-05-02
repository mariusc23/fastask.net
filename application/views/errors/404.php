<?php // vim: set ts=2 et sts=2 sw=2: ?>
<div class="errors">
  <h2>Not Found</h2>
  <p>
    We could not find the requested page.<br/>
    <?php if ($user && $user->id): ?>
    <a href="<?php print Url::site('in') ?>">Go to the main page</a>
    <?php else: ?>
    <a href="<?php print Url::site('/') ?>">Go to the main page</a>
    <?php endif ?>
  </p>
</div>
