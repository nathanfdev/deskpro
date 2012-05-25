<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Tools
 */

namespace DeskPRO\Tools;

########################################################################################################################
# Boot up
########################################################################################################################

if (php_sapi_name() != 'cli') {
	echo "This script must only be run from the CLI.\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(1);
}

define('DP_START_DIR', getcwd());

if (!defined('DP_ROOT')) {
	define('DP_ROOT', realpath(dirname(__FILE__) . '/../'));
}

if (!defined('DP_WEB_ROOT')) {
	define('DP_WEB_ROOT', realpath(dirname(__FILE__) . '/../../'));
}

define('DP_CONFIG_FILE', DP_WEB_ROOT.'/config.php');

@ini_set('memory_limit', -1);
@ini_set('memory_limit', 268435456);
@set_time_limit(0);

require DP_ROOT . '/src/Application/InstallBundle/Install/server_check_functions.php';
require DP_ROOT . '/sys/load_config.php';

require DP_ROOT.'/vendor/symfony/src/Symfony/Component/HttpKernel/Util/Filesystem.php';
require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Process/ExecutableFinder.php';
require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Finder/Finder.php';
require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Finder/Glob.php';
require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Finder/SplFileInfo.php';
require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Finder/Iterator/RecursiveDirectoryIterator.php';
require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Finder/Iterator/ExcludeDirectoryFilterIterator.php';
require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Finder/Iterator/FileTypeFilterIterator.php';
require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Finder/Iterator/FilenameFilterIterator.php';
require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Console/Output/OutputInterface.php';
require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Console/Formatter/OutputFormatterInterface.php';
require DP_ROOT.'/src/Orb/Util/Numbers.php';
require DP_ROOT.'/src/Orb/Util/Env.php';

dp_load_config();

if (!defined('DP_MA_SERVER')) {
	define('DP_MA_SERVER', 'http://www.deskpro.com/members');
}

########################################################################################################################
# Basic requirement checks
########################################################################################################################

$errors = array();

if (!deskpro_install_check_version()) {
	$errors[] = "The version of PHP you have is too old. DeskPRO requires PHP v5.3.2 or newer. You need to upgrade your version.";
}

if (!deskpro_install_check_pcre()) {
	$errors[] = "PHP is configured with a `pcre.backtrack_limit` value that is too low. Edit your php.ini configuration and change it to at least 100000.";
}

if (!deskpro_install_check_safemode()) {
	$errors[] = "PHP currently has <code>safe_mode</code> enabled. DeskPRO requires safe_mode to be set to \"Off\". You need to edit your PHP configuration to make this change.";
}

if (deskpro_install_check_pdo()) {
	if (deskpro_install_check_pdo_mysql()) {
		// ok
	} else {
		$errors[] = "PDO (http://php.net/manual/en/book.pdo.php) is installed, but the MySQL driver is not. You need to install pdo_mysql into your php.ini file.";
	}
} else {
	$errors[] = "PDO (http://php.net/manual/en/book.pdo.php) is not installed. You need to install PDO into your php.ini file.";
}

if ($errors) {
	echo "There are problems with your server or PHP configuration that prevents this tool from running:\n";
	foreach ($errors as $e) {
		echo "- " . $e;
		echo "\n";
	}
	echo "\n";
	echo "We have automatically detected the path to your php.ini file at:\n";
	echo \Orb\Util\Env::getPhpIniPath();
	echo "\n\n";
	echo "If you require assistance, email support@deskpro.com\n\n";
	exit(1);
}
unset($errors);

########################################################################################################################
# Upgrade class
########################################################################################################################

class Upgrade
{
	/**
	 * @var array
	 */
	protected $argv;

	/**
	 * @var array
	 * @see getLatestVersion()
	 */
	protected $latest_version = null;

	/**
	 * @var resource
	 * @see log
	 */
	protected $log_fh;

	/**
	 * @var string
	 */
	protected $file_backup;

	/**
	 * @var string
	 */
	protected $db_backup;

	/**
	 * 'files' to revert files
	 * 'db' to revert files and db
	 * @var string
	 */
	protected $revert_checkpoint;

	/**
	 * @var ZipStrategy
	 */
	protected $zip;

	public function run(array $argv)
	{
		$this->argv = $argv;

		if (in_array('--auto', $argv)) {
			register_shutdown_function('DeskPRO\\Tools\\Upgrade_Shutdown_Function');
			$this->runAction_auto();
		}

		$this->checkEnv();
		register_shutdown_function('DeskPRO\\Tools\\Upgrade_Shutdown_Function');

		if (in_array('--help', $argv)) {
			$this->runAction_help();
		} elseif (in_array('--check-version', $argv)) {
			$this->runAction_checkVersion();
		} elseif (in_array('--download-latest', $argv)) {
			$this->runAction_downloadLatest();
		} elseif (in_array('--backup-db', $argv)) {
			$this->runAction_backupDatabase();
		} elseif (in_array('--restore-db', $argv)) {
			$this->runAction_restoreDatabase();
		} elseif (in_array('--backup-files', $argv)) {
			$this->runAction_backupFiles();
		} elseif (in_array('--restore-files', $argv)) {
			$this->runAction_restoreFiles();
		} elseif (in_array('--install-latest-files', $argv)) {
			$this->runAction_installLatestFiles();
		} elseif (in_array('--run-db-upgrade', $argv)) {
			$this->runAction_dbUpgrade();
		} else {
			$this->runAction_interactive();
		}
	}

	protected function checkEnv()
	{
		try {
			if (!is_dir($this->getBackupDir()) || !is_writable($this->getBackupDir())) {
				$this->outAndLog("Backup directory does not exist or is not writable: " . $this->getBackupDir());
				exit(1);
			}

			if (!is_dir($this->getLogDir()) || !is_writable($this->getLogDir())) {
				$this->outAndLog("Log  directory does not exist or is not writable: " . $this->not());
				exit(1);
			}

			if (!is_dir($this->getTmpDir()) || !is_writable($this->getTmpDir())) {
				$this->outAndLog("Tmp directory does not exist or is not writable: " . $this->getTmpDir());
				exit(1);
			}
		} catch (\Exception $e) {} // to catch error about log

		try {
			$this->zip = new ZipStrategy($this);
		} catch (\Exception $e) {
			$this->outAndLog("To use this tool, the zlib or Zip PHP extensions must be enabled.");
			exit(1);
		}

		try {
			global $DP_CONFIG;

			// Empty the db first
			$pdo = new \PDO("mysql:host={$DP_CONFIG['db']['host']};dbname={$DP_CONFIG['db']['dbname']}", $DP_CONFIG['db']['user'], $DP_CONFIG['db']['password']);
		} catch (\Exception $e) {
			$this->outAndLog("There was a problem connecting to the database: " . $e->getMessage());
			exit(1);
		}

		try {
			$tables = $pdo->query("SHOW TABLES")->fetchColumn(0);
			if (!$tables) {
				$this->outAndLog("Your database appears to be empty. Did you mean to run the import.php command?");
				exit(1);
			}
		} catch (\Exception $e) { }
	}


	/**
	 * @param $string
	 */
	public function out($string, $nl = true)
	{
		echo $string;
		if ($nl) {
			echo "\n";
		}
	}


	/**
	 * @param $string
	 */
	public function outAndLog($string, $nl = true)
	{
		$this->out($string, $nl);
		$this->log($string);
	}


