<div class="reset">
<h1><?php print $title; ?></h1>
<?php if (isset($user)): ?>
<h1><?php print $user->username; ?></h1>
<ul>
    <li><a href="<?php echo Url::site('user/logout') ?>">Log out</a></li>
</ul>
<?php elseif (isset($code)): ?>
<?php
print Form::open(Url::site('user/reset', 'https'), array('class' => 'reset'));
if (isset($errors)) {
    print '<ul class="error">';
    foreach ($errors as $error) {
        print '<li>' . ucfirst($error) . '</li>';
    }
}
print '</ul>';
print '<label for="password"><span>Password:</span> '
    . Form::password('password', '', array('id' => 'password')) . '</label>';
print '<label for="password_confirm"><span>Repeat:</span> '
    . Form::password('password_confirm', '', array('id' => 'password_confirm', 'maxlength' => 50));
// persist code
print Form::hidden('code', $code);
?>
<?php
print Form::submit('reset', 'change and log in');

print Form::close();
?>
<?php elseif (isset($start)): ?>
<div>Enter username. You'll receive a notification at your registered email address.</div>
<?php
print Form::open(Url::site('user/reset', 'https'), array('class' => 'intro'));
if (isset($errors)) {
    print '<ul class="error">';
    foreach ($errors as $error) {
        print '<li>' . ucfirst($error) . '</li>';
    }
}
print '</ul>';
print '<label for="username">'
    . Form::input('username', '', array('id' => 'username')) . '</label>';
?>
<?php
print Form::submit('reset', 'get an email');
print Form::close();
?>
<?php else: //if (isset($message)): ?>
    <div><?php print $message; ?></div>
<?php endif; ?>
</div>