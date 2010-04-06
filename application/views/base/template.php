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
<link type="image/x-icon" href="<?php print Url::site('favicon.ico') ?>" rel="shortcut icon">
<?php if ($model == 'user' && $action == 'reset'): ?>
<link type="text/css" href="<?php print Url::site('min/f=css/login.css'); ?>" rel="stylesheet" media="screen" />
<?php elseif ($model == 'user' && $action == 'login'): ?>
<link type="text/css" href="<?php print Url::site('min/f=css/login.css'); ?>" rel="stylesheet" media="screen" />
<?php elseif ($model == 'user' && $action == 'register'): ?>
<link type="text/css" href="<?php print Url::site('min/f=css/register.css'); ?>" rel="stylesheet" media="screen" />
<?php elseif ($model == 'errors'): ?>
<link type="text/css" href="<?php print Url::site('min/f=css/errors.css'); ?>" rel="stylesheet" media="screen" />
<?php else: ?>
<link type="text/css" href="<?php print Url::site('min/?g=css'); ?>" rel="stylesheet" media="screen" />
<?php endif; ?>
</head>
<body class="<?php print $model . '-' . $action; ?>">
<div id="content" role="main">
<?php if (!isset($okjs)): ?>
<div class="nojs">
    <script type="text/javascript">
        document.write("Loading...");
    </script>
    <noscript>You must enable javascript to use Tasklist.</noscript>
</div>
<?php endif; ?>
<?php print $content ?>
</div><!-- /#content -->

<?php if ($model == 'tasklist' && $action == 'index'): ?>
<script type="text/javascript" src="<?php print Url::site('min/?g=js'); ?>"></script>
<!--
<script type="text/javascript" src="/js/jquery.min.js"></script>
<script type="text/javascript" src="/js/jquery.history.js"></script>
<script type="text/javascript" src="/js/jqModal.js"></script>
<script type="text/javascript" src="/js/jquery.autocomplete.pack.js"></script>
<script type="text/javascript" src="/js/constants.js"></script>
<script type="text/javascript" src="/js/url.js"></script>
<script type="text/javascript" src="/js/notification.js"></script>
<script type="text/javascript" src="/js/row.js"></script>
<script type="text/javascript" src="/js/list.js"></script>
<script type="text/javascript" src="/js/workbox.js"></script>
<script type="text/javascript" src="/js/profile.js"></script>
<script type="text/javascript" src="/js/main.js"></script>
-->
<?php //elseif ($model == 'errors'): ?>
<?php elseif (($model == 'user' && $action == 'login') ||
     ($model == 'user' && $action == 'register')): ?>
<script type="text/javascript" src="<?php print URL::site('min/?g=lr'); ?>"></script>
<?php endif; ?>
</body>
</html>