	/**
	 * Log a message
	 *
	 * @param $string
	 * @throws \Exception
	 */
	public function log($string)
	{
		if (!$this->log_fh) {
			$this->log_fh = fopen($this->getLogDir() . '/upgrade.log', 'a');
			if (!$this->log_fh) {
				throw new \Exception("Could not open log file: " . $this->getLogDir() . '/upgrade.log');
			}

			$this->registerCleanupParam('close_log_fh', $this->log_fh);

			$this->log("(Command: " . implode(' ', $this->argv) . ")");
		}

		$string = trim($string);
		fwrite($this->log_fh, sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $string));
	}


	/**
	 * Log an exception
	 *
	 * @param \Exception $e
	 */
	public function logException(\Exception $e)
	{
		$this->log("-> {$e->getCode()} {$e->getMessage()}");

		$lines = $e->getTraceAsString();
		$lines = str_replace(DP_ROOT, '', $lines);
		$lines = explode("\n", $lines);

		foreach ($lines as $l) {
			$this->log("-> $l");
		}
	}


	####################################################################################################################
	# help
	####################################################################################################################

	public function runAction_help()
	{
		$this->out("Usage: php upgrade-util.php <action>");
		$this->out('');
		$this->out("Possible actions:");

		$this->out("\t--auto [--quiet] [--error-halt]");
		$this->out("\t\tAutomatically checks for a newer version, and if one exists, will attempt to ");
		$this->out("\t\tdownload it, extract it and install it. Backups will be made to the backups directory.");
		$this->out('');
		$this->out("\t\t--quiet suppresses output. Ideal for automation. The log file will contain any");
		$this->out("\t\trelevant information.");
		$this->out('');
		$this->out("\t\t--error-halt will halt on errors instead of trying to restore the files/database");
		$this->out("\t\twhen somethign bad happens.");
		$this->out('');

		$this->out("\t--check-version");
		$this->out("\t\tOutputs information about your current version and the latest version of DeskPRO available");
		$this->out('');

		$this->out("\t--run-db-upgrade");
		$this->out("\t\tIf the database is out of date with the files on the filesystem, then any required database");
		$this->out("\t\tupdates will be executed.");
		$this->out('');

		$this->out("\t--backup-db");
		$this->out("\t\tExecutes a mysqldump of your database into the data/backups directory");
		$this->out('');

		$this->out("\t--restore-db --path <zip-file>");
		$this->out("\t\tRestores the database from a backup. zip-file should be a full path, or the filename");
		$this->out("\t\tof a backup in the data/backups directory.");
		$this->out('');

		$this->out("\t--backup-files");
		$this->out("\t\tBacks up all DeskPRO files. Note: This will NOT back up the data/backups directory.");
		$this->out('');

		$this->out("\t--restore-files --path <zip-file>");
		$this->out("\t\tRestores files from a backup file. zip-file should be a full path, or the filename");
		$this->out("\t\tof a backup in the data/backups directory.");
		$this->out('');

		$this->out("\t--download-latest [--path <path>]");
		$this->out("\t\tDownloads the latest version of DeskPRO and saves it into the data/backups directory,");
		$this->out("\t\tunless you specify a path with --path.");
		$this->out('');

		$this->out("\t--install-latest-files [--path <zip-path>] --dry-run");
		$this->out("\t\tExtracts a ZIP and replaces current files with the ones from the ZIP. This does NOT upgrade");
		$this->out("\t\tthe database scheme. You still need to run upgrade.php after this to update the database.");
		$this->out('');
		$this->out("\t\tIf --path is supplied, the ZIP from --path will be used as the source. Otherwise, the latest");
		$this->out("\t\tsource is downloaded (same as running --download-latest).");
		$this->out('');
		$this->out("\t\tIf --dry-run is specified, no actual files will be overwritten or created. Your console will");
		$this->out("\t\tfill up with a log of files that will be copied.");
		$this->out('');
		$this->out("\t\tIt is recommended to run --backup-files before running this.");
		$this->out('');
		$this->out("\t\tNote that files are copied and overwritten, but old files remain. Any custom files you have");
		$this->out("\t\tuploaded will not be removed.");
		$this->out('');
	}

	####################################################################################################################
	# interactive
	####################################################################################################################

	public function runAction_interactive()
	{
		$interactive = new UpgradeInteractive($this);
	}


	####################################################################################################################
	# auto
	####################################################################################################################

	public function runAction_auto()
	{
		$time_start = microtime(true);

		$is_quiet        = in_array('--quiet', $this->argv);
		$is_error_halt = true;
		$is_status_write = in_array('--write-status-file', $this->argv);

		$skip_file_backup = in_array('--skip-backup-file', $this->argv);
		$skip_db_backup   = in_array('--skip-backup-db', $this->argv);

		if ($is_status_write) {

			if (file_exists(DP_WEB_ROOT . '/auto-update-status.txt') && !unlink(DP_WEB_ROOT . '/auto-update-status.txt')) {
				$this->outAndLog("Could not delete previous auto-update-status.log file");
				exit(1);
			}

			$that = $this;
			$write_status = function($code, $message = '') use ($that) {
				$fp = fopen(DP_WEB_ROOT . '/auto-update-status.txt', 'a');
				$time = microtime(true);

				if (is_array($message)) {
					$message = json_encode($message);
				}

				fwrite($fp, "STATUS(" . $code . ")@$time#$message\n");
				fclose($fp);
			};

			if (!($fp = fopen(DP_WEB_ROOT . '/auto-update-status.txt', 'w'))) {
				$this->outAndLog("Could not write update status file");
				exit(1);
			}

			fclose($fp);

		} else {
			$write_status = function($code, $message = '') {
				// null
			};
		}

		$write_status("start");

		if (!$this->isInstanceOutdated()) {
			$write_status("done");
			if (!$is_quiet) {
				$this->out("You are all up to date.");
			}
			exit(0);
		}

		#----------------------------------------
		# Requirement Checks
		#----------------------------------------

		$write_status("basic_checks_start");

		$checks_fail = false;

		#---
		# Binary Paths
		#---

		$php_path        = $this->getPhpBinaryPath();
		$mysql_dump_path = $this->getMysqldumpBinaryPath();
		$mysql_path      = $this->getMysqlBinaryPath();

		$this->log("php: $php_path\n");
		$this->log("mysql: $mysql_path\n");
		$this->log("mysqldump: $mysql_dump_path\n");

		if ($is_status_write) {
			if ($php_path) $write_status('php_path_okay'); else $write_status('error_php_path');
			if ($mysql_dump_path) $write_status('mysqldump_path_okay'); else $write_status('error_mysqldump_path');
			if ($mysql_path) $write_status('mysql_path_okay'); else $write_status('error_mysql_path');
		}

		if (!$php_path || !$mysql_path || !$mysql_dump_path) {
			$unknown_binary_paths = array();
			if (!$php_path)        $this->outAndLog("Cannot find path to `php` binary");
			if (!$mysql_dump_path) $this->outAndLog("Cannot find path to `mysqldump` binary");
			if (!$mysql_path)      $this->outAndLog("Cannot find path to `mysql` binary");

			$write_status("error_unknown_binary", $unknown_binary_paths);
			$checks_fail = true;
		}

		#---
		# Requirements check
		#---

		try {
			if (!is_dir($this->getBackupDir()) || !is_writable($this->getBackupDir())) {
				$write_status('error_backup_dir', $this->getBackupDir());
				$this->outAndLog("Backup directory does not exist or is not writable: " . $this->getBackupDir());
				$checks_fail = true;
			} else {
				$write_status('backup_dir_okay');
			}

			if (!is_dir($this->getLogDir()) || !is_writable($this->getLogDir())) {
				$write_status('error_log_dir', $this->getLogDir());
				$this->outAndLog("Log directory does not exist or is not writable: " . $this->getLogDir());
				$checks_fail = true;
			} else {
				$write_status('log_dir_okay');
			}

			if (!is_dir($this->getTmpDir()) || !is_writable($this->getTmpDir())) {
				$write_status('error_tmp_dir', $this->getTmpDir());
				$this->outAndLog("Tmp directory does not exist or is not writable: " . $this->getTmpDir());
				$checks_fail = true;
			} else {
				$write_status('tmp_dir_okay');
			}
		} catch (\Exception $e) {} // to catch error about log

		try {
			$this->zip = new ZipStrategy($this);
			$write_status('zip_ext_okay');
		} catch (\Exception $e) {
			$write_status('error_zip_ext', \Orb\Util\Env::getPhpIniPath());
			$this->outAndLog("To use this tool, the zlib or Zip PHP extensions must be enabled.");
			$checks_fail = true;
		}

		#---
		# File permissions: CHeck a few dirs/files to make sure we can write them all
		#---

		$check = array(
			DP_ROOT,
			DP_ROOT.'/src',
			DP_ROOT.'/sys',
			DP_ROOT.'/sys/cache',
			DP_ROOT.'/sys/cache',
			DP_ROOT.'/sys/cache/prod',
			DP_ROOT.'/sys/system.php',
			DP_ROOT.'/sys/vendor',
		);

		$write_fail = false;
		foreach ($check as $f) {
			if (file_exists($f) && !is_writable($f)) {
				$write_fail = $f;
				break;
			}
		}
		if ($write_fail) {

			$this->log("Failed write check on: $write_fail");

			$guess_user = 'unknown';
			if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
				$uinfo = @posix_getpwuid(@posix_geteuid());
				if (isset($uinfo['name'])) {
					$guess_user = $uinfo['name'];
				}
			} elseif (function_exists('get_current_user')) {
				if (@get_current_user()) {
					$guess_user = @get_current_user();
				}
			}

			$owner_user = 'unknown';
			if (function_exists('posix_geteuid') && function_exists('fileowner')) {
				$uinfo = @posix_getpwuid(@fileowner($write_fail));
				if (isset($uinfo['name'])) {
					$owner_user = $uinfo['name'];
				}
			}

			$checks_fail = true;
			$write_status("error_permissions", sprintf("User who is running the utility: %s, User who owns the files: %s", $guess_user, $owner_user));
			$this->outAndLog("Found insufficient write permissions to DeskPRO files. Does this user own them or have permission to write?");
			$this->outAndLog(sprintf("Current user: %s, File owner: %s", $guess_user, $owner_user));
		} else {
			$write_status("permissions_okay");
		}

		if ($checks_fail) {
			$write_status("error_basic_checks_fail");
			$this->outAndLog("Failed basic checks");
			exit(10);
		}

		$write_status("basic_checks_done");

		#----------------------------------------
		# Do upgrade
		#----------------------------------------

		// Shutdown helpdesk
		$fileutil = new FilesystemUtil();

		if (!$is_quiet) $this->out("Turning helpdesk off");
		$fileutil->touch(DP_ROOT.'/helpdesk-offline.trigger');

		try {
			$write_status("file_backup_start");
			if (!$skip_file_backup) {
				if (!$is_quiet) $this->out("Doing file backup ...");
				$this->file_backup = $this->backupFiles(true);
				if (!$is_quiet) $this->out("-> Done");
			}
			$write_status("file_backup_done");
		} catch (\Exception $e) {
			$write_status("error_backup_files", $e->getMessage());
			$fileutil->remove(DP_ROOT.'/helpdesk-offline.trigger');
			$this->out($e->getCode() . ' ' . $e->getMessage());
			$this->logException($e);
			exit(14);
		}

		try {
			$write_status("database_backup_start");
			if (!$skip_db_backup) {
				if (!$is_quiet) $this->out("Doing database backup ... ");
				$this->db_backup = $this->backupDatabase(true);
				if (!$is_quiet) $this->out("-> Done");
			}
			$write_status("database_backup_end");

		} catch (\Exception $e) {
			$write_status("error_backup_db", $e->getMessage());
			$fileutil->remove(DP_ROOT.'/helpdesk-offline.trigger');
			$this->out($e->getCode() . ' ' . $e->getMessage());
			$this->logException($e);
			exit(15);
		}

		try {
			$write_status("downloading_update_start");
			if (!$is_quiet) $this->out("Downloading latest source ...");
			$new_source_zip = $this->downloadLatest();
			if (!$is_quiet) $this->out("-> Done");
			$write_status("downloading_update_done");
		} catch (\Exception $e) {
			$write_status("error_downloading_update", $e->getMessage());
			$this->out($e->getCode() . ' ' . $e->getMessage());
			$this->logException($e);
			exit(20);
		}

		try {
			$this->revert_checkpoint = 'files';

			$write_status("installing_files_start");
			if (!$is_quiet) $this->out("Installing latest source files ...");
			$this->installFilesFromZip($new_source_zip, false);
			if (!$is_quiet) $this->out("-> Done");
			$write_status("installing_files_done");
		} catch (\Exception $e) {
			$write_status("error_installing_files", $e->getMessage());
			$this->out($e->getCode() . ' ' . $e->getMessage());
			$this->logException($e);

			if (!$is_error_halt) {
				$this->revertAutoUpgrade();
			}
			exit(25);
		}

		$this->revert_checkpoint = 'db';

		if (!$is_quiet) $this->out("Performing database upgrades ...");

		$write_status("updating_db_start");
		chdir(DP_ROOT);
		if ($is_quiet) {
			$cmd = "$php_path cmd.php dp:upgrade 2>&1";
			exec($cmd, $out, $ret);
		} else {
			$cmd = "$php_path cmd.php dp:upgrade 2>&1";
			$out = '';
			passthru($cmd, $ret);
		}
		chdir(DP_START_DIR);
		$write_status("updating_db_end");

		if ($ret) {
			$write_status("error_updating_db");
			$this->outAndLog("Upgrade returned erorr status $ret");
			if ($out) {
				foreach ($out as $l) {
					$this->outAndLog("-> $l");
				}
			}

			if (!$is_error_halt) {
				$write_status("reverting_files");
				$this->revertAutoUpgrade();
				$fileutil->touch(DP_ROOT.'/helpdesk-offline.trigger');
			}

			exit(30);
		}

		if (!$is_quiet) $this->out("-> Done");
		$fileutil->remove(DP_ROOT.'/helpdesk-offline.trigger');

		$write_status("done");

		$this->revert_checkpoint = null;
		$str = sprintf("Upgrade done in %.4f seconds", microtime(true) - $time_start);

		if ($is_quiet) {
			$this->log($str);
		} else {
			$this->outAndLog($str);
		}

		exit(0);
	}

	public function revertAutoUpgrade()
	{
		if ($this->revert_checkpoint == 'files' || $this->revert_checkpoint == 'db') {
			$this->installFilesFromZip($this->file_backup);
		}

		if ($this->revert_checkpoint == 'db'){
			$this->restoreDbFromZip($this->db_backup);
		}

		unlink(DP_ROOT.'/helpdesk-offline.trigger');

		$this->revert_checkpoint = null;
	}

	####################################################################################################################
	# run-db-upgrade
	####################################################################################################################

	public function runAction_dbUpgrade()
	{
		$php_path = $this->getPhpBinaryPath();
		if (!$php_path) {
			$this->outAndLog("Cannot find path to `php` binary");
		}

		chdir(DP_ROOT);
		$cmd = "$php_path cmd.php dp:upgrade 2>&1";
		$out = '';
		passthru($cmd, $ret);
		chdir(DP_START_DIR);

		return $ret;
	}

	####################################################################################################################
	# upgrade-files
	####################################################################################################################

	public function runAction_installLatestFiles()
	{
		$zip_path = null;
		$zip_specified = false;
		if (($key = array_search('--path', $this->argv)) !== false && isset($this->argv[$key+1])) {
			$zip_path = @realpath($this->argv[$key+1]);
			if (!file_exists($zip_path)) {
				$this->out("Invalid --path");
				exit(1);
			}

			$zip_specified = true;
		}

		if (!$zip_path) {
			$this->out("Downloading latest source ... ");
			$zip_path = $this->downloadLatest();
			$this->out("-> Done");

			$this->registerCleanupParam('unlink_zip_path', $zip_path);
		}

		$dry_run = in_array('--dry-run', $this->argv);

		$this->out("Installing files ... ");
		$this->installFilesFromZip($zip_path, $dry_run);
		$this->out("-> Done");

		if (!$zip_specified) {
			unlink($zip_path);
			$this->registerCleanupParam('unlink_zip_path', null);
		}
	}

	/**
	 * Replaces current source files with ones from $zip_path.
	 *
	 * Note: Only files that are in the source will be actually replaces. That means
	 * custom files remain (e.g., config.php).
	 *
	 * @param string $zip_path
	 */
	public function installFilesFromZip($zip_path, $dry_run = false)
	{
		if (!is_file($zip_path)) {
			throw new UpgradeFilesException("Zip path does not exist: $zip_path", UpgradeFilesException::BAD_ZIP);
		}

		$time_start = microtime(true);

		#------------------------------
		# Extract the zip into the dir
		#------------------------------

		$tmp_dir = $this->zip->decompressZip($zip_path);

		if (!$tmp_dir) {
			throw new UpgradeFilesException("Failed to extract zip", UpgradeFilesException::EXTRACT_ERROR);
		}

		#------------------------------
		# Now copy everything over
		#------------------------------

		$fileutil = new FilesystemUtil();
		if ($dry_run) {
			$fileutil->enableDryRun();
		}

		// Delete old cache dir
		$fileutil->remove(DP_ROOT.'/sys/cache/dev');
		$fileutil->remove(DP_ROOT.'/sys/cache/prod');
		$fileutil->remove(DP_ROOT.'/sys/cache/doctrine-proxies');
		$fileutil->remove(DP_ROOT.'/sys/cache/twig-compiled');

		// Copy all files over
		$fileutil->mirror($tmp_dir, DP_WEB_ROOT, null, array(
			'override' => true,
			'copy_on_windows' => true
		));

		$this->registerCleanupParam('unlink_scratch_dir', null);

		$this->log(sprintf('installFilesFromZip: time(%.4f)', microtime(true) - $time_start));
	}

	####################################################################################################################
	# backup-database
	####################################################################################################################

	public function runAction_backupDatabase()
	{
		try {
			$this->backupDatabase();
		} catch (MysqlBackupException $e) {
			$this->outAndLog($e->getMessage());
			exit(1);
		}
	}

	public function backupDatabase($overwrite = false)
	{
		global $DP_CONFIG;

		$time_start = microtime(true);

		$mysql_dump_path = $this->getMysqldumpBinaryPath();
		if (!$mysql_dump_path) {
			throw new MysqlBackupException("Could not find path to `mysqldump` command", MysqlBackupException::NO_MYSQLDUMP);
		}

		$f = date('Y-m-d') . '-database.sql';
		$f_full = $this->getBackupDir() . '/' . $f;

		if (file_exists($f_full)) {
			if ($overwrite) {
				unlink($f_full);
			}
			if (file_exists($f_full)) {
				throw new MysqlBackupException("Target backup file already exists: $f_full", MysqlBackupException::FILE_EXISTS);
			}
		}

		$pass = '';
		if ($DP_CONFIG['db']['password']) {
			$pass = "--password=" . escapeshellarg($DP_CONFIG['db']['password']);
		}
		$cmd = sprintf(
			"%s --opt -Q -h%s -u%s %s %s > %s",
			$mysql_dump_path,
			escapeshellarg($DP_CONFIG['db']['host']),
			escapeshellarg($DP_CONFIG['db']['user']),
			$pass,
			escapeshellarg($DP_CONFIG['db']['dbname']),
			escapeshellarg($f)
		);

		$this->log("Backup directory:  {$this->getBackupDir()}");
		$this->log("Backup command:    $cmd");

		$out = null;
		$ret = $this->execCommand($cmd, $this->getBackupDir(), $out);

		if ($ret) {
			$this->out("Backup error: Command exited with error status: $ret");

			foreach ($out as $l) {
				$this->out("-> " . $l);
			}

			throw new MysqlBackupException("Command exited with error status: $ret", MysqlBackupException::DUMP_ERROR);
		}

		// Try to verify that the complete dump is there
		$fh = fopen($f_full, 'r');
		fseek($fh, -256000, \SEEK_END);
		$code = fread($fh, 256000);

		if (strpos($code, 'CREATE TABLE `worker_jobs`') === false) {
			$this->log("Database dump seems invalid.");
			throw new MysqlBackupException("Database dump seems invalid", MysqlBackupException::DUMP_ERROR);
		}
		fclose($fh);
		unset($code);

		$this->log(sprintf("backupDatabase: time(%.4f)   dump_size(%d)", microtime(true) - $time_start, filesize($f_full)));
		$f_full = $this->compressFile($f_full);

		if (!$f_full) {
			return false;
		}

		$backup_path = $this->getBackupDir() . '/' . str_replace('.sql', '', $f) . '.zip';
		rename($f_full, $backup_path);

		return $backup_path;
	}

	public function getMysqldumpBinaryPath()
	{
		return dp_get_mysqldump_path();
	}

	public function getMysqlBinaryPath()
	{
		return dp_get_mysql_path();
	}

	public function getPhpBinaryPath()
	{
		return dp_get_php_path();
	}

	####################################################################################################################
	# restore-db
	####################################################################################################################

	public function runAction_restoreDatabase()
	{
		$fileutil = new FilesystemUtil();

		$zip_path = false;
		if (($key = array_search('--path', $this->argv)) !== false && isset($this->argv[$key+1])) {
			if ($fileutil->isAbsolutePath($this->argv[$key+1])) {
				$zip_path = @realpath($this->argv[$key+1]);
			} else {
				$zip_path = $this->getBackupDir() . '/' . $this->argv[$key+1];
			}
		}

		if (!$zip_path || !file_exists($zip_path)) {
			$this->out("Invalid --path. File does not exist: " . $zip_path);
			exit(1);
		}

		$this->out("Restoreing datbase ... ");
		$this->restoreDbFromZip($zip_path);
		$this->out("-> Done");
	}

	/**
	 * Drops everything from the current database, then installs dump
	 *
	 * @param string $zip_path
	 */
	public function restoreDbFromZip($zip_path)
	{
		if (!is_file($zip_path)) {
			throw new MysqlRestoreException("Zip path does not exist: $zip_path", MysqlRestoreException::BAD_ZIP);
		}

		$mysql_path = $this->getMysqlBinaryPath();
		if (!$mysql_path) {
			throw new MysqlBackupException("Could not find path to `mysql` command", MysqlRestoreException::NO_MYSQL);
		}

		$time_start = microtime(true);

		#------------------------------
		# Extract the zip into the dir
		#------------------------------

		$tmp_dir = $this->zip->decompressZip($zip_path);

		if (!$tmp_dir) {
			throw new UpgradeFilesException("Failed to extract zip", MysqlRestoreException::EXTRACT_ERROR);
		}

		// Find the SQL file
		$finder = new \Symfony\Component\Finder\Finder();
		$finder->in($tmp_dir)->files()->name('*.sql');

		$file = null;
		foreach ($finder as $file) break;

		if ($file === null) {
			throw new UpgradeFilesException("No sql file in the zip", MysqlRestoreException::EXTRACT_ERROR);
		}

		$sql_filename = $file->getFilename();

		#------------------------------
		# Drop everything from the database first
		#------------------------------

		global $DP_CONFIG;

		// Empty the db first
		$pdo = new \PDO("mysql:host={$DP_CONFIG['db']['host']};dbname={$DP_CONFIG['db']['dbname']}", $DP_CONFIG['db']['user'], $DP_CONFIG['db']['password']);
		$tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_NUM);

		$pdo->exec("SET foreign_key_checks = 0");
		foreach ($tables as $t) {
			$t = $t[0];
			$pdo->exec("DROP TABLE `$t`");
		}
		$pdo->exec("SET foreign_key_checks = 1");

		#------------------------------
		# Restore dump
		#------------------------------

		$pass = '';
		if ($DP_CONFIG['db']['password']) {
			$pass = "--password=".escapeshellarg($DP_CONFIG['db']['password']);
		}
		$cmd = sprintf(
			'%s -h%s -u%s %s %s < %s',
			$mysql_path,
			escapeshellarg($DP_CONFIG['db']['host']),
			escapeshellarg($DP_CONFIG['db']['user']),
			$pass,
			escapeshellarg($DP_CONFIG['db']['dbname']),
			escapeshellarg($sql_filename)
		);

		$ret = $this->execCommand($cmd, $tmp_dir);
		if ($ret) {
			throw new UpgradeFilesException("Error importing database backup", MysqlRestoreException::RESTORE_ERROR);
		}

		$this->log(sprintf("backupDatabase: time(%.4f)", microtime(true) - $time_start));
	}

	####################################################################################################################
	# backup-files
	####################################################################################################################

	public function runAction_backupFiles()
	{
		try {
			$this->backupFiles();
		} catch (FileBackupException $e) {
			$this->outAndLog($e->getMessage());
			exit(1);
		}
	}

	public function backupFiles()
	{
		$time_start = microtime(true);

		$f = '/' . date('Y-m-d') . '-files';
		$backup_dir = $this->getTmpDir() . $f;
		if (is_dir($backup_dir)) {
			throw new FileBackupException("Backup directory already exists: $backup_dir", FileBackupException::FILE_EXISTS);
		}

		if (!mkdir($backup_dir, 0755, true)) {
			throw new FileBackupException("Could not create backup directory: $backup_dir", FileBackupException::PERM_ERROR);
		}

		$finder = new \Symfony\Component\Finder\Finder();
		$finder->in(DP_WEB_ROOT)->files();
		$file_list = iterator_to_array($finder, false);

		$count_file = 0;
		$count_dir = 0;
		foreach ($file_list as $file) {
			/** @var $file \Symfony\Component\Finder\SplFileInfo */

			// Ignore data dir
			if (strpos($file->getRealPath(), DP_WEB_ROOT.DIRECTORY_SEPARATOR.'data') === 0) {
				continue;
			}

			$file_rel_dir = str_replace(DP_WEB_ROOT, '', dirname($file->getRealPath()));
			$file_backup_dir = $backup_dir . '/' . $file_rel_dir;

			if (!is_dir($file_backup_dir)) {
				$count_dir++;
				if (!mkdir($file_backup_dir, 0755, true)) {
					throw new FileBackupException("Could not create backup directory: $backup_dir", FileBackupException::PERM_ERROR);
				}
			}

			if (!copy($file->getRealPath(), $file_backup_dir . '/' . $file->getFilename())) {
				throw new FileBackupException("Could not copy file to backup directory: {$file->getRealPath()} to {$file_backup_dir}{$file->getFilename()}", FileBackupException::PERM_ERROR);
			}

			$count_file++;
		}

		$this->log(sprintf("backupFiles: time(%.4f)   file_count(%d)    dir_count(%d)", microtime(true) - $time_start, $count_file, $count_dir));

		$f_path = $this->compressFile($backup_dir);

		if (!$f_path) {
			return false;
		}

		$backup_file = $this->getBackupDir() . '/' . $f . '.zip';
		if (is_file($backup_file)) {
			unlink($backup_file);
		}
		rename($f_path, $backup_file);

		return $backup_file;
	}


	####################################################################################################################
	# restore-files
	####################################################################################################################

	public function runAction_restoreFiles()
	{
		$fileutil = new FilesystemUtil();

		$zip_path = false;
		if (($key = array_search('--path', $this->argv)) !== false && isset($this->argv[$key+1])) {
			if ($fileutil->isAbsolutePath($this->argv[$key+1])) {
				$zip_path = @realpath($this->argv[$key+1]);
			} else {
				$zip_path = $this->getBackupDir() . '/' . $this->argv[$key+1];
			}
		}

		if (!$zip_path || !file_exists($zip_path)) {
			$this->out("Invalid --path. File does not exist: " . $zip_path);
			exit(1);
		}

		$dry_run = in_array('--dry-run', $this->argv);

		$this->out("Restoreing files ... ");
		$this->installFilesFromZip($zip_path, $dry_run);
		$this->out("-> Done");
	}


	####################################################################################################################
	# download-latest
	####################################################################################################################

	public function runAction_downloadLatest()
	{
		$save_path = null;
		if (($key = array_search('--path', $this->argv)) !== false || isset($this->argv[$key+1])) {
			$save_path = @realpath($this->argv[$key+1]);
			if (!$save_path) {
				$this->out("Invalid --path");
				exit(1);
			}
		}

		try {
			$this->downloadLatest($save_path);
		} catch (DownloadException $e) {
			$this->outAndLog($e->getMessage());
			exit(1);
		}
	}


	/**
	 * Download the latest copy of DeskPRO into $save_path. If $save_path is not defined, then it will be put
	 * into the backups directory.
	 *
	 * @param $save_path
	 * @return string The path it was saved to
	 */
	public function downloadLatest($save_path = null)
	{
		$version_info = $this->getLatestVersion();

		$time_start = microtime(true);

		if (!$save_path) {
			$save_path = $this->getTmpDir();
		}

		if (is_dir($save_path)) {
			$save_path .= '/' . basename(dirname($version_info['download'])) . '-' . basename($version_info['download']);
		}

		$save_dir = dirname($save_path);

		if (!is_dir($save_dir) && !@mkdir($save_dir, 0777, true)) {
			throw new DownloadException("Save directory does not exist: " . $save_dir, DownloadException::NO_DIR);
		}

		if (!is_writable($save_dir)) {
			throw new DownloadException("Save directory is not writable: " . $save_dir, DownloadException::PERM_ERROR);
		}

		// It already exists, just return it
		if (file_exists($save_path)) {
			return $save_path;
		}

		$this->log("downloadLatest: Downloading from " . $version_info['download']);
		$this->log("downloadLatest: Saving to " . $save_path);

		file_put_contents($save_path, file_get_contents($version_info['download']));

		$this->log(sprintf("downloadLatest: time(%.4f)  file_size(%d)", microtime(true) - $time_start, filesize($save_path)));

		if (filesize($save_path) < 15728640) {
			//throw new DownloadException(sprintf("Saved file seems too small: $save_path is %d bytes", filesize($save_path)), DownloadException::BAD_FILE);
		}

		return $save_path;
	}


	####################################################################################################################
	# check-version
	####################################################################################################################

	public function runAction_checkVersion()
	{
		$version_info = $this->getLatestVersion();

		$this->out(sprintf("Your build:      %s (%s)", DP_BUILD_TIME, $this->formatBuild(DP_BUILD_TIME)));
		$this->out(sprintf("Latest build:    %s (%s)", $version_info['build'], $this->formatBuild($version_info['build'])));
		$this->out(sprintf("                 %s", $version_info['download']));
		$this->out(str_repeat('-', 70));

		$this->log(sprintf("runCheckVersion: current(%s)   latest(%s)", DP_BUILD_TIME, $version_info['build']));

		if ($this->isInstanceOutdated()) {
			$this->out("Your instance is outdated. You should upgrade.");
		} else {
			$this->out("Your instance is up to date.");
		}

		echo "\n";
	}


	/**
	 * Is the currently installed instance outdated?
	 *
	 * @return bool
	 */
	public function isInstanceOutdated()
	{
		$version_info = $this->getLatestVersion();

		if (DP_BUILD_TIME < $version_info['build']) {
			return true;
		}

		return false;
	}


	/**
	 * @return array
	 */
	public function getLatestVersion()
	{
		if ($this->latest_version !== null) {
			return $this->latest_version;
		}

		$this->latest_version = $this->callService('check-latest-version.json');

		return $this->latest_version;
	}


	####################################################################################################################

	/**
	 * Compress a file or directory with ZIP.
	 *
	 * @param $path
	 */
	public function compressFile($path)
	{
		$time_start = microtime(true);

		$out_filepath = $this->zip->compressFile($path);
		if (!$out_filepath) {
			return false;
		}

		// Double check the out file too
		$success = true;
		if ($out_filepath) {
			if (!file_exists($out_filepath) || filesize($out_filepath) < 10) {
				$this->log("compressFile: reported success but file looks bad: $out_filepath");
				$success = false;
			}
		}

		// If we're a success, then we can remove the original
		if ($success) {
			$fileutil = new FilesystemUtil();
			$fileutil->remove($path);
		}

		$this->log(sprintf("compressFile: time(%.4f)   file_size(%d)", microtime(true) - $time_start, filesize($out_filepath)));

		return $out_filepath;
	}


	/**
	 * Executes a $command in $dir, puts the output in $out, and returns the status of the command.
	 *
	 * @param string $command
	 * @param string $dir
	 * @param array  $out
	 * @return int
	 */
	public function execCommand($command, $dir = null, &$out = null)
	{
		$this->log(sprintf("execCommand: dir(%s)  cmd(%s)", $dir, $command));

		@set_time_limit(1800);

		if ($dir) {
			chdir($dir);
		}

		$command .= ' 2>&1';
		$ret = 0;
		exec($command, $out, $ret);

		chdir(DP_START_DIR);
		@set_time_limit(0);

		$this->log(sprintf("execCommand: -> result: %d", $ret));
		if (strpos($command, '--help') === false) {
			foreach ($out as $l) {
				$l = trim($l);
				if ($l !== '') {
					$this->log(sprintf("execCommand: -> %s", $l));
				}
			}
		}

		return $ret;
	}


	/**
	 * @return string
	 */
	public function getBackupDir()
	{
		return dp_get_backup_dir();
	}


	/**
	 * @return string
	 */
	public function getTmpDir()
	{
		return dp_get_tmp_dir();
	}


	/**
	 * @return string
	 */
	public function getLogDir()
	{
		return dp_get_log_dir();
	}


	/**
	 * Formats a build as a time
	 *
	 * @param $build
	 * @return string
	 */
	public function formatBuild($build)
	{
		return date('Y-m-d H:i:s', $build);
	}


	/**
	 * Call a DeskPRO service
	 *
	 * @param string $endpoint
	 * @param array $post_data
	 * @return array
	 */
	public function callService($endpoint, array $post_data = array())
	{
		$url = DP_MA_SERVER . '/api/' . ltrim($endpoint, '/');
		return $this->fetchServiceResult($url, $post_data);
	}


	/**
	 * @param string $url
	 * @param array $post_data
	 * @return array
	 */
	public function fetchServiceResult($url, array $post_data = array())
	{
		$context = stream_context_create(array(
			'http' => array(
				'timeout'  => 15,
				'method'   => 'POST',
				'header'   => 'Content-type: application/x-www-form-urlencoded',
				'content'  => http_build_query($post_data, null, '&')
			)
		));

		$result = @file_get_contents($url, null, $context);

		if (!$result) {
			throw new ServiceCallException("No response from server: $url $result", ServiceCallException::INVALID_RESPONSE);
		}

		$res_data = @json_decode($result, true);
		if (!is_array($res_data)) {
			throw new ServiceCallException("Invalid JSON response from server: $url $result", ServiceCallException::INVALID_RESPONSE);
		}

		return $res_data;
	}


	/**
	 * Register a cleanup param
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function registerCleanupParam($name, $value)
	{
		global $UPGRADE_CLEANUP;
		if (!$UPGRADE_CLEANUP) {
			$UPGRADE_CLEANUP = array();
		}

		if ($value === null) {
			unset($UPGRADE_CLEANUP[$name]);
		} else {
			$UPGRADE_CLEANUP[$name] = $value;
		}
	}

	/**
	 * @static
	 * @param int $bytes
	 * @return string
	 */
	public static function getFilesizeDisplay($bytes)
	{
		if (!$bytes OR $bytes < 1) {
			return array('number' => 0, 'symbol' => 'B');
	    }

	    $all_symbols = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $exp = floor(log($bytes)/log(1024));
        $val = $bytes/pow(1024, floor($exp));

        $sym = '';
        if (isset($all_symbols[$exp])) {
            $sym = $all_symbols[$exp];
        }

		$parts=  array(
			'number' => $val,
			'symbol' => $sym
		);

		return sprintf('%.2f %s', $parts['number'], $parts['symbol']);
	}
}

