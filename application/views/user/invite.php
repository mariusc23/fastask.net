<div class="invite">
<?php if ($user->id && !$message): ?>
<?php
print Form::open(Url::site('user/invite', 'https'), array('class' => 'invite'));
print '<label for="email"><span>Email address:</span> '
    . Form::input('email', '', array('id' => 'email')) . '</label>';

print Form::submit('invite', 'send invitation');

print Form::close();
?>
<?php else: //if (isset($message)): ?>
    <h1><?php print $title; ?></h1>
    <div><?php print $message; ?></div>
<?php endif; ?>
</div>
