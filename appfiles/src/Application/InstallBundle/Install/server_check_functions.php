<?php

function deskpro_install_check_version()
{
	return version_compare(phpversion(), '5.3.2', '>=');
}

function deskpro_install_check_config()
{
	if (!is_file(DP_ROOT.'/config.php')) {
		return false;
	}

	return true;
}

function deskpro_install_check_pdo()
{
	return class_exists('PDO', false);
}

function deskpro_install_check_pdo_mysql()
{
	return in_array('mysql', PDO::getAvailableDrivers());
}

function deskpro_install_check_writable()
{
	if (!is_writable(DP_ROOT.'/sys/cache') || !is_writable(DP_ROOT.'/sys/logs')) {
		return false;
	}

	return true;
}

function deskpro_install_basic_error($message)
{
	$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>DeskPRO</title>
	<link rel="stylesheet" type="text/css" href="../static/stylesheets/install/install.css" />
</head>
<body>
<div class="container">
	<div class="page-header">
		<h1>DeskPRO Installation</h1>
	</div>
	<div class="alert-message error">
		{$message}
	</div>

	<div class="alert-message block-message warning">
		<p>
			<strong>Need help?</strong> If you're unsure how to resolve this error,
			just ask one of our support agents and they'll know exactly what to do.
		</p>
		<div class="alert-actions">
			<a class="btn" href="mailto:support@deskpro.com">Email support@deskpro.com</a>
			<a class="btn" href="http://support.deskpro.com/">Visit our helpdesk</a>
		</div>
	</div>
</div>
</body>
</html>
HTML;

	return $html;
}