########################################################################################################################
# Shutdown handler
########################################################################################################################

/**
 * When things quit unexpectedly, try to clean up anything that might be left over.
 */
function Upgrade_Shutdown_Function()
{
	global $UPGRADE_CLEANUP;
	if (!$UPGRADE_CLEANUP) {
		return;
	}

	$fileutil = new FilesystemUtil();

	if (isset($UPGRADE_CLEANUP['close_log_fh'])) {
		@fclose($UPGRADE_CLEANUP['close_log_fh']);
	}
	if (isset($UPGRADE_CLEANUP['unlink_zip_path'])) {
		try {
			$fileutil->remove($UPGRADE_CLEANUP['unlink_zip_path']);
		} catch (\Exception $e) {}
	}
	if (isset($UPGRADE_CLEANUP['unlink_scratch_dir'])) {
		try {
			$fileutil->remove($UPGRADE_CLEANUP['unlink_scratch_dir']);
		} catch (\Exception $e) {}
	}

	try {
		$fileutil->remove(DP_ROOT.'/helpdesk-offline.trigger');
	} catch (\Exception $e) {}

	$UPGRADE_CLEANUP = null;
}

########################################################################################################################
# Custom filesystem util class
########################################################################################################################

class FilesystemUtil extends \Symfony\Component\HttpKernel\Util\Filesystem
{
	protected $dry_run = false;

