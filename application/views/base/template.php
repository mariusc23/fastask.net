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
<link type="text/css" href="<?php print Url::site('min/?g=css'); ?>" rel="stylesheet" media="screen" />
</head>
<body class="<?php print $model . '-' . $action; ?>">
<div id="content" role="main">
<div class="nojs">
    You must enable javascript to use Tasklist.
</div>
<?php print $content ?>
</div><!-- /#content -->

<div id="footer" role="contentinfo">
<div id="footer-inner">
&copy; 2009 - <?php print date('Y'); ?> <a href="/"><?php print SITE_NAME; ?></a>. <a href="http://craciunoiu.net">Contact us for feedback or any concerns.</a>
</div><!-- /#footer-inner -->
</div><!-- /#footer -->

<!--<script type="text/javascript" src="<?php print Url::site('min/?g=js'); ?>"></script>-->
<script type="text/javascript" src="/js/jquery.min.js"></script>
<script type="text/javascript" src="/js/jquery.history.js"></script>
<script type="text/javascript" src="/js/jqModal.js"></script>
<script type="text/javascript" src="/js/main.js"></script>

</body>
</html>

