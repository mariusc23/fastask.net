<?php
if (isset($title)) {
    $title = $title . SITE_SEPARATOR . SITE_NAME;
} else {
    $title = SITE_NAME;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title><?php print $title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php if ($model == 'user' && $action == 'reset'): ?>
<link type="text/css" href="<?php print Url::site('min/f=css/login.css'); ?>" rel="stylesheet" media="screen" />
<?php elseif ($model == 'user' && $action == 'login'): ?>
<link type="text/css" href="<?php print Url::site('min/f=css/login.css'); ?>" rel="stylesheet" media="screen" />
<?php elseif ($model == 'user' && $action == 'register'): ?>
<link type="text/css" href="<?php print Url::site('min/f=css/register.css'); ?>" rel="stylesheet" media="screen" />
<?php else: ?>
<link type="text/css" href="<?php print Url::site('min/?g=css'); ?>" rel="stylesheet" media="screen" />
<?php endif; ?>
</head>
<body class="<?php print $model . '-' . $action; ?>">
<div id="content" role="main">
<?php if (!isset($okjs)): ?>
<div class="nojs">
    You must enable javascript to use Tasklist.
</div>
<?php endif; ?>
<?php print $content ?>
</div><!-- /#content -->

<?php if ($model == 'tasklist' && $action == 'index'): ?>
<script type="text/javascript" src="<?php print Url::site('min/?g=js'); ?>"></script>
<?php elseif (($model == 'user' && $action == 'login') ||
     ($model == 'user' && $action == 'register')): ?>
<script type="text/javascript" src="<?php print URL::site('min/?g=lr'); ?>"></script>
<?php endif; ?>
</body>
</html>

