<?php
$username = isset($_POST['username']) ? $_POST['username']
            : '';
$email = isset($_POST['email']) ? $_POST['email']
            : '';
?>
<div class="register" style="display: none">
<h1>Register</h1>

<?php print Form::open('user/register', array('class' => 'register')); ?>
    <ul>
        <li>We're still in private beta. Registration is invitation-only.</li>
<?php if (isset($errors)): ?>
    <?php foreach ($errors as $error): ?>
        <li><?php print ucfirst($error); ?></li>
    <?php endforeach; ?>
<?php endif; ?>
    </ul>
<label for="username"><span class="label">Username:</span> <?php
    print Form::input('username', $username, array('maxlength' => 50, 'id' => "username"));
?>
    <span class="icon"></span><span class="info">Letters &amp; numbers</span>
</label>
<label for="email"><span class="label">Email:</span> <?php
    print Form::input('email', $email, array('maxlength' => 255, 'id' => "email"));
?>
    <span class="icon"></span><span class="info">For reminders</span>
</label>
<label for="password"><span class="label">Password:</span> <?php
    print Form::password('password', '', array('maxlength' => 50, 'id' => "password"));
?>
    <span class="icon"></span><span class="info">Case sensitive *</span>
</label>
<label class="strength"><span class="label">Strength:</span>
<span class="indicator">
    <span class="s s-1"></span>
    <span class="s s-2"></span>
    <span class="s s-3"></span>
    <span class="s s-4"></span>
    <span class="s s-5"></span>
</span>
    <span class="info">Very Weak</span>
</label>
<label for="password_confirm"><span class="label">Password again:</span> <?php
    print Form::password('password_confirm', '', array('maxlength' => 50, 'id' => "password_confirm"));
?>
    <span class="icon"></span><span class="info">Don't copy &amp; paste</span>
</label>

<?php
    // TODO: remove this
    if (isset($_REQUEST['a06d2d1f8c394e3421286a81254d6ad6bf9c4ead'])) {
        print Form::hidden('a06d2d1f8c394e3421286a81254d6ad6bf9c4ead', $_REQUEST['a06d2d1f8c394e3421286a81254d6ad6bf9c4ead']);
    }
    print Form::submit('register', 'sign up');
    print Form::close();
?>

<a id="account" href="/user/login/">Already have an account? Log in!<br/>
    * Password must be at least 6 characters.</a>

</div>