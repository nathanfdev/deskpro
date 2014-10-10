<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Monolog\NullLogger;
use Psr\Log\LoggerInterface;

abstract class AbstractBuild
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	protected $container;

	/**
	 * @var bool
	 */
	protected $rerun = false;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;


	/**
	 * @param DeskproContainer $container
	 * @param LoggerInterface  $logger
	 */
	public function __construct(DeskproContainer $container, LoggerInterface $logger = null)
	{
		if ($logger === null) {
			$logger = new NullLogger();
		}

		$this->logger = $logger;

		$this->container = $container;
		$this->init();
	}


	/**
	 * Saves data to the filesystem (into the tmp dir). Will be overwritten if it already exists.
	 *
	 * @param string $tag
	 * @param string $name
	 * @param string|array $data Array data will be json_encoded, string data will be written as-is
	 * @return string|false Filename written when successful, or false if failed to write
	 */
	public function saveUpgradeData($tag, $name, $data, $throw_exception = true)
	{
		if (is_array($data)) {
			$fname = 'updata-' . $tag . '.' . $name . '.json';
			$path = dp_get_tmp_dir() . DIRECTORY_SEPARATOR . $fname;
			$data = json_encode($data);
			if (file_put_contents($path, $data) === false) {
				if ($throw_exception) {
					throw new \RuntimeException("Failed to write upgrade data file to: $path");
				}
				return false;
			}
		} else {
			$fname = 'updata-' . $tag . '.' . $name . '.dat';
			$path = dp_get_tmp_dir() . DIRECTORY_SEPARATOR . $fname;
			$data = (string)$data;
			if (file_put_contents($path, $data) === false) {
				if ($throw_exception) {
					throw new \RuntimeException("Failed to write upgrade data file to: $path");
				}
				return false;
			}
		}

		@chmod($path, 0777);

		return $path;
	}


	/**
	 * Read previously saved upgrade data.
	 *
	 * @param string $tag
	 * @param string $name
	 * @return array|null|string  Array for JSON-encoded array data, string for string data or null if file could not be found
	 */
	public function getUpgradeData($tag, $name)
	{
		$name_part = 'updata-' . $tag . '.' . $name . '.';
		$path_part = dp_get_tmp_dir() . DIRECTORY_SEPARATOR . $name_part;

		if (file_exists($path_part.'json')) {
			$data = file_get_contents($path_part.'json');
			$data = json_decode($data, true);
			return $data;
		} else if (file_exists($path_part.'dat')) {
			$data = file_get_contents($path_part.'dat');
			return $data;
		} else {
			return null;
		}
	}


	/**
	 * Empty hook into the constructor.
	 */
	protected function init() { }


	/**
	 * Run through the upgrade
	 *
	 * @return void
	 */
	abstract public function run();


	/**
	 * Set this build handler to run again.
	 * This allows "pages" to run. The "runcount" (fetch with getStatus('runcount')) will be
	 * incremented automatically.
	 *
	 * @param bool $rerun
	 * @return bool
	 */
	public function setRerun($rerun = true)
	{
		return $this->rerun = (bool)$rerun;
	}


	/**
	 * @return bool
	 */
	public function shouldRerun()
	{
		return $this->rerun;
	}


	/**
	 * Write to output
	 *
	 * @param string $string
	 */
	public function out($string)
	{
		$this->logger->info($string);
	}


	/**
	 * @param $sql
	 */
	public function execMutateSql($sql, $ignore_err = false)
	{
		$sql = preg_replace('#^\s*#m', '', $sql);
		try {
			$this->container->getDb()->exec($sql);
		} catch (\Exception $e) {
			$this->logger->info("SQL: " . $sql);
			$this->logger->error($e->getMessage());
			if (!$ignore_err) {
				throw $e;
			}
		}
	}


	/**
	 * Save status data (ex. steps completed etc)
	 *
	 * @param $key
	 * @param $val
	 */
	public function saveStatus($key, $val)
	{
		$this->container->getDb()->replace('import_datastore', array(
			'typename' => 'up.' . $this->getBuildId() . '.' . $key,
			'data' => $val
		));
	}


	/**
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getStatus($key, $default = null)
	{
		$val = $this->container->getDb()->fetchArray("
			SELECT data
			FROM import_datastore
			WHERE typename = ?
		", array('up.' . $this->getBuildId() . '.' . $key));

		if (!$val) {
			return $default;
		}

		return $val[0];
	}

	public function recompileCustomTemplates()
	{
		$templates = $this->container->getDb()->fetchAll("
			SELECT id, name, template_code
			FROM templates
		");

		$twig = $this->container->get('twig');

		foreach ($templates as $tpl) {
			$name         = $tpl['name'];
			$compile_code = $tpl['template_code'];

			try {
				if (strpos($name, 'DeskPRO:emails_') !== false || strpos($name, 'DeskPRO:custom_emails_') !== false) {
					$proc = new \Application\DeskPRO\Twig\PreProcessor\EmailPreProcessor();
					$compile_code = $proc->process($compile_code, $name);
				}

				$compile_code = preg_replace('#\{%\s*include\s+(.*?)\s*%\}#', '{% include $1 ignore missing %}', $compile_code);
				$compiled = $twig->compileSource($compile_code, $name);

				$this->container->getDb()->update('templates', array(
					'template_compiled' => $compiled,
				), array('id' => $tpl['id']));
			} catch (\Exception $e) {
				@file_put_contents(
					dp_get_backup_dir() . DIRECTORY_SEPARATOR . 'tpl-backup-' . str_replace(':', '_', $tpl['name']),
					$tpl['template_code']
				);
				$this->container->getDb()->delete('templates', array('id' => $tpl['id']));
			}
		}
	}

	public function getDefaultCollation()
	{
		try {
			$collation = \Application\DeskPRO\App::getSetting('core.db_collation');
		} catch (\Exception $e) {
			$collation = null;
		}

		return $collation ?: 'utf8_general_ci';
	}


	/**
	 * @static
	 * @return string
	 */
	public function getBuildId()
	{
		$name = get_class($this);
		$base = \Orb\Util\Util::getBaseClassname($name);

		// Build1293243423 becomes just 1293243423
		$build_id = str_replace('Build', '', $base);

		return $build_id;
	}
}