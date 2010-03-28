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
<?php if ($model == 'user' && $action == 'login'): ?>
<link type="text/css" href="<?php print Url::site('min/f=css/login.css'); ?>" rel="stylesheet" media="screen" />
<?php elseif ($model == 'user' && $action == 'register'): ?>
<link type="text/css" href="<?php print Url::site('min/f=css/register.css'); ?>" rel="stylesheet" media="screen" />
<?php else: ?>
<link type="text/css" href="<?php print Url::site('min/?g=css'); ?>" rel="stylesheet" media="screen" />
<?php endif; ?>
</head>
<body class="<?php print $model . '-' . $action; ?>">
<div id="content" role="main">
<div class="nojs">
    You must enable javascript to use Tasklist.
</div>
<?php print $content ?>
</div><!-- /#content -->

<!--<script type="text/javascript" src="<?php print Url::site('min/?g=js'); ?>"></script>-->
<script type="text/javascript" src="/js/jquery.min.js"></script>
<?php if ($model == 'tasklist' && $action == 'index'): ?>
<script type="text/javascript" src="/js/jquery.history.js"></script>
<script type="text/javascript" src="/js/jqModal.js"></script>
<script type="text/javascript" src="/js/debug.js"></script>
<script type="text/javascript" src="/js/hash.js"></script>
<script type="text/javascript" src="/js/workbox.js"></script>
<script type="text/javascript" src="/js/profile.js"></script>
<script type="text/javascript" src="/js/left.js"></script>
<script type="text/javascript" src="/js/main.js"></script>
<?php elseif ($model == 'user' && $action == 'login'): ?>
<script type="text/javascript" src="/js/login.js"></script>
<?php elseif ($model == 'user' && $action == 'register'): ?>
<script type="text/javascript" src="/js/register.js"></script>
<?php endif; ?>
</body>
</html>

