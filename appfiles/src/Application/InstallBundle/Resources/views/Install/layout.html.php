<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>DeskPRO</title>
	<link rel="stylesheet" type="text/css" href="../../static/stylesheets/install/install.css" />
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
