<?php if (isset($user)): ?>
<h1><?php print $user->username; ?></h1>
<ul>
    <li><a href="<?php echo Url::site('user/logout') ?>">Log out</a></li>
</ul>
<?php else: ?>
<h1>Log in</h1>
<?php
print Form::open(Url::site('user/login', 'https'), array('class'=>'login'));
if (isset($errors)) {
    print '<ul class="error">';
    foreach ($errors as $error) {
        print '<li>' . ucfirst($error) . '</li>';
    }
}
print '</ul>';
print '<label for="username"><span>Username:</span> ' . Form::input('username') . '</label>';
print '<label for="password"><span>Password:</span> ' . Form::password('password', '', array('maxlength' => 30)) . '</label>';

print Form::submit('login', 'Log in');

print Form::close();
?>
<?php endif; ?>