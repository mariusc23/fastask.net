<?php // vim: set ts=2 et sts=2 sw=2:
  $username = isset($_POST['username']) ? $_POST['username'] : '';
?>
<div class="login" style="display: none">
  <?php if (isset($user)): ?>
  <h1><?php print $user->username; ?></h1>
  <ul>
    <li><a href="<?php echo Url::site('user/logout') ?>">Log out</a></li>
  </ul>
  <?php else:
    print Form::open(Url::site('user/login'), array('class'=>'login'));
    if (isset($errors)) {
      print '<ul class="error">';
      foreach ($errors as $error) {
        print '<li>' . ucfirst($error) . '</li>';
      }
      print '</ul>';
    }
    print '<label for="username" id="user"><span>Username:</span> ' .
            Form::input('username', $username, array('id' => 'username')) .
            '</label><label for="password" id="pass"><span>Password:</span> ' .
            Form::password('password', '', array('id' => 'password',
                                                 'maxlength' => 50)) .
            '<br/><a href="/user/reset" id="forgot">Forgot password?</a></label>';
    ?>
    <a id="noaccount" href="/user/register/">Don't have an account?<br/>Sign up for one!</a>
    <?php
    if ($_REQUEST['r']) {
      print Form::hidden('r', $_REQUEST['r']);
    }
    print Form::submit('login', 'log in') .
          Form::close();
  endif; ?>
</div>

