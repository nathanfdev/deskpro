<?php

function deskpro_install_check_version()
{
	return version_compare(phpversion(), '5.3.2', '>=');
}

function deskpro_install_check_pcre()
{
	$backtrack_limit = (int)(@ini_get('pcre.backtrack_limit'));

	if ($backtrack_limit < 100000) {
		return false;
	}

	return true;
}

function deskpro_install_check_safemode()
{
	$v = ini_get('safe_mode');
	if (!$v || $v != 'Off' || $v != 'false') {
		return true;
	}

	return false;// samemode on, fails test
}

function deskpro_install_check_config()
{
	if (!is_file(DP_CONFIG_FILE)) {
		return false;
	}

	return true;
}

function deskpro_install_check_mbstring()
{
	return function_exists('mb_stripos');
}

function deskpro_install_check_pdo()
{
	return class_exists('PDO', false);
}

function deskpro_install_check_pdo_mysql()
{
	return (deskpro_install_check_pdo() && in_array('mysql', PDO::getAvailableDrivers()));
}

function deskpro_install_check_image_manip()
{
	if (class_exists('Imagick', false) || class_exists('Gmagick', false) || function_exists('gd_info')) {
		return true;
	}
	return false;
}

function deskpro_install_check_memory_limit()
{
	$mem_size = @ini_get('memory_limit');
	if ($mem_size && $mem_size != '-1' && deskpro_install_check_parseinisize($mem_size) < 134217728/* 128 MB */) {
		return false;
	}

	return true;
}

function deskpro_install_check_writable()
{
	if (!is_writable(DP_ROOT.'/sys/cache')) {
		return false;
	}

	return true;
}

function deskpro_install_basic_error($message, $title = 'DeskPRO Installation')
{
	// We dont know the root path yet, so lets just inline the CSS
	$css = file_get_contents(DP_WEB_ROOT.'/web/stylesheets/install/install.css');
	$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>DeskPRO</title>
	<style type="text/css">
	$css
	</style>
</head>
<body>
<div class="container">
	<div class="page-header">
		<h1>{$title}</h1>
	</div>
	<div class="alert-message block-message error">
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


/**
 * This is a copy of Orb\Util\Numbers::parseIniSize() because that class isn't included at the time preboot is called
 */
function deskpro_install_check_parseinisize($val)
{
	$val = trim($val);
	$last = strtoupper($val[strlen($val)-1]);

	// Already in bytes
	if (ctype_digit($last)) {
		return (int)$val;
	}

	$val = (int)$val;

	if ($last != 'G' && $last != 'M' && $last != 'K') {
		return 0;
	}

	switch($last) {
		case 'G':
			$val *= 1024;
		case 'M':
			$val *= 1024;
		case 'K':
			$val *= 1024;
	}

	return $val;
}

/**
 * @return string
 */
function deskpro_install_guess_phpini_path()
{
	ob_start();
	phpinfo();
	$phpinfo = ob_get_clean();
	$phpinfo = html_entity_decode(strip_tags($phpinfo), ENT_QUOTES);

	if (preg_match('#^Loaded Configuration File (.*?)$#m', $phpinfo, $m)) {
		$path = $m[1];
		$path = str_replace('=>', '', $path);
		$path = trim($path);
		return $path;
	} else {
		return false;
	}
}