	public function enableDryRun()
	{
		$this->dry_run = true;
	}

	public function copy($originFile, $targetFile, $override = false)
	{
		if ($this->dry_run) {
			echo "[copy] $originFile => $targetFile\n";
			return;
		}

		parent::copy($originFile, $targetFile, $override);
	}

	public function mkdir($dirs, $mode = 0777)
	{
		if ($this->dry_run) {
			foreach ($this->toIterator($dirs) as $dir) {
				if (is_dir($dir)) {
					continue;
				}

				echo "[mkdir] $dir\n";
			}

			return true;
		}

		return parent::mkdir($dirs, $mode);
	}

	public function touch($files)
	{
		if ($this->dry_run) {
			foreach ($this->toIterator($files) as $file) {
				echo "[touch] $file\n";
			}
			return;
		}

		parent::touch($files);
	}

	public function remove($files)
	{
		if ($this->dry_run) {
			$files = iterator_to_array($this->toIterator($files));
			$files = array_reverse($files);
			foreach ($files as $file) {
				if (!file_exists($file)) {
					continue;
				}

				if (is_dir($file) && !is_link($file)) {
					echo "[rmdir] $file\n";
				} else {
					echo "[rm] $file\n";
				}
			}
			return;
		}

		parent::remove($files);
	}

