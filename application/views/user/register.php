
<div class="register" style="display: none">
<h1>Register</h1>

<?php print Form::open('user/register', array('class' => 'register')); ?>
<?php if (isset($errors)): ?>
    <ul>
    <?php foreach ($errors as $error): ?>
        <li><?php print ucfirst($error); ?></li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>
<label for="username"><span>Username:</span> <?php
    print Form::input('username', '', array('maxlength' => 50, 'id' => "username"));
?></label>
<label for="email"><span>Email:</span> <?php
    print Form::input('email', '', array('maxlength' => 255, 'id' => "email"));
?></label>
<label for="password"><span>Password:</span> <?php
    print Form::password('password', '', array('maxlength' => 50, 'id' => "password"));
?></label>
<label for="password_confirm"><span>Password again:</span> <?php
    print Form::password('password_confirm', '', array('maxlength' => 50, 'id' => "password_confirm"));
?></label>

<?php
    print Form::submit('register', 'Register');
    print Form::close();
?>
</div>