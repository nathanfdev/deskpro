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
	define('DP_ROOT', realpath(__DIR__ . '/../'));
}

if (!defined('DP_WEB_ROOT')) {
	define('DP_WEB_ROOT', realpath(__DIR__ . '/../../'));
}

@ini_set('memory_limit', -1);
@ini_set('memory_limit', 268435456);
@set_time_limit(0);

require DP_ROOT . '/../config.php';

if (!isset($DP_CONFIG) || !is_array($DP_CONFIG)) {
	$DP_CONFIG = array();
}

if (!isset($DP_CONFIG['db'])) $DP_CONFIG['db'] = array();
if (!isset($DP_CONFIG['db']['host']))      $DP_CONFIG['db']['host']      = DP_DATABASE_HOST;
if (!isset($DP_CONFIG['db']['user']))      $DP_CONFIG['db']['user']      = DP_DATABASE_USER;
if (!isset($DP_CONFIG['db']['password']))  $DP_CONFIG['db']['password']  = DP_DATABASE_PASSWORD;
if (!isset($DP_CONFIG['db']['dbname']))    $DP_CONFIG['db']['dbname']    = DP_DATABASE_NAME;

if (file_exists(DP_ROOT.'/sys/config/build-time.php')) {
	require(DP_ROOT . '/sys/config/build-time.php');
} else {
	echo "Error: /sys/config/build-time.php does not exist. Cannot automatically upgrade.\n";
	exit(1);
}

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

