<?php if (!defined('DP_ROOT')) exit('No access'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>DeskPRO</title>
	<link rel="stylesheet" type="text/css" href="../../web/stylesheets/install/install.css" />
	<script type="text/javascript" src="../../web/vendor/jquery/jquery.min.js"></script>
	<?php $view['slots']->output('head') ?>
</head>
<body>
<div class="container">
	<div class="page-header">
		<h1>DeskPRO Installation<?php if ($view['slots']->has('subtitle')): ?>&nbsp;<small><?php $view['slots']->output('subtitle') ?></small><?php endif ?></h1>
	</div>
	<?php $view['slots']->output('_content') ?>
</div>
</body>
</html>
