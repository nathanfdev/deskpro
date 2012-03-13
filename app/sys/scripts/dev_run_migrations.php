<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\Migrations;

if (!defined('DP_ROOT')) exit('No access');

/**
 * Runs migrations
 */
class RunMigrations
{
	public function run()
	{
		global $DP_CONFIG;
		require DP_CONFIG_FILE;

		if (!isset($DP_CONFIG) || !is_array($DP_CONFIG)) {
			$DP_CONFIG = array();
		}

		if (!isset($DP_CONFIG['db'])) $DP_CONFIG['db'] = array();
		if (!isset($DP_CONFIG['db']['host']))      $DP_CONFIG['db']['host']      = DP_DATABASE_HOST;
		if (!isset($DP_CONFIG['db']['user']))      $DP_CONFIG['db']['user']      = DP_DATABASE_USER;
		if (!isset($DP_CONFIG['db']['password']))  $DP_CONFIG['db']['password']  = DP_DATABASE_PASSWORD;
		if (!isset($DP_CONFIG['db']['dbname']))    $DP_CONFIG['db']['dbname']    = DP_DATABASE_NAME;

		$env = 'dev';
		$debug = true;

		require DP_ROOT . '/sys/KernelBooter.php';
		$app = \DeskPRO\Kernel\KernelBooter::getCliApp($env, $debug);

		$argv = $_SERVER['argv'];
		array_shift($argv); // remove cron.php
		array_unshift($argv, 'cmd.php', 'dpdev:do-migration', '--no-interaction');
		$input = new \Symfony\Component\Console\Input\ArgvInput($argv);

		$output = new \Symfony\Component\Console\Output\StreamOutput(fopen('php://output', 'w'), \Symfony\Component\Console\Output\StreamOutput::VERBOSITY_VERBOSE, null, $formatter = null);

		header('Content-Type: text/plain');
		header('Content-Disposition: inline; filename=migration.txt');

		$app->run($input, $output);
	}
}

$file_loader = new RunMigrations();
$file_loader->run();