if (!defined('DP_LIC_SERVER')) {
	define('DP_LIC_SERVER', 'http://dev.deskprodev.com/lic/index.php');
}

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

	public function run(array $argv)
	{
		$this->argv = $argv;

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
		} elseif (in_array('--auto', $argv)) {
			$this->runAction_auto();
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
				$this->outAndLog("Backup directory does not exist or is not writable: " . $this->getBackupDir());
				exit(1);
			}
		} catch (\Exception $e) {} // to catch error about log
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

		$is_quiet      = in_array('--quiet', $this->argv);
		$is_error_halt = in_array('--error-halt', $this->argv);

		if (!$this->isInstanceOutdated()) {
			if (!$is_quiet) {
				$this->out("You are all up to date!");
			}
			exit(0);
		}

		$php_path        = $this->getPhpBinaryPath();
		$mysql_dump_path = $this->getMysqldumpBinaryPath();
		$mysql_path      = $this->getMysqlBinaryPath();

		if (!$php_path || !$mysql_path || !$mysql_dump_path) {
			if (!$php_path)        $this->outAndLog("Cannot find path to `php` binary");
			if (!$mysql_dump_path) $this->outAndLog("Cannot find path to `mysqldump` binary");
			if (!$mysql_path)      $this->outAndLog("Cannot find path to `mysql` binary");
			exit(10);
		}

		// Shutdown helpdesk
		$fileutil = new FilesystemUtil();

		if (!$is_quiet) $this->out("Turning helpdesk off");
		$fileutil->touch(DP_ROOT.'/helpdesk-offline.trigger');

		try {
			if (!$is_quiet) $this->out("Doing file backup ...");
			$this->file_backup = $this->backupFiles();
			if (!$is_quiet) $this->out("-> Done");

			if (!$is_quiet) $this->out("Doing database backup ... ");
			$this->db_backup = $this->backupDatabase();
			if (!$is_quiet) $this->out("-> Done");

		} catch (\Exception $e) {
			$fileutil->remove(DP_ROOT.'/helpdesk-offline.trigger');
			$this->out($e->getCode() . ' ' . $e->getMessage());
			$this->logException($e);
			exit(15);
		}

		try {
			if (!$is_quiet) $this->out("Downloading latest source ...");
			$new_source_zip = $this->downloadLatest();
			if (!$is_quiet) $this->out("-> Done");
		} catch (\Exception $e) {
			$this->out($e->getCode() . ' ' . $e->getMessage());
			$this->logException($e);
			exit(20);
		}

		try {
			$this->revert_checkpoint = 'files';

			if (!$is_quiet) $this->out("Installing latest source files ...");
			$this->installFilesFromZip($new_source_zip, false);
			if (!$is_quiet) $this->out("-> Done");
		} catch (\Exception $e) {
			$this->out($e->getCode() . ' ' . $e->getMessage());
			$this->logException($e);

			if (!$is_error_halt) {
				$this->revertAutoUpgrade();
			}
			exit(25);
		}

		$this->revert_checkpoint = 'db';

		if (!$is_quiet) $this->out("Performing database upgrades ...");

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

		if ($ret) {
			$this->outAndLog("Upgrade returned erorr status $ret");
			if ($out) {
				foreach ($out as $l) {
					$this->outAndLog("-> $l");
				}
			}

			if (!$is_error_halt) {
				$this->revertAutoUpgrade();
				$fileutil->touch(DP_ROOT.'/helpdesk-offline.trigger');
			}

			exit(30);
		}

		if (!$is_quiet) $this->out("-> Done");
		$fileutil->remove(DP_ROOT.'/helpdesk-offline.trigger');

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

		$fileutil->remove(DP_ROOT.'/helpdesk-offline.trigger');

		$this->revert_checkpoint = null;
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

		// Eg: DeskPRO.2012-01-01.zip
		$zip_name = basename($zip_path);

		#------------------------------
		# Create a tmp dir to extract new source to
		#------------------------------

		$tmp_dir = sys_get_temp_dir();
		if (!$tmp_dir || !is_writable($tmp_dir)) {
			$tmp_dir = $this->getBackupDir();
		}

		$tmp_dir .= uniqid('dpsource');
		if (!mkdir($tmp_dir, 0777, true)) {
			throw new UpgradeFilesException("Failed to create temp extract dir: $tmp_dir", UpgradeFilesException::EXTRACT_ERROR);
		}
		$this->registerCleanupParam('unlink_scratch_dir', $tmp_dir);

		if (!copy($zip_path, $tmp_dir.'/'.$zip_name)) {
			throw new UpgradeFilesException("Failed to copy ZIP to tmp dir: $tmp_dir", UpgradeFilesException::EXTRACT_ERROR);
		}

		#------------------------------
		# Extract the zip into the dir
		#------------------------------

		$ret = $this->execCommand("unzip -q $zip_name", $tmp_dir, $out);

		if ($ret) {
			throw new UpgradeFilesException("Failed to extract zip", UpgradeFilesException::EXTRACT_ERROR);
		}

		// Delete the zip from the dir so its not copied
		unlink($tmp_dir . '/' . $zip_name);

		#------------------------------
		# Now copy everything over
		#------------------------------

		$fileutil = new FilesystemUtil();
		if ($dry_run) {
			$fileutil->enableDryRun();
		}

		// Delete old cache dir
		$fileutil->remove(DP_ROOT.'/sys/cache');

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

	public function backupDatabase()
	{
		global $DP_CONFIG;

		$time_start = microtime(true);

		$mysql_dump_path = $this->getMysqldumpBinaryPath();
		if (!$mysql_dump_path) {
			throw new MysqlBackupException("Could not find path to `mysqldump` command", MysqlBackupException::NO_MYSQLDUMP);
		}

		$f = "{$DP_CONFIG['db']['dbname']}-" . date('Y-m-d-H-i-s') . '.sql';
		$f_full = $this->getBackupDir() . '/' . $f;

		if (file_exists($f_full)) {
			throw new MysqlBackupException("Target backup file already exists: $f_full", MysqlBackupException::FILE_EXISTS);
		}

		$cmd = $mysql_dump_path . " --opt -Q -h{$DP_CONFIG['db']['host']} -u{$DP_CONFIG['db']['user']} -p{$DP_CONFIG['db']['password']} {$DP_CONFIG['db']['dbname']} > $f";

		$this->out("Backup directory:  {$this->getBackupDir()}");
		$this->out("Backup command:    $cmd");

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
		$code = fread($fh, filesize($f_full));

		if (strpos($code, 'INSERT INTO `worker_jobs`') === false) {
			$this->out("Database dump seems invalid.");
			throw new MysqlBackupException("Database dump seems invalid", MysqlBackupException::DUMP_ERROR);
		}
		fclose($fh);
		unset($code);

		$this->log(sprintf("backupDatabase: time(%.4f)   dump_size(%d)", microtime(true) - $time_start, filesize($f_full)));
		$f_full = $this->compressFile($f_full);

		return $f_full;
	}

	public function getMysqldumpBinaryPath()
	{
		static $mysql_dump_path = null;

		if ($mysql_dump_path === null) {
			global $DP_CONFIG;

			if (!empty($DP_CONFIG['mysqldump_path'])) {
				$mysql_dump_path = $DP_CONFIG['mysqldump_path'];
			}
			if (!$mysql_dump_path) {
				$finder = new \Symfony\Component\Process\ExecutableFinder();
				$finder->addSuffix('');
				$finder->addSuffix('.exe');
				$finder->addSuffix('.bat');
				$finder->addSuffix('.cmd');
				$finder->addSuffix('.com');
				$mysql_dump_path = $finder->find('mysqldump');
			}

			if (!$mysql_dump_path) {
				$mysql_dump_path = false;
			}
		}

		return $mysql_dump_path;
	}

	public function getMysqlBinaryPath()
	{
		static $mysql_path = null;

		if ($mysql_path === null) {
			global $DP_CONFIG;

			if (!empty($DP_CONFIG['mysql_path'])) {
				$mysql_path = $DP_CONFIG['mysql_path'];
			} elseif ($this->getMysqldumpBinaryPath()) {
				$dir = dirname($this->getMysqldumpBinaryPath());
				if (is_file($dir . '/mysql')) {
					$mysql_path = $dir . '/mysql';
				} elseif (is_file($dir . '/mysql.exe')) {
					$mysql_path = $dir . '/mysql.exe';
				}
			}

			if (!$mysql_path) {
				$finder = new \Symfony\Component\Process\ExecutableFinder();
				$finder->addSuffix('');
				$finder->addSuffix('.exe');
				$finder->addSuffix('.bat');
				$finder->addSuffix('.cmd');
				$finder->addSuffix('.com');
				$mysql_path = $finder->find('mysql');
			}

			if (!$mysql_path) {
				$mysql_path = false;
			}
		}

		return $mysql_path;
	}

	public function getPhpBinaryPath()
	{
		static $php_path = null;

		if ($php_path === null) {
			global $DP_CONFIG;

			if (!empty($DP_CONFIG['php_path'])) {
				$php_path = $DP_CONFIG['php_path'];
			}
			if (!$php_path) {
				$finder = new \Symfony\Component\Process\ExecutableFinder();
				$finder->addSuffix('');
				$finder->addSuffix('.exe');
				$finder->addSuffix('.bat');
				$finder->addSuffix('.cmd');
				$finder->addSuffix('.com');
				$php_path = $finder->find('php');
			}

			if (!$php_path) {
				$php_path = false;
			}
		}

		return $php_path;
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

		// Eg: xxxx.sql.zip
		$zip_name = basename($zip_path);

		#------------------------------
		# Create a tmp dir to extract new source to
		#------------------------------

		$tmp_dir = sys_get_temp_dir();
		if (!$tmp_dir || !is_writable($tmp_dir)) {
			$tmp_dir = $this->getBackupDir();
		}

		$tmp_dir .= uniqid('dpsource');
		if (!mkdir($tmp_dir, 0777, true)) {
			throw new UpgradeFilesException("Failed to create temp extract dir: $tmp_dir", MysqlRestoreException::EXTRACT_ERROR);
		}
		$this->registerCleanupParam('unlink_scratch_dir', $tmp_dir);

		if (!copy($zip_path, $tmp_dir.'/'.$zip_name)) {
			throw new UpgradeFilesException("Failed to copy zip to tmp dir: $tmp_dir", MysqlRestoreException::EXTRACT_ERROR);
		}

		#------------------------------
		# Extract the zip into the dir
		#------------------------------

		$ret = $this->execCommand("unzip -q $zip_name", $tmp_dir, $out);

		if ($ret) {
			throw new UpgradeFilesException("Failed to extract zip", MysqlRestoreException::EXTRACT_ERROR);
		}

		// Delete the zip from the dir so its not copied
		unlink($tmp_dir . '/' . $zip_name);

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

		$cmd = "$mysql_path -h{$DP_CONFIG['db']['host']} -u{$DP_CONFIG['db']['user']} -p{$DP_CONFIG['db']['password']} {$DP_CONFIG['db']['dbname']} < $sql_filename";

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

		$backup_dir = $this->getBackupDir() . '/files-' . date('Y-m-d-H-i-s');
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

		return $this->compressFile($backup_dir);
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
			$save_path = $this->getBackupDir();
		}

		if (is_dir($save_path)) {
			$save_path .= '/' . basename($version_info['download']);
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
			throw new DownloadException(sprintf("Saved file seems too small: $save_path is %d bytes", filesize($save_path)), DownloadException::BAD_FILE);
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
			$this->out("Your instance is outdated! You should upgrade.");
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

		$dir      = dirname($path);
		$filename = basename($path);

		if (is_dir($path)) {
			$out_filename = $filename . '.zip';
			$cmd = "zip -r -q $dir/$out_filename * .htaccess";

			$ret = $this->execCommand($cmd, $path);
		} else {
			$out_filename = $filename . '.zip';
			$cmd = "zip -r -q $out_filename $filename";

			$ret = $this->execCommand($cmd, $dir);
		}

		if (!$ret) {
			$success = true;
		}

		$out_filepath = $dir . '/' . $out_filename;

		// Double check the out file too
		if ($success) {
			if (!file_exists($out_filepath) || filesize($out_filepath) < 10) {
				$this->log("compressFile: reported success but file looks bad: $out_filepath");
				$success = false;
			}
		}

		// If we're a success, then we can remove the original
		if ($success) {
			$fileutil = new FilesystemUtil();
			$fileutil->remove($path);

			$this->log(sprintf("compressFile: time(%.4f)   file_size(%d)", microtime(true) - $time_start, filesize($out_filepath)));
		}

		return $out_filename;
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
		static $backup_dir = null;

		if ($backup_dir === null) {
			global $DP_CONFIG;
			if (isset($DP_CONFIG['dir_backups']) && $DP_CONFIG['dir_backups']) {
				$backup_dir = $DP_CONFIG['dir_backups'];
			} else {
				$backup_dir = DP_WEB_ROOT . '/data/backups';
			}
		}

		return $backup_dir;
	}


	/**
	 * @return string
	 */
	public function getLogDir()
	{
		static $log_dir = null;

		if ($log_dir === null) {
			global $DP_CONFIG;
			if (isset($DP_CONFIG['dir_logs']) && $DP_CONFIG['dir_logs']) {
				$log_dir = $DP_CONFIG['dir_logs'];
			} else {
				$log_dir = DP_WEB_ROOT . '/data/logs';
			}
		}

		return $log_dir;
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
		$url = DP_LIC_SERVER . '/' . ltrim($endpoint, '/');
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

		$this->outputFormatter = new \Symfony\Component\Console\Formatter\OutputFormatter(true, array(
			'title' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('white', 'blue', array('bold')),
			'note' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('yellow', null),
			'prompt' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('cyan', 'black')
		));

		$this->dialogHelper    = new \Symfony\Component\Console\Helper\DialogHelper();

		#------------------------------
		# GO
		#------------------------------

		$this->outHeader('DeskPRO Upgrader', true);
		$this->out();
		$this->out();

		$this->out(
			"<info>Welcome to the DeskPRO interactive upgrader. This tool will help you check for updates, backup your"
			." installation and then install updates. If you require assistance at any time, visit our support"
			." portal at http://support.deskpro.com/ or email support@deskpro.com.</info>"
		);

		$this->out();

		$this->outNote(
			"Note: You can execute many of these commands by themselves manually by using command-line"
			." switches. For a list, try running this command: php upgrade.php --help"
		);

		$this->out();
		$this->out();

		#------------------------------
		# Menu
		#------------------------------

		$version_info = $this->upgrade->getLatestVersion();

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
					$this->runAction_downloadChoice();
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
				$this->out("\nBye!");
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

		$this->out("<prompt>Before we install the updates, you should generate back up first. You can back up both your files and your database.</prompt>");

		while(true) {
			$this->out("Do you want to back up your current source files? [Y/n]> ", false);
			$this->answer_backup_files = $this->dialogHelper->askConfirmation($this, '', true);

			$this->out("Do you want to back up your database? [Y/n]> ", false);
			$this->answer_backup_db = $this->dialogHelper->askConfirmation($this, '', true);

			$this->out();
			$this->out("<comment>Backup files:" . ($this->answer_backup_files ? "YES" : "NO") . "</comment>");
			$this->out("<comment>Backup database: " . ($this->answer_backup_files ? "YES" : "NO") . "</comment>");

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
		# Backup files
		#------------------------------

		$this->outHeader("Installing Updates");

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
		}

		#------------------------------
		# Install files
		#------------------------------

		$this->out(sprintf("%-40s", "<info>[*] Installing files ...</info>"), false);

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

		if ($this->answer_backup_files) {
			$this->out(sprintf("%-40s", "<info>[*] Backing up database ...</info>"), false);

			try {
				$this->file_backup = $this->upgrade->backupDatabase();
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

		chdir(DP_ROOT);
		$cmd = "$php_path cmd.php dp:upgrade 2>&1";
		passthru($cmd, $ret);
		chdir(DP_START_DIR);

		if ($ret) {
			$this->outAndLog("Upgrade returned erorr status $ret");
			$this->errorExit("There was a problem installing the database updates");
		}


		$this->outHeader("DONE");

		$this->out("<info>DeskPRO has been upgraded successfully!</info>");
		$this->out();
		$this->out('Bye!');
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

		if ($version >= DP_BUILD_TIME) {
			$this->out("<info>Your database and source file builds correspond. No database upgrades need to be run.</info>");
			$this->out();
			$this->out("Bye!");
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
			$this->out("Bye!");
			exit(0);
		}

		$this->out("<prompt>Before we install the updates, you should generate back up first.</prompt>");

		while(true) {
			$this->out("Do you want to back up your database? [Y/n]> ", false);
			$this->answer_backup_db = $this->dialogHelper->askConfirmation($this, '', true);

			$this->out();
			$this->out("<comment>Backup database: " . ($this->answer_backup_files ? "YES" : "NO") . "</comment>");

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

		if ($this->answer_backup_files) {
			$this->out(sprintf("%-40s", "<info>[*] Backing up database ...</info>"), false);

			try {
				$this->file_backup = $this->upgrade->backupDatabase();
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

		chdir(DP_ROOT);
		$cmd = "$php_path cmd.php dp:upgrade 2>&1";
		passthru($cmd, $ret);
		chdir(DP_START_DIR);

		if ($ret) {
			$this->outAndLog("Upgrade returned erorr status $ret");
			$this->errorExit("There was a problem installing the database updates");
		}


		$this->outHeader("DONE");

		$this->out("<info>DeskPRO has been upgraded successfully!</info>");
		$this->out();
		$this->out('Bye!');
	}

	public function errorExit($message = '')
	{
		if ($message) {
			$this->out("<error>$message</error>");
			$this->out();
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


########################################################################################################################
# RUN
########################################################################################################################

$upgrade = new Upgrade();
$upgrade->run($_SERVER['argv']);
