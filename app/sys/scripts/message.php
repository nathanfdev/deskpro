<?php if (!defined('DP_ROOT')) exit('No access'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>DeskPRO</title>
	<link rel="stylesheet" type="text/css" href="./web/stylesheets/install/install.css" />
</head>
<body>

<?php if (isset($_GET['ie8_admin']) || isset($_GET['ie8_reports'])): ?>
	<div style="margin: 100px;">
		<h2>IE8 is not supported</h2>
		<p>
			Unfortunately, DeskPRO does not support IE8 in the admin or reports interfaces.
		</p>
		<p>
			To continue, please download an alternative browser such as:
		</p>
		<ul>
			<li><a href="http://www.getfirefox.com/">Mozilla Firefox</a></li>
			<li><a href="http://www.google.com/chrome/">Google Chrome</a></li>
			<li><a href="http://microsoft.com/ie">Upgrade to a new version of IE</a></li>
		</ul>
	</div>
<?php endif ?>

</body>
</html>
