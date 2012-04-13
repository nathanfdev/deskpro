<?php if (!defined('DP_ROOT')) exit('No access'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>DeskPRO</title>
	<link rel="stylesheet" type="text/css" href="../../web/stylesheets/install/install.css" />
	<script type="text/javascript" src="../../web/vendor/jquery/jquery.min.js"></script>
	<style type="text/css">
		html, body {
			width: 100%;
			height: 100%;
			padding: 0;
			margin: 0;
			background-color: #ECEEF0;
		}

		body > table {
			width: 100%;
			height: 100%;
			margin: 0;
			padding: 0;
		}
		body > table > tbody > tr > td {
			vertical-align: middle;
			background: url(../../web/images/dp-logo-color.png) no-repeat 50% 100%;
			padding-bottom: 80px;
			border-top: 10px solid transparent;
			border-bottom: 10px solid transparent;
		}

		.dp-wrapper {
			background-color: #fff;
			border: 1px solid #D0D2D3;
			-webkit-border-radius: 6px;
			-moz-border-radius: 6px;
			border-radius: 6px;
			padding: 25px;
		}

		.page-header {
			margin-top: -15px;
			margin-left: -15px;
			margin-right: -15px;
			margin-bottom: 8px;
		}
	</style>
	<?php $view['slots']->output('head') ?>
</head>
<body>
<table cellspacing="0" cellpadding="0" width="100%" height="100%">
	<tbody>
		<tr>
			<td align="center" valign="middle" width="100%" height="100%">
				<div class="container">
				<div class="dp-wrapper">
					<div class="page-header">
						<h1>Installation<?php if ($view['slots']->has('subtitle')): ?>&nbsp;<small><?php $view['slots']->output('subtitle') ?></small><?php endif ?></h1>
					</div>
					<?php $view['slots']->output('_content') ?>
				</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>
</body>
</html>