	public function chmod($files, $mode, $umask = 0000)
	{
		if ($this->dry_run) {
			foreach ($this->toIterator($files) as $file) {
				printf("[chmod] %o %s\n", $file, $mode);
			}
			return;
		}

		parent::chmod($files, $mode, $umask);
	}

	public function rename($origin, $target)
	{
		if ($this->dry_run) {
			echo "[rename] $origin => $target\n";
			return;
		}

		parent::rename($origin, $target);
	}

	public function symlink($originDir, $targetDir, $copyOnWindows = false)
	{
		if ($this->dry_run) {
			echo "[symlink] $originDir => $targetDir\n";
			return;
		}

		parent::symlink($originDir, $targetDir);
	}

	private function toIterator($files)
	{
		if (!$files instanceof \Traversable) {
			$files = new \ArrayObject(is_array($files) ? $files : array($files));
		}

		return $files;
	}
}

########################################################################################################################
# Interactive Upgrader
########################################################################################################################

class UpgradeInteractive implements \Symfony\Component\Console\Output\OutputInterface
{
	/**
	 * @var \DeskPRO\Tools\Upgrade
	 */
	protected $upgrade;

	/**
	 * @var \Symfony\Component\Console\Formatter\OutputFormatter
	 */
	protected $outputFormatter;

