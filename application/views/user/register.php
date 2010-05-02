<?php // vim: set ts=2 et sts=2 sw=2:
  $username = isset($_POST['username']) ? $_POST['username'] : '';
  $email = isset($_POST['email']) ? $_POST['email'] : '';

  if (!$email) {
    $email = $invite_email;
  }
?>
<div class="register" style="display: none">
  <h1>Register</h1>
  <?php print Form::open('user/register', array('class' => 'register')); ?>
  <?php if ($errors || !$invited): ?>
    <ul>
      <li>we're still in private beta. <a href="http://craciunoiu.net/contact">ask for an invitation</a></li>
      <?php if ($errors): ?>
        <?php foreach ($errors as $error): ?>
          <li><?php print ucfirst($error); ?></li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>
  <?php endif; ?>

  <label for="username"><span class="label">Username:</span> <?php
    print Form::input('username', $username, array('maxlength' => 50, 'id' => "username")); ?>
    <span class="info-icon">
      <span class="icon"></span>
      <span class="info">Letters only</span>
    </span>
  </label>
  <label for="email"><span class="label">Email:</span> <?php
    print Form::input('email', $email, array('maxlength' => 255, 'id' => "email")); ?>
    <span class="info-icon">
      <span class="icon"></span>
      <span class="info">For reminders</span>
    </span>
  </label>
  <label for="password"><span class="label">Password:</span> <?php
    print Form::password('password', '', array('maxlength' => 50, 'id' => "password")); ?>
    <span class="info-icon">
      <span class="icon"></span>
      <span class="info">Case sensitive *</span>
    </span>
  </label>
  <label class="strength"><span class="label">Strength:</span>
    <span class="indicator">
        <span class="s s-1"></span>
        <span class="s s-2"></span>
        <span class="s s-3"></span>
        <span class="s s-4"></span>
        <span class="s s-5"></span>
    </span>
    <span class="info-icon">
        <span class="info">Very Weak</span>
    </span>
  </label>
  <label for="password_confirm"><span class="label">Password again:</span> <?php
    print Form::password('password_confirm', '', array('maxlength' => 50, 'id' => "password_confirm")); ?>
    <span class="info-icon">
      <span class="icon"></span><span class="info">Don't copy &amp; paste</span>
    </span>
  </label>

  <?php
    if ($invited && $_REQUEST['code']) {
      print Form::hidden('code', $_REQUEST['code']);
    }
    print Form::submit('register', 'sign up') .
          Form::close();
  ?>

  <a id="account" href="/user/login/">Already have an account? Log in!<br/>
    * Password must be at least 5 characters.</a>
</div>
