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

define('DP_ROOT', realpath(__DIR__ . '/../'));
define('DP_WEB_ROOT', realpath(__DIR__ . '/../../'));

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

require DP_ROOT . '/bin/build/inc.php';
require DP_ROOT . '/sys/system.php';

########################################################################################################################

class ServiceCallException extends \Exception
{
	const NO_RESPONSE      = 100;
	const INVALID_RESPONSE = 200;
}

class MysqlBackupException extends \Exception
{
	const NO_MYSQLDUMP = 100;
	const FILE_EXISTS  = 200;
	const DUMP_ERROR   = 300;
}

class FileBackupException extends \Exception
{
	const FILE_EXISTS = 100;
	const PERM_ERROR  = 200;
}

class DownloadException extends \Exception
{
	const FILE_EXISTS = 100;
	const NO_DIR      = 200;
	const PERM_ERROR  = 300;
	const BAD_FILE    = 400;
}

class UpgradeFilesException extends \Exception
{
	const BAD_ZIP       = 100;
	const EXTRACT_ERROR = 200;
	const COPY_ERROR    = 300;
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
		} elseif (in_array('--backup-files', $argv)) {
			$this->runAction_backupFiles();
		} else {
			$this->out("Use --help for a list of possible actions.");
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


	####################################################################################################################
	# help
	####################################################################################################################

	public function runAction_help()
	{
		$this->out("Usage: php upgrade-util.php <action>");
		$this->out('');
		$this->out("Possible actions:");

		$this->out("\t--auto [--verbose]");
		$this->out("\t\tAutomatically checks for a newer version, and if one exists, will attempt to ");
		$this->out("\t\tdownload it, extract it and install it. Backups will be made to the backups directory.");
		$this->out('');
		$this->out("\t\tThis command is meant to be done on a schedule task and is quiet by default");
		$this->out('');

		$this->out("\t--check-version");
		$this->out("\t\tOutputs information about your current version and the latest version of DeskPRO available");
		$this->out('');

		$this->out("\t--backup-db");
		$this->out("\t\tExecutes a mysqldump of your database into the data/backups directory");
		$this->out('');

		$this->out("\t--backup-files");
		$this->out("\t\tBacks up all DeskPRO files. Note: This will NOT back up the data/backups directory.");
		$this->out('');

		$this->out("\t--download-latest [--path <path>]");
		$this->out("\t\tDownloads the latest version of DeskPRO and saves it into the data/backups directory,");
		$this->out("\t\tunless you specify a path with --path.");
		$this->out('');

		$this->out("\t--upgrade-files [--path <zip-path>]");
		$this->out("\t\tExtracts a ZIP and replaces current files with the ones from the ZIP. This does NOT upgrade");
		$this->out("\t\tthe database scheme. You still need to run --upgrade-db after this updates the files.");
		$this->out('');
		$this->out("\t\tIf --path is supplied, the ZIP from --path will be used as the source. Otherwise, the latest");
		$this->out("\t\tsource is downloaded (same as running --download-latest).");
		$this->out('');
		$this->out("\t\tIt is recommended to run --backup-files before running this.");
		$this->out('');
	}

	####################################################################################################################
	# upgrade-files
	####################################################################################################################

	public function runAction_upgradeFiles()
	{
		$zip_path = null;
		$zip_specified = false;
		if (($key = array_search('--path', $this->argv)) === false || !isset($this->argv[$key+1])) {
			$zip_path = @realpath($this->argv[$key+1]);
			if (!file_exists($zip_path)) {
				$this->out("Invalid --path");
				exit(1);
			}

			$zip_specified = true;
		}

		if (!$zip_path) {
			$zip_path = $this->downloadLatest();
			$this->registerCleanupParam('unlink_zip_path', $zip_path);
		}

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
	public function upgradeFiles($zip_path)
	{
		if (!is_file($zip_path)) {
			throw new UpgradeFilesException("Zip path does not exist: $zip_path", UpgradeFilesException::BAD_ZIP);
		}

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

		if (!copy($zip_path, $tmp_dir)) {
			throw new UpgradeFilesException("Failed to copy ZIP to tmp dir: $tmp_dir", UpgradeFilesException::EXTRACT_ERROR);
		}

		#------------------------------
		# Extract the zip into the dir
		#------------------------------

		$ret = $this->execCommand("unzip $zip_name", $tmp_dir, $out);

		if (!$ret) {
			throw new UpgradeFilesException("Failed to extract zip", UpgradeFilesException::EXTRACT_ERROR);
		}

		#------------------------------
		# Now copy everything over
		#------------------------------

		$fileutil = new \Symfony\Component\HttpKernel\Util\Filesystem();

		// Delete old cache dir
		$fileutil->remove(DP_ROOT.'/sys/cache');

		// Copy all files over
		$fileutil->mirror($tmp_dir, DP_WEB_ROOT, null, array(
			'override' => true,
			'copy_on_windows' => true
		));

		$this->registerCleanupParam('unlink_scratch_dir', null);
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
			$this->out("### Backup Error ###");
			$this->out("Command exited with error status: $ret");

			foreach ($out as $l) {
				$this->out("-> " . $l);
			}

			throw new MysqlBackupException("Command exited with error status: $ret", MysqlBackupException::DUMP_ERROR);
		}

		$this->log(sprintf("backupDatabase: time(%.4f)   dump_size(%d)", microtime(true) - $time_start, filesize($f_full)));
		$this->attemptCompress($f_full);

		return true;
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

		$this->attemptCompress($backup_dir);
	}


	####################################################################################################################
	# download-latest
	####################################################################################################################

	public function runAction_downloadLatest()
	{
		$version_info = $this->getLatestVersion();

		$save_path = null;
		if (($key = array_search('--path', $this->argv)) === false || !isset($this->argv[$key+1])) {
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

		if (file_exists($save_path)) {
			throw new DownloadException("Save path already exists: " . $save_dir, DownloadException::FILE_EXISTS);
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

	public function attemptCompress($path)
	{
		$time_start = microtime(true);

		$dir      = dirname($path);
		$filename = basename($path);

		if (is_dir($path)) {
			$strat = $this->getCompressDirStrategy();
		} else {
			$strat = $this->getCompressFileStrategy();
		}

		if ($strat == 'none') {
			return;
		}

		$success = false;
		switch ($strat) {
			case 'gzip':
				$out_filename = $filename . '.gz';
				$cmd = "gzip -c $filename > $out_filename";

				$ret = $this->execCommand($cmd, $dir);
				if (!$ret) {
					$success = true;
				}

				break;

			case 'tar':
				$out_filename = $filename . '.tgz';
				$cmd = "tar -zc $filename > $out_filename";

				$ret = $this->execCommand($cmd, $dir);
				if (!$ret) {
					$success = true;
				}

				break;

			case 'zip':
				$out_filename = $filename . '.zip';
				$cmd = "zip -r $out_filename $filename";

				$ret = $this->execCommand($cmd, $dir);
				if (!$ret) {
					$success = true;
				}

				break;
		}

		$out_filepath = $dir . '/' . $out_filename;

		// Double check the out file too
		if ($success) {
			if (!file_exists($out_filepath) || filesize($out_filepath) < 10) {
				$this->log("attemptCompress: reported success but file looks bad: $out_filepath");
				$success = false;
			}
		}

		// If we're a success, then we can remove the original
		if ($success) {
			$fileutil = new \Symfony\Component\HttpKernel\Util\Filesystem();
			$fileutil->remove($path);

			$this->log("attemptCompress: time(%.4f)   file_size(%d)", microtime(true) - $time_start, filesize($out_filepath));
		}
	}


	/**
	 * @return string
	 */
	public function getCompressDirStrategy()
	{
		static $compress_strategy = null;

		if ($compress_strategy === null) {
			$res = $this->execCommand('tar --help');
			if (!$res) {
				$compress_strategy = 'tar';
			}

			if ($compress_strategy === null) {
				$res = $this->execCommand('zip --help');
				if (!$res) {
					$compress_strategy = 'zip';
				}
			}

			if ($compress_strategy === null) {
				$compress_strategy = 'none';
			}

			$this->log('Directory compression strategy: ' . $compress_strategy);
		}

		return $compress_strategy;
	}


	/**
	 * @return string
	 */
	public function getCompressFileStrategy()
	{
		static $compress_strategy = null;

		if ($compress_strategy === null) {
			$res = $this->execCommand('gzip --help');
			if (!$res) {
				$compress_strategy = 'gzip';
			}

			if ($compress_strategy === null) {
				$res = $this->execCommand('zip --help');
				if (!$res) {
					$compress_strategy = 'zip';
				}
			}

			if ($compress_strategy === null) {
				$compress_strategy = 'none';
			}

			$this->log('File compression strategy: ' . $compress_strategy);
		}

		return $compress_strategy;
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
			if (isset($DP_CONFIG['dir_backups'])) {
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
			if (isset($DP_CONFIG['dir_logs'])) {
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
		$url = \DeskPRO\Kernel\License::getLicServer() . '/' . ltrim($endpoint, '/');
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

function Upgrade_Shutdown_Function()
{
	global $UPGRADE_CLEANUP;
	if (!$UPGRADE_CLEANUP) {
		return;
	}

	if (isset($UPGRADE_CLEANUP['close_log_fh'])) {
		fclose($UPGRADE_CLEANUP['close_log_fh']);
	}
	if (isset($UPGRADE_CLEANUP['unlink_zip_path'])) {
		unlink($UPGRADE_CLEANUP['unlink_zip_path']);
	}
	if (isset($UPGRADE_CLEANUP['unlink_scratch_dir'])) {
		if (is_dir($UPGRADE_CLEANUP['unlink_scratch_dir'])) {
			chdir($UPGRADE_CLEANUP['unlink_scratch_dir']);
			exec('rm -rf ' . dirname($UPGRADE_CLEANUP['unlink_scratch_dir']));
			chdir(DP_START_DIR);
		}
	}

	$UPGRADE_CLEANUP = null;
}

$upgrade = new Upgrade();
$upgrade->run($_SERVER['argv']);