	/**
	 * @var \Symfony\Component\Console\Helper\DialogHelper
	 */
	protected $dialogHelper;

	/**
	 * @var int
	 */
	protected $spinner_state = 0;

	/**
	 * @var string
	 */
	protected $dl_distro = null;
	protected $file_backup = null;
	protected $db_backup = null;
	protected $revert_checkpoint = null;

	protected $answer_backup_files = null;
	protected $answer_backup_db = null;

	/**
	 * @param \DeskPRO\Tools\Upgrade $upgrade
	 */
	public function __construct(Upgrade $upgrade)
	{
		$this->upgrade = $upgrade;

		#------------------------------
		# Load the required Symfony libs
		#------------------------------

		require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Console/Formatter/OutputFormatterStyleInterface.php';
		require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Console/Formatter/OutputFormatterStyle.php';
		require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Console/Formatter/OutputFormatter.php';
		require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Console/Helper/HelperInterface.php';
		require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Console/Helper/Helper.php';
		require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Console/Helper/DialogHelper.php';
		require DP_ROOT.'/vendor/symfony/src/Symfony/Component/Console/Helper/FormatterHelper.php';

		#------------------------------
		# Create helpers
		#------------------------------

		$decorated = true;
		if (strpos(strtoupper(PHP_OS), 'WIN') === 0) {
			$decorated = false;
		}
		$this->outputFormatter = new \Symfony\Component\Console\Formatter\OutputFormatter($decorated, array(
			'title' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('white', 'blue', array('bold')),
			'note' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('yellow', null),
			'prompt' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('cyan', 'black')
		));

		$this->dialogHelper    = new \Symfony\Component\Console\Helper\DialogHelper();

		#------------------------------
		# Check requirements
		#------------------------------

		$php_path        = $this->upgrade->getPhpBinaryPath();
		$mysql_dump_path = $this->upgrade->getMysqldumpBinaryPath();
		$mysql_path      = $this->upgrade->getMysqlBinaryPath();

		if (!$php_path || !$mysql_path || !$mysql_dump_path) {

			$this->out("<error>Error: We could not find the path to an important utility</error>");

			$this->out("The upgrader could not locate the paths to the following utilitie(s):");
			if (!$php_path) {
				$this->upgrade->log("Cannot find path to `php` binary");
				$this->out("\t- Could not find the path to php");
			}
			if (!$mysql_dump_path) {
				$this->upgrade->log("Cannot find path to `mysqldump` binary");
				$this->out("\t- Could not find the path to mysqldump");
			}
			if (!$mysql_path) {
				$this->upgrade->log("Cannot find path to `mysql` binary");
				$this->out("\t- Could not find the path to mysql");
			}

			$this->out();
			$this->out("Edit your config.php file to learn more about locating these utilities and setting their paths.");

			exit(10);
		}

		#------------------------------
		# GO
		#------------------------------

		$this->outHeader('DeskPRO Upgrader', true);
		$this->out();

		$this->out(
			"<info>Welcome to the DeskPRO interactive upgrader.\n"
			."For help please visit: http://support.deskpro.com</info>"
		);

		$this->out();

		#------------------------------
		# Menu
		#------------------------------

		try {
			$version_info = $this->upgrade->getLatestVersion();
		} catch (\Exception $e) {
			$this->upgrade->log("getLatestVersion error: {$e->getMessage()}");
			$version_info = null;
		}

		#-----
		# We have version info
		#-----

		if ($version_info) {
			$this->out(sprintf("Your build:      %s (%s)", DP_BUILD_TIME, $this->upgrade->formatBuild(DP_BUILD_TIME)));
			$this->out(sprintf("Latest build:    %s (%s)", $version_info['build'], $this->upgrade->formatBuild($version_info['build'])));
			$this->upgrade->log(sprintf("runCheckVersion: current(%s)   latest(%s)", DP_BUILD_TIME, $version_info['build']));

			$this->out();

			if ($this->upgrade->isInstanceOutdated()) {
				$this->out("<prompt>Your current instance is outdated. Would you like to download updates now?</prompt>");
				$this->out("[Y/n]> ", '', true);

				$ret = $this->dialogHelper->askConfirmation($this, false);
				if ($ret) {
					$this->runAction_downloadAndInstallChoice();
				} else {
					$this->runAction_checkAndUpgrade();
				}
			} else {
				$this->runAction_checkAndUpgrade();
			}

		#-----
		# We don't know about the version
		#-----

		} else {

			$this->out(
				"<error>We could not fetch version information from our web server. There are a number of possible causes:\n"
				."    - Your server is behind a firewall\n"
				."    - There is a network problem between your server and ours\n"
				."    - Our version server may be having difficulties. Check http://www.deskpro.com/status/\n"
				."\n"
				."You can try again but if you continue to experience trouble, you can contact us at support@deskpro.com</error>"
			);
			$this->out();

			$this->out(
				"<prompt>Would you like to continue? If you have manually updated DeskPRO files, or if you wish to"
				." check the version of your database, you can still run this tools.</prompt>"
			);
			$this->out("Continue? [y/N]> ", false);
			$ret = $this->dialogHelper->askConfirmation($this, '', false);

			if (!$ret) {
				$this->out("\n");
				exit(0);
			}

			$this->runAction_checkAndUpgrade();
		}
	}

