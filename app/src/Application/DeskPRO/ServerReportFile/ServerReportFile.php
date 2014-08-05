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
 */

namespace Application\DeskPRO\ServerReportFile;

use Application\DeskPRO\App;
use Application\DeskPRO\ORM\Util\Util;
use Application\DeskPRO\Service\ErrorReporter;
use DeskPRO\Kernel\License;
use Doctrine\ORM\EntityManager;
use Orb\Util\Strings;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class ServerReportFile
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var string
	 */
	protected $tmpdir = '';

	/**
	 * @var string
	 */
	protected $file_name = 'deskpro-report.zip';

	/**
	 * @var string
	 */
	protected $archive_file = '';

	/**
	 * @var array - this is mapping array between file name and method of this class that creates file content
	 */
	protected $files_added_to_archive = array(
		'phpinfo-web.html'      => '_createPhpInfoFile',
		'phpinfo-cli.txt'       => '_createCliInfoFile',
		'errorlog-deskpro.txt'  => '_createDeskPROErrorLog',
		'errorlog-web.txt'      => '_createWebErrorLog',
		'errorlog-cli.txt'      => '_createCliErrorLog',
		'mysql-schema.sql'      => '_createMysqlSchema',
		'mysql-status.txt'      => '_createMysqlStatus',
		'mysql-vars.txt'        => '_createMysqlVariables',
		'misc.txt'              => '_createMisc',
		'mysql-schema-diff.sql' => '_createMysqlSchemaDiff',
		'cron-status.txt'       => '_createCronStatus',
		'license.txt'           => '_createLicense',
		'file-integrity.txt'    => '_createFileIntegrity',
		'templates.txt'         => '_createTemplates',
	);

	/**
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;

		$this->tmpdir = dp_get_tmp_dir() . DIRECTORY_SEPARATOR . uniqid('dpd', true);

		if (!mkdir($this->tmpdir, 0777, true)) {

			die("Could not create temp dir: " . $this->tmpdir);
		}

		$this->archive_file = $this->tmpdir . '/deskpro-report.zip';
	}

	/**
	 * Saves results of integrity file checks in some temporary space
	 * Later it will be used when generating resulting archive including report information
	 *
	 * @param string $file_check_results - string with results of integrity file checks
	 */
	public function saveFileCheckResults($file_check_results)
	{
		try {

			$this->_createFile(dp_get_tmp_dir() . DIRECTORY_SEPARATOR . 'file_check_results.txt', $file_check_results);

		} catch(IOException $e) {

		}
	}

	/**
	 * Actually outputs the archive as downloadable attachment
	 */
	public function outputArchive()
	{
		header('Content-Type: application/zip; filename=' . $this->file_name);
		header('Content-Length: ' . filesize($this->archive_file));
		header('Content-Disposition: attachment; filename=' . $this->file_name);

		$fp = fopen($this->archive_file, 'r');

		while (!feof($fp)) {

			echo fread($fp, 1024);
		}

		fclose($fp);

		unlink($this->archive_file);

		$fs = new Filesystem();
		$fs->remove($this->tmpdir);

		exit;
	}

	/**
	 * Creates archive with all needed files inside it
	 */
	public function createArchive()
	{
		$this->_addFilesToArchive();

		require_once(DP_ROOT . '/vendor-src/pclzip/pclzip.lib.php');

		$archive = new \PclZip($this->archive_file);

		$list = $archive->add(
			$this->tmpdir,
			\PCLZIP_OPT_REMOVE_ALL_PATH
		);

		if ($list == 0) {

			die("Error : " . $archive->errorInfo(true));
		}
	}

	/**
	 * This methods iterates over all of the $this->files_added_to_archive and creates all the needed files
	 */
	protected function _addFilesToArchive()
	{
		foreach($this->files_added_to_archive as $file_name => $func) {

			$this->$func($file_name);
		}
	}

	/**
	 * @param string $file_name
	 */
	protected function _createPhpInfoFile($file_name)
	{
		/**
		 * @var \Application\DeskPRO\ServerPhpInfo\ServerPhpInfo $service
		 */

		$service = App::getSystemService('server_php_info');
		$info    = $service->getPhpInfo(true);

		try {

			$this->_createFile($this->tmpdir . '/' . $file_name, $info['web_php']['phpinfo']);

		} catch(IOException $e) {

			echo $e->getMessage();
		}
	}

	/**
	 * @param string $file_name
	 */
	protected function _createCliInfoFile($file_name)
	{
		/**
		 * @var \Application\DeskPRO\ServerPhpInfo\ServerPhpInfo $service
		 */

		$service = App::getSystemService('server_php_info');
		$info    = $service->getPhpInfo(true);

		try {

			$this->_createFile($this->tmpdir . '/' . $file_name, $info['cli_php']['phpinfo']);

		} catch(IOException $e) {

			echo $e->getMessage();
		}
	}

	/**
	 * @param string $file_name
	 */
	protected function _createDeskPROErrorLog($file_name)
	{
		$file = str_repeat('#', 72) . "\n# error.log\n" . str_repeat('#', 72) . "\n\n";

		try {

			$file .= $this->_readFile(dp_get_log_dir() . '/error.log');

		} catch(IOException $e) {

			$file = '';
		}

		try {

			$this->_createFile($this->tmpdir . '/' . $file_name, $file);

		} catch(IOException $e) {

			echo $e->getMessage();
		}
	}

	/**
	 * @param string $file_name
	 */
	protected function _createWebErrorLog($file_name)
	{
		$file = str_repeat('#', 72) . "# server-phperr-web.log\n" . str_repeat('#', 72) . "\n\n";

		$log_file_path = @ini_get('error_log');

		if (!$log_file_path) {

			$log_file_path = dp_get_log_dir() . '/server-phperr-web.log';
		}

		try {

			$file .= $this->_readFile($log_file_path);

		} catch(IOException $e) {

			$file = '';
		}

		try {

			$this->_createFile($this->tmpdir . '/' . $file_name, $file);

		} catch(IOException $e) {

			echo $e->getMessage();
		}
	}

	/**
	 * @param string $file_name
	 */
	protected function _createCliErrorLog($file_name)
	{
		$file = str_repeat('#', 72) . "# cli-phperr.log\n" . str_repeat('#', 72) . "\n\n";

		try {

			$file .= $this->_readFile(dp_get_log_dir() . '/cli-phperr.log');

		} catch(IOException $e) {

			$file = '';
		}

		try {

			$this->_createFile($this->tmpdir . '/' . $file_name, $file);

		} catch(IOException $e) {

			echo $e->getMessage();
		}
	}

	/**
	 * @param string $file_name
	 */
	protected function _createMysqlSchema($file_name)
	{
		$sql = array();

		$sql[] = '### ' . App::getContainer()->getSetting('core.deskpro_url') . "\n";
		$sql[] = '### DeskPRO Build: ' . DP_BUILD_TIME . "\n";
		$sql[] = '### Generated: ' . date('Y-m-d H:i:s') . "\n\n";

		$tables = App::getDb()->fetchAllCol("SHOW TABLES");

		foreach ($tables as $table) {

			$sql[] = "### TABLE: $table\n";
			$sql[] = App::getDb()->fetchColumn("SHOW CREATE TABLE `$table`", array(), 1);
			$sql[] = "\n\n";
		}

		$sql = implode('', $sql);

		try {

			$this->_createFile($this->tmpdir . '/' . $file_name, $sql);

		} catch(IOException $e) {

			echo $e->getMessage();
		}
	}

	/**
	 * @param string $file_name
	 */
	protected function _createMysqlStatus($file_name)
	{
		$sections = array();

		try {
			$mysqlstatus              = App::getDb()->fetchAllKeyValue("SHOW STATUS", array(), 0, 1);
			$sections['MySQL Status'] = Strings::keyValueAsciiTable($mysqlstatus);

		} catch(\Exception $e) {

		}

		$out = '';

		foreach ($sections as $title => $content) {

			$out .= "\n\n\n\n\n";
			$out .= str_repeat('#', 80) . "\n";
			$out .= '# ' . str_pad($title, 76) . ' #' . "\n";
			$out .= str_repeat('#', 80) . "\n";
			$out .= $content;
		}

		$out = trim($out);

		try {

			$this->_createFile($this->tmpdir . '/' . $file_name, $out);

		} catch(IOException $e) {

			echo $e->getMessage();
		}
	}

	/**
	 * @param string $file_name
	 */
	protected function _createMysqlVariables($file_name)
	{
		$sections = array();

		try {
			$mysqlinfo                   = App::getDb()->fetchAllKeyValue("SHOW VARIABLES", array(), 0, 1);
			$sections['MySQL Variables'] = Strings::keyValueAsciiTable($mysqlinfo);

		} catch(\Exception $e) {

		}

		$out = '';

		foreach ($sections as $title => $content) {

			$out .= "\n\n\n\n\n";
			$out .= str_repeat('#', 80) . "\n";
			$out .= '# ' . str_pad($title, 76) . ' #' . "\n";
			$out .= str_repeat('#', 80) . "\n";
			$out .= $content;
		}

		$out = trim($out);

		try {

			$this->_createFile($this->tmpdir . '/' . $file_name, $out);

		} catch(IOException $e) {

			echo $e->getMessage();
		}
	}

	/**
	 * @param string $file_name
	 */
	protected function _createMisc($file_name)
	{
		/**
		 * @var \Application\DeskPRO\ServerPhpInfo\ServerPhpInfo $service
		 */

		$service = App::getSystemService('server_php_info');
		$vars    = $service->getPhpInfo(true);

		$items                         = array_merge($vars['binary_paths'], $vars['web_php']['php_config']);
		$items['ini_path']             = $vars['web_php']['ini_path'];
		$items['effective_max_upload'] = $vars['web_php']['effective_max_upload'];
		$items['cli_ini_path']         = isset($vars['cli_php']['ini_path']) ? $vars['cli_php']['ini_path'] : '';

		if (isset($vars['cli_php']['php_config'])) {

			foreach ($vars['cli_php']['php_config'] as $k => $v) {

				if (is_array($v)) {

					foreach ($v as $subk => $subv) {

						$items['cli_' . $k . '.' . $subk] = $subv;
					}
				} else {

					$items['cli_' . $k] = $v;
				}
			}
		}
		$items['has_apc']      = $vars['has_apc'] ? 'Yes' : 'No';
		$items['has_wincache'] = $vars['has_wincache'] ? 'Yes' : 'No';
		$items                 = array_merge($items, $vars['debug_settings']);

		$sections = array();

		$sections['Info']          = Strings::keyValueAsciiTable($items);
		$sections['Reporter Info'] = Strings::keyValueAsciiTable(ErrorReporter::getBasicData(true));

		$out = '';

		foreach ($sections as $title => $content) {

			$out .= "\n\n\n\n\n";
			$out .= str_repeat('#', 80) . "\n";
			$out .= '# ' . str_pad($title, 76) . ' #' . "\n";
			$out .= str_repeat('#', 80) . "\n";
			$out .= "\n\n";
			$out .= $content;
		}

		$out = trim($out);

		try {

			$this->_createFile($this->tmpdir . '/' . $file_name, $out);

		} catch(IOException $e) {

			echo $e->getMessage();
		}
	}

	protected function _createTemplates()
	{
		$templates = App::getDb()->fetchAll("SELECT name, template_code, date_created, date_updated FROM templates");
		$out = array();

		foreach ($templates as $t) {
			$out[] = ">>>>>>>>>>>>>>>>>>>> Template: {$t['name']} -- Created: {$t['date_created']} -- Updated: {$t['date_updated']} <<<<<<<<<<<<<<<<<<<<\n\n";
			$out[] = $t['template_code'];
			$out[] = "\n\n\n\n\n";
		}

		$out = trim(implode('', $out));

		try {
			$this->_createFile($this->tmpdir . '/' . 'templates.txt', $out);
		} catch(IOException $e) {
			echo $e->getMessage();
		}
	}

	/**
	 * @param string $file_name
	 */
	protected function _createMysqlSchemaDiff($file_name)
	{
		try {

			$schemadiff = Util::getUpdateSchemaSql();

			if ($schemadiff) {

				$schemadiff = implode(";\n", $schemadiff) . ";";
			}

		} catch(\Exception $e) {

			$schemadiff = null;
		}

		try {

			$this->_createFile($this->tmpdir . '/' . $file_name, $schemadiff);

		} catch(IOException $e) {

			echo $e->getMessage();
		}
	}

	/**
	 * @param string $file_name
	 */
	protected function _createCronStatus($file_name)
	{
		/**
		 * @var \Application\DeskPRO\ServerCron\ServerCron $service
		 */

		$content = '';

		$content .=  '### ' . App::getContainer()->getSetting('core.deskpro_url') . "\n";
		$content .=  '### DeskPRO Build: ' . DP_BUILD_TIME . "\n";
		$content .=  '### Generated: ' . date('Y-m-d H:i:s') . "\n\n";

		$content .= 'Task    Interval   Last Run   Last Complete   Next Run' . "\n\n";

		$service = App::getSystemService('server_cron');
		$vars    = $service->getAllForApi();

		foreach($vars as $job) {

			$content .= $job['title'] . '       ' . $job['interval_readable'] . '       ';

			$last_start_date = date('D, jS M Y g:ia', $job['last_start_date_ts']) ?
				date('D, jS M Y g:ia', $job['last_start_date_ts']) : 'N/A';
			$content .= $last_start_date . '       ';

			$last_run_date = date('D, jS M Y g:ia', $job['last_run_date_ts']) ?
							date('D, jS M Y g:ia', $job['last_run_date_ts']) : 'N/A';
			$content .= $last_run_date . '       ';

			$content .= $job['next_run_time'];
			$content .= "\n";
		}

		try {

			$this->_createFile($this->tmpdir . '/' . $file_name, $content);

		} catch(IOException $e) {

			echo $e->getMessage();
		}
	}

	/**
	 * @param string $file_name
	 */
	protected function _createLicense($file_name)
	{
		$license = License::getLicense();

		$licenseExpiresIn = function () use ($license) {

			if ($license->getExpireDays() == 0 && $license->getExpireTime('hours') == 0) {

				return 'In ' . $license->getExpireTime('mins') . ' minutes';

			} elseif ($license->getExpireDays() < 3) {

				return 'In ' . $license->getExpireTime('hours') . ' hours';

			} else {

				return 'In ' . $license->getExpireDays() . ' days';
			}
		};

		$content = '';

		$content .= '### ' . App::getContainer()->getSetting('core.deskpro_url') . "\n";
		$content .= '### DeskPRO Build: ' . DP_BUILD_TIME . "\n";
		$content .= '### Generated: ' . date('Y-m-d H:i:s') . "\n\n";

		$content .= 'License ID: ' . $license->getLicenseId() . "\n";
		$content .= 'Agents: ' . $license->getMaxAgents() . "\n";
		$content .= 'Expires: ' . $licenseExpiresIn() . "\n\n\n";

		$content .= 'Core.license: ' . App::getSetting('core.license') . "\n\n\n";
		$content .= 'Core.install_key: ' . App::getSetting('core.install_key') . "\n\n";
		$content .= 'Core.licenseopt: ' . App::getSetting('core.licenseopt') . "\n\n";

		try {

			$this->_createFile($this->tmpdir . '/' . $file_name, $content);

		} catch(IOException $e) {

			echo $e->getMessage();
		}
	}

	/**
	 * @param string $file_name
	 */
	protected function _createFileIntegrity($file_name)
	{
		$fs = new Filesystem();

		if (file_exists(dp_get_tmp_dir() . DIRECTORY_SEPARATOR . 'file_check_results.txt')) {

			try {

				$fs->copy(
					dp_get_tmp_dir() . DIRECTORY_SEPARATOR . 'file_check_results.txt',
					$this->tmpdir . '/' . $file_name
				);

			} catch(IOException $e) {

				die(
					"Could not create File Integrity file under this location - " . $this->tmpdir . '/' . $file_name .
						". More info:" . $e->getMessage()
				);
			}
		}
	}

	/**
	 * Attempts to create file in specified location with specified content
	 * Also acts as wrapper for throwing an exception in case of fail
	 *
	 * @param string $file_name
	 * @param string $content
	 *
	 * @throws \Symfony\Component\Filesystem\Exception\IOException
	 */
	protected function _createFile($file_name, $content)
	{
		if (@file_put_contents($file_name, $content) === false) {

			throw new IOException('Could not create file under location - ' . $file_name);
		}
	}

	/**
	 * Attempts to read a file from specified location
	 * Also acts as wrapper for throwing an exception in case of fail
	 *
	 * @param string $file_name
	 * @return string
	 *
	 * @throws \Symfony\Component\Filesystem\Exception\IOException
	 */
	protected function _readFile($file_name)
	{
		if(($content = @file_get_contents($file_name)) === false) {

			throw new IOException('Could not read file under location - ' . $file_name);
		}

		return $content;
	}
}