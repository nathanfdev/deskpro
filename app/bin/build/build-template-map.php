#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
	echo "This script must only be run from the CLI.\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(1);
}

define('DP_BUILDING', true);
define('DP_ROOT', realpath(__DIR__ . '/../../'));
define('DP_WEB_ROOT', realpath(__DIR__ . '/../../../'));
define('DP_CONFIG_FILE', DP_WEB_ROOT . '/config.php');

require DP_ROOT . '/bin/build/inc.php';
require DP_ROOT.'/sys/system.php';

$paths = array(
	'AdminBundle'   => DP_ROOT.'/src/Application/AdminBundle/Resources/views',
	'AgentBundle'   => DP_ROOT.'/src/Application/AgentBundle/Resources/views',
	'DeskPRO'       => DP_ROOT.'/src/Application/DeskPRO/Resources/views',
	'ReportBundle'  => DP_ROOT.'/src/Application/ReportBundle/Resources/views',
	'UserBundle'    => DP_ROOT.'/src/Application/UserBundle/Resources/views',
);

$tpl_info = array();

$bogus = false;
if (in_array('--bogus', $_SERVER['argv'])) {
	$bogus = true;
}

foreach ($paths as $bundle => $dir) {
	$finder = new \Symfony\Component\Finder\Finder();
	$finder->files()->name('*.twig')->in($dir);

	foreach ($finder as $file) {
		/** @var \Symfony\Component\Finder\SplFileinfo $file */

		$filepath = $file->getRealPath();

		$tplname = str_replace($dir . '/', ':', $filepath);
		$tplname = str_replace('/', ':', $tplname);
		if (substr_count($tplname, ':') < 2) {
			$tplname = ':' . $tplname; // for layouts that are in top dir, MyBundle::layout
		}
		$tplname = $bundle . $tplname;

		if (!$bogus) {
			exec("git log --date=short -s -1 -- {$filepath}", $out);
			$res = implode("\n", $out);

			preg_match('#^Date:\s*([0-9]{4}\-[0-9]{2}\-[0-9]{2})#m', $res, $m);
			$time = strtotime($m[1]);
		} else {
			$time = time();
		}


		$tpl_info[$tplname] = array(
			'path' => str_replace(DP_ROOT, '', $file->getRealPath()),
			'last_updated' => $time,
		);

		echo ".";
	}
}

$php = array("<?php return array(\n");

foreach ($tpl_info as $k => $info) {
	$php[] = "'$k' => array('path' => DP_ROOT.'{$info['path']}', 'last_updated' => {$info['last_updated']}),\n";
}

$php[] = ");";
$php[] = "\n";

file_put_contents(DP_ROOT.'/sys/config/template-map.php', implode('', $php));

echo "\n";