<?php // vim: set ts=2 et sts=2 sw=2: ?>
<div class="reset">
  <h1><?php print $title; ?></h1>
  <?php if (isset($user)): ?>
    <h1><?php print $user->username; ?></h1>
    <ul>
      <li><a href="<?php echo Url::site('user/logout') ?>">Log out</a></li>
    </ul>
  <?php elseif (isset($code)):
    print Form::open(Url::site('user/reset'), array('class' => 'reset'));
    if (isset($errors)) {
      print '<ul class="error">';
      foreach ($errors as $error) {
        print '<li>' . ucfirst($error) . '</li>';
      }
      print '</ul>';
    }
    print '<label for="password"><span>Password:</span> ' .
            Form::password('password', '', array('id' => 'password')) . '</label>' .
          '<label for="password_confirm"><span>Repeat:</span> ' .
            Form::password('password_confirm', '', array('id' => 'password_confirm', 'maxlength' => 50)) .
          // persist code
          Form::hidden('code', $code) .
          Form::submit('reset', 'change and log in') .
          Form::close();
  elseif (isset($start)): ?>
    <div>Enter username. You'll receive a notification at your registered email address.</div>
    <?php
      print Form::open(Url::site('user/reset'), array('class' => 'intro'));
      if (isset($errors)): ?>
        <ul class="error">
        <?php foreach ($errors as $error): ?>
          <li><?php print ucfirst($error); ?></li>
        <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    <label for="username">
      <?php print Form::input('username', '', array('id' => 'username')); ?>
    </label>
    <?php
      print Form::submit('reset', 'get an email') .
            Form::close();
    ?>
  <?php else: //if (isset($message)): ?>
    <div><?php print $message; ?></div>
  <?php endif; ?>
</div>

