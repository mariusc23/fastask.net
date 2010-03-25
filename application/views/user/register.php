<h1>Register</h1>

<p>Registration is disabled at the moment. Please contact us at <a href="http://craciunoiu.net">our website</a> if you are interested in contributing.</p>
<?php
return ;

print Form::open('user/register', array('class'=>'login'));
if (isset($errors)) {
    print '<ul>';
    foreach ($errors as $error) {
        print '<li>' . ucfirst($error) . '</li>';
    }
}
print '</ul>';
print '<label for="username"><span>Username:</span> ' . Form::input('username') . '</label>';
print '<label for="email"><span>Email:</span> ' . Form::input('email') . '</label>';
print '<label for="password"><span>Password:</span> ' . Form::password('password', '', array('maxlength' => 30)) . '</label>';
print '<label for="password_confirm"><span>Confirm password:</span> ' . Form::password('password_confirm', '', array('maxlength' => 30)) . '</label>';

print Form::submit('login', 'Log in');

print Form::close();
