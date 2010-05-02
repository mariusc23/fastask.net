<?php // vim: set ts=2 et sts=2 sw=2: ?>
<div class="invite">
  <?php if ($user->id && !$message):
  print Form::open(Url::site('user/invite'), array('class' => 'invite')) .

        '<label for="email"><span>Email address:</span> ' .
          Form::input('email', '', array('id' => 'email')) . '</label>' .
        Form::submit('invite', 'send invitation') .

        Form::close();

  else: ?>
    <h1><?php print $title; ?></h1>
    <div><?php print $message; ?></div>
  <?php endif; ?>
</div>