	/**
	 * Download and install updates
	 */
	public function	runAction_downloadAndInstallChoice()
	{
		$this->out();

		#------------------------------
		# Download
		#------------------------------

		$this->spinner("Downloading latest version ...");

		try {
			$this->dl_distro = $this->upgrade->downloadLatest();
		} catch (DownloadException $e) {
			$this->clearSpinner();
			$this->upgrade->outAndLog($e->getMessage());
			$this->errorExit("There was a problem trying to download the latest version. Try again later.");
		}

		$this->clearSpinner();

		$this->out("<info>Download was successful. Pacakge saved to:\n{$this->dl_distro}\n</info>");
		$this->out();

		$this->out("<prompt>Before we install the updates, you should generate a back up first. You can back up both your files and your database.\n</prompt>");

		$db_backup_path   = $this->upgrade->getBackupDir() . '/' . date('Y-m-d') . '-database.zip';
		$file_backup_path = $this->upgrade->getBackupDir() . '/' . date('Y-m-d') . '-files.zip';

		while(true) {
			$this->out("Do you want to back up your current source files? [Y/n]> ", false);
			$this->answer_backup_files = $this->dialogHelper->askConfirmation($this, '', true);

			$this->out("Do you want to back up your database? [Y/n]> ", false);
			$this->answer_backup_db = $this->dialogHelper->askConfirmation($this, '', true);

			$this->out();
			$this->out("<comment>Backup files: " . ($this->answer_backup_db ? "YES" : "NO") . "</comment>");
			$this->out("<comment>Backup database: " . ($this->answer_backup_files ? "YES" : "NO") . "</comment>");

			if ($this->answer_backup_files && is_file($file_backup_path)) {
				$this->out("<warn>WARNING: File backup for today already exists. It will be overwritten if you continue.</warn>");
			}
			if ($this->answer_backup_db && is_file($db_backup_path)) {
				$this->out("<warn>WARNING: Database backup for today already exists. It will be overwritten if you continue.</warn>");
			}

			$this->out();
			$this->out("<prompt>Are you ready to continue?\nAnswer 'n' to re-input backup options.</prompt>");
			$this->out("Continue with the upgrade? [Y/n]> ", false);

			$ret = $this->dialogHelper->askConfirmation($this, '', true);
			if ($ret) {
				break;
			}
			$this->out();
		}

		$fileutil = new FilesystemUtil();
		$fileutil->touch(DP_ROOT.'/helpdesk-offline.trigger');

		if (is_file($db_backup_path)) {
			$fileutil->remove($db_backup_path);
		}
		if (is_file($file_backup_path)) {
			$fileutil->remove($file_backup_path);
		}

		#------------------------------
		# Backup files
		#------------------------------

		$this->outHeader("Installing Updates");
		$this->out();

		if ($this->answer_backup_files) {
			$this->out(sprintf("%-40s", "<info>[*] Backing up files ...</info>"), false);

			try {
				$this->file_backup = $this->upgrade->backupFiles();
			} catch (\Exception $e) {
				$this->upgrade->outAndLog($e->getMessage());
				$this->errorExit("There was a problem backing up your files.");
			}

			$this->revert_checkpoint = 'files';
			$this->out("<info>DONE</info>");

			$this->out(sprintf("    File: %s :: %s", Upgrade::getFilesizeDisplay(filesize($file_backup_path)), $file_backup_path));
		}

		#------------------------------
		# Install files
		#------------------------------

		$this->out(sprintf("%-55s", "<info>[*] Installing files ...</info>"), false);

		try {
			$this->upgrade->installFilesFromZip($this->dl_distro, false);
		} catch (\Exception $e) {
			$this->upgrade->outAndLog($e->getMessage());
			$this->errorExit("There was a problem installing the new files.");
		}

		$this->out("<info>DONE</info>");

		#------------------------------
		# Backup database
		#------------------------------

		if ($this->answer_backup_db) {
			$this->out(sprintf("%-55s", "<info>[*] Backing up database ...</info>"), false);

			try {
				$this->db_backup = $this->upgrade->backupDatabase();
			} catch (\Exception $e) {
				$this->upgrade->outAndLog($e->getMessage());
				$this->errorExit("There was a problem backing up your database.");
			}

			$this->out(sprintf("    File: %s :: %s", Upgrade::getFilesizeDisplay(filesize($db_backup_path)), $db_backup_path));

			$this->revert_checkpoint = 'db';
			$this->out("<info>DONE</info>");
		}

		#------------------------------
		# Run upgrader
		#------------------------------

		$this->out(sprintf("%-55s", "<info>[*] Installing database updates</info>"));

		$php_path = $this->upgrade->getPhpBinaryPath();

		chdir(DP_ROOT);
		$cmd = "$php_path cmd.php dp:upgrade 2>&1";
		passthru($cmd, $ret);
		chdir(DP_START_DIR);

		if ($ret) {
			$this->outAndLog("Upgrade returned erorr status $ret");
			$this->errorExit("There was a problem installing the database updates");
		}

		$fileutil->remove(DP_ROOT.'/helpdesk-offline.trigger');

		$this->outHeader("DONE");
		$this->out();

		$this->out("<info>DeskPRO has been upgraded successfully.</info>");
		$this->out();
		$this->out('');
	}


	/**
	 * Running just the upgrade against currently file sources
	 */
	public function runAction_checkAndUpgrade()
	{
		global $DP_CONFIG;

		#------------------------------
		# Check versions
		#------------------------------

		$pdo = new \PDO("mysql:host={$DP_CONFIG['db']['host']};dbname={$DP_CONFIG['db']['dbname']}", $DP_CONFIG['db']['user'], $DP_CONFIG['db']['password']);
		$version = $pdo->query("SELECT value FROM settings WHERE name = 'core.deskpro_build'")->fetch(\PDO::FETCH_NUM);

		if (!$version) {
			$this->errorExit("We could not find your currently installed version.");
		}

		$version = $version[0];

		$this->out(sprintf("File build version:      %s (%s)", DP_BUILD_TIME, $this->upgrade->formatBuild(DP_BUILD_TIME)));
		$this->out(sprintf("Database build version:  %s (%s)", $version, $this->upgrade->formatBuild($version)));

		$this->out();

		if ($version >= DP_BUILD_TIME) {
			$this->out("<info>Your database and source file builds correspond. No database upgrades need to be run.</info>");
			$this->out();
			$this->out("");
			exit(0);
		}

		#------------------------------
		# Gather input
		#------------------------------

		$this->out("<info>Your database is out of date. Would you like to perform an upgrade now?</info>");
		$this->out("Upgrade now? [Y/n]> ", false);

		$ret = $this->dialogHelper->askConfirmation($this, '', true);
		if (!$ret) {
			$this->out();
			$this->out("");
			exit(0);
		}

		$this->out("<prompt>Before we install the updates, you should generate back up first.</prompt>");

		while(true) {
			$this->out("Do you want to back up your database? [Y/n]> ", false);
			$this->answer_backup_db = $this->dialogHelper->askConfirmation($this, '', true);

			$this->out();
			$this->out("<comment>Backup database: " . ($this->answer_backup_db ? "YES" : "NO") . "</comment>");

			$this->out();
			$this->out("<prompt>Are you ready to continue? Answer 'n' to re-input backup options.</prompt>");
			$this->out("Continue with the upgrade? [Y/n]> ", false);

			$ret = $this->dialogHelper->askConfirmation($this, '', true);
			if ($ret) {
				break;
			}
			$this->out();
		}

		$fileutil = new FilesystemUtil();
		$fileutil->touch(DP_ROOT.'/helpdesk-offline.trigger');

		#------------------------------
		# Backup database
		#------------------------------

		if ($this->answer_backup_db) {
			$this->out(sprintf("%-40s", "<info>[*] Backing up database ...</info>"), false);

			try {
				$this->db_backup = $this->upgrade->backupDatabase();
			} catch (\Exception $e) {
				$this->upgrade->outAndLog($e->getMessage());
				$this->errorExit("There was a problem backing up your database.");
			}

			$this->revert_checkpoint = 'db';
			$this->out("<info>DONE</info>");
		}

		#------------------------------
		# Run upgrader
		#------------------------------

		$this->out(sprintf("%-40s", "<info>[*] Installing database updates</info>"));

		$php_path = $this->upgrade->getPhpBinaryPath();

		chdir(DP_ROOT);
		$cmd = "$php_path cmd.php dp:upgrade 2>&1";
		passthru($cmd, $ret);
		chdir(DP_START_DIR);

		if ($ret) {
			$this->outAndLog("Upgrade returned erorr status $ret");
			$this->errorExit("There was a problem installing the database updates");
		}


		$this->outHeader("DONE");

		$this->out("<info>DeskPRO has been upgraded successfully.</info>");
		$this->out();
		$this->out('');
	}


	/**
	 * @param string $message
	 */
	public function errorExit($message = '')
	{
		if ($message) {
			$this->out("<error>$message</error>");
			$this->out();
		}

		if ($this->file_backup && $this->revert_checkpoint == 'files' || $this->revert_checkpoint == 'db') {
			$this->out(sprintf("%-40s", "<info>[*] Restoring files from backup ...</info>"), false);
			$this->upgrade->installFilesFromZip($this->file_backup);
			$this->out("<info>Done</info>");
		}

		if ($this->revert_checkpoint == 'db'){
			$this->out(sprintf("%-40s", "<info>[*] Restoring database from backup ...</info>"), false);
			$this->restoreDbFromZip($this->db_backup);
			$this->out("<info>Done</info>");
		}

		$fileutil = new FilesystemUtil();
		$fileutil->remove(DP_ROOT.'/helpdesk-offline.trigger');

		exit(1);
	}


	/**
	 * Infinite spinner
	 */
	public function spinner($message = '')
	{
		echo "\r";
		echo str_repeat(' ', 70);
		echo "\r";

		echo "(";
		switch ($this->spinner_state) {
			case 0:
				echo "-";
				break;

			case 1:
				echo "\\";
				break;

			case 2:
				echo "|";
				break;

			case 3:
				echo "/";
				break;
		}

		$this->spinner_state++;
		if ($this->spinner_state > 3) {
			$this->spinner_state = 0;
		}

		echo ")";
		if ($message) {
			echo " $message";
		}
	}


	/**
	 * Clear the spinner form the line
	 */
	public function clearSpinner()
	{
		$this->spinner_state = 0;
		echo "\r";
		echo str_repeat(' ', 72);
		echo "\r";
	}


	/**
	 * @param string $string
	 * @param bool $nl
	 */
	public function out($string = '', $nl = true)
	{
		$tagged = null;
		if (preg_match('#^<(.*?)>(.*?)</$1>$#', $string, $m)) {
			$tagged = $m[1];
			$string = $m[2];
		}

		$string = wordwrap($string, 72, "\n", true);

		if ($tagged) {
			$string = "<$tagged>$string</$tagged>";
		}

		$string = $this->outputFormatter->format($string);
		$this->upgrade->out($string, $nl);
	}


	/**
	 * @param string $title
	 * @param bool $big
	 */
	public function outHeader($title, $big = false)
	{
		$string = '';
		if ($big) {
			$string .= str_repeat(' ', 72) . "\n";
		}

		$len = strlen($title);
		$remain = 72-$len;
		$left  = floor($remain/2);
		$right = 72 - $len - $left;

		$string .= str_repeat(' ', $left) . $title . str_repeat(' ', $right);

		if ($big) {
			$string .= "\n" . str_repeat(' ', 72);
		}

		$string = $this->outputFormatter->format("<title>$string</title>");

		echo $string;
	}

	/**
	 * @param $note
	 */
	public function outNote($note)
	{
		$note = wordwrap($note, 65, "\n", true);

		$lines = explode("\n", $note);
		foreach ($lines as &$l) $l = '    > ' . $l;
		$note = implode("\n", $lines);

		$string = $this->outputFormatter->format("<note>$note</note>");

		echo $string;
	}

	function write($messages, $newline = false, $type = 0)
	{
		$this->out($messages, $newline);
	}

	function writeln($messages, $type = 0)
	{
		$this->out($messages, true);
	}

	function setVerbosity($level)
	{

	}

	function getVerbosity()
	{
		return 1;
	}

	function setDecorated($decorated)
	{

	}

	function isDecorated()
	{
		return true;
	}

	function setFormatter(\Symfony\Component\Console\Formatter\OutputFormatterInterface $formatter)
	{

	}

	function getFormatter()
	{
		return $this->outputFormatter;
	}
}

########################################################################################################################
# ZIP Classes
########################################################################################################################

class ZipStrategy implements DpZip
{
	protected $zip;

	public function __construct(Upgrade $upgrade, $force_strategy = null)
	{
		if ($force_strategy !== null) {
			switch ($force_strategy) {
				case 'Zip_PHP':     $this->zip = new Zip_PHP($upgrade);     return;
				case 'Zip_PclZip':  $this->zip = new Zip_PclZip($upgrade);  return;
			}
		}

		if (extension_loaded('Zip')) {
			$upgrade->log("ZipStrategy: Zip_PHP");
			$this->zip = new Zip_PHP();
		} elseif (extension_loaded('zlib')) {
			$upgrade->log("ZipStrategy: Zip_PclZip");
			$this->zip = new Zip_PclZip();
		} else {
			throw new ZipException("Zip and zlib extensions not installed, no way to zip");
		}
	}

	public function compressFile($path)
	{
		return $this->zip->compressFile($path);
	}

	public function decompressZip($path, $to = null)
	{
		return $this->zip->decompressZip($path, $to);
	}
}

interface DpZip
{
	/**
	 * Compress a file or directory of files.
	 * If a directory, the ZIP should be created at the root. E.g., extracting
	 * should extract into the cwd.
	 *
	 * @param string $path
	 * @return string
	 */
	public function compressFile($path);

	/**
	 * Decompress a zip file
	 *
	 * @param string $path
	 * @return string
	 */
	public function decompressZip($path, $to = null);
}

class Zip_PHP implements DpZip
{
	public function compressFile($path)
	{
		$path = str_replace('\\', '/', $path);
		$path = rtrim($path, '/');

		$dir          = dirname($path);
		$filename     = basename($path);
		$out_filename = $filename . '-' . time() . '-' . mt_rand(1000,9999) . '.zip';
		$out_filepath = sys_get_temp_dir() . '/' . $out_filename;

		$zip = new \ZipArchive();
		if (!$zip->open($out_filepath, \ZipArchive::CREATE)) {
			return false;
		}

		if (is_dir($path)) {
			$basedir = "/dp_zip";

			$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);
			foreach ($files as $file) {
				$file = str_replace('\\', '/', realpath($file));

				if (is_dir($file) === true) {
					$local = $basedir . str_replace($path. '/', '', '/' . $file . '/');
					$zip->addEmptyDir($local);
				} elseif (is_file($file) === true && realpath($file) != $out_filepath) {
					$local = $basedir . str_replace($path . '/', '', '/' . $file);
					$zip->addFile(realpath($file), $local);
				}
			}
		} else {
			$zip->addFile($path, '/dp/' . $filename);
		}

		if (!$zip->close()) {
			return false;
		}

		return $out_filepath;
	}

	public function decompressZip($path, $to = null)
	{
		$zip = new \ZipArchive();
		if (!$zip->open($path)) {
			return false;
		}

		$tmpdir = sys_get_temp_dir() . '/' . time() . '-' . mt_rand(1000,9999);
		mkdir($tmpdir);

		if (!$zip->extractTo($tmpdir)) {
			return false;
		}

		$realpath = $tmpdir;
		if (is_dir($tmpdir . '/dp_zip')) {
			$realpath = $tmpdir . '/dp_zip';
		}

		if ($to) {
			$fileutil = new FilesystemUtil();
			$fileutil->mirror($realpath, $to, null, array('override' => true));
			$fileutil->remove($tmpdir);
			return $to;
		}

		return $realpath;
	}
}

class Zip_PclZip implements DpZip
{
	public function __construct()
	{
		require_once(DP_ROOT . '/vendor/pclzip/pclzip.lib.php');
	}

	public function compressFile($path)
	{
		$path = str_replace('\\', '/', $path);

		$dir          = dirname($path);
		$filename     = basename($path);
		$out_filename = $filename . '-' . time() . '-' . mt_rand(1000,9999) . '.zip';
		$out_filepath = sys_get_temp_dir() . '/' . $out_filename;

		$zip = new \PclZip($out_filepath);
		$zip->add(
			$path,
			\PCLZIP_OPT_REMOVE_PATH, $path,
			\PCLZIP_OPT_ADD_PATH, 'dp_zip'
		);

		return $out_filepath;
	}

	public function decompressZip($path, $to = null)
	{
		$zip = new \PclZip($path);

		$tmpdir = sys_get_temp_dir() . '/' . time() . '-' . mt_rand(1000,9999);
		mkdir($tmpdir);

		if (!is_array($zip->extract(\PCLZIP_OPT_PATH, $tmpdir))) {
			return false;
		}

		// Ones we make have dp_zip as the container folder
		$realpath = $tmpdir;
		if (is_dir($tmpdir . '/dp_zip')) {
			$realpath = $tmpdir . '/dp_zip';
		}

		if ($to) {
			$fileutil = new FilesystemUtil();
			$fileutil->mirror($realpath, $to, null, array('override' => true));
			$fileutil->remove($tmpdir);
			return $to;
		}

		return $realpath;
	}
}

########################################################################################################################
# Exception Classes
########################################################################################################################

/**
 * Exception thrown when trying to request a DeskPRO service
 */
class ServiceCallException extends \Exception
{
	/**
	 * An empty response from the server
	 */
	const NO_RESPONSE      = 100;

	/**
	 * An invalid response from the server (invalid JSON).
	 */
	const INVALID_RESPONSE = 200;
}


/**
 * Exception thrown when trying to perform a MySQL backup
 */
class MysqlBackupException extends \Exception
{
	/**
	 * We dont know where mysqldump is
	 */
	const NO_MYSQLDUMP = 100;

	/**
	 * The dump target file already exists
	 */
	const FILE_EXISTS  = 200;

	/**
	 * mysqldump exited with an error status
	 */
	const DUMP_ERROR   = 300;
}


/**
 * Exception thrown when trying to perform a MySQL backup
 */
class MysqlRestoreException extends \Exception
{
	/**
	 * We dont know where mysql is
	 */
	const NO_MYSQL = 100;

	/**
	 * The dump file doesnt exist
	 */
	const BAD_ZIP = 200;

	/**
	 * mysqldump exited with an error status
	 */
	const RESTORE_ERROR   = 300;

	/**
	 * There was a problem trying to extract the zip
	 */
	const EXTRACT_ERROR = 400;
}


/**
 * Exception thrown when trying to perform a file backup
 */
class FileBackupException extends \Exception
{
	/**
	 * The target backup dir exists
	 */
	const FILE_EXISTS = 100;

	/**
	 * There was an error to do with permissions
	 */
	const PERM_ERROR  = 200;
}


/**
 * Exception thrown when trying to download latest distro
 */
class DownloadException extends \Exception
{
	/**
	 * The target file already exists
	 */
	const FILE_EXISTS = 100;

	/**
	 * The target directory to put the distro into doesnt exist
	 */
	const NO_DIR      = 200;

	/**
	 * Couldnt write the distro to the target
	 */
	const PERM_ERROR  = 300;

	/**
	 * The distro appears to be corrupted
	 */
	const BAD_FILE    = 400;
}


/**
 * Exception thrown when trying to upgrade files on the filesystem from a zip
 */
class UpgradeFilesException extends \Exception
{
	/**
	 * The zip doesnt exist or appears to be invalid
	 */
	const BAD_ZIP       = 100;

	/**
	 * There was a problem trying to extract the zip
	 */
	const EXTRACT_ERROR = 200;

	/**
	 * There was a probelm while trying to put the new files in place.
	 * This is a bad error because it means there might be a half-upgraded filesystem.
	 */
	const COPY_ERROR    = 300;
}

/**
 * Exception thrown when trying to upgrade files on the filesystem from a zip
 */
class ZipException extends \Exception
{
	/**
	 * No way to create zips
	 */
	const NO_STRATEGY       = 100;
}

########################################################################################################################
# RUN
########################################################################################################################

$upgrade = new Upgrade();
$upgrade->run($_SERVER['argv']);
