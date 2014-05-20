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
 * @category Entities
 */

namespace Application\InstallBundle\Data;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Monolog\NullLogger;
use Psr\Log\LoggerInterface;
use Orb\Util\Strings;
use Orb\Util\Util;

class DefaultDataProcessor
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private $container;

	/**
	 * @var string
	 */
	private $root_dir;

	/**
	 * @var array
	 */
	private $data_classes;

	/**
	 * @var array
	 */
	private $data_info;

	/**
	 * @var int|null
	 */
	private $loaded_data_id = null;

	/**
	 * @var
	 */
	private $logger;

	public function __construct(DeskproContainer $container, $root_dir = null)
	{
		if ($root_dir === null){
			$root_dir = DP_ROOT.'/src/Application/InstallBundle/Data/DefaultData';
		}

		$this->container = $container;
		$this->root_dir = $root_dir;

		$this->logger = new NullLogger();
	}


	/**
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * Loads install data if its not already loaded
	 */
	private function initDataInfo()
	{
		if ($this->data_info !== null) return;

		$row = $this->container->getDb()->fetchAssoc("SELECT id, data FROM datastore WHERE name = 'sys.install.default_data' LIMIT 1");
		$data = null;
		if ($row) {
			$data = @unserialize($row['data']);
			$this->loaded_data_id = $row['id'];
		}
		if (!$data) {
			$data = array();
		}

		if (!isset($data['installed'])) {
			$data['installed'] = array();
		}

		$this->data_info = $data;
	}


	/**
	 * Save data info
	 */
	private function flushDataInfo()
	{
		if ($this->loaded_data_id) {
			$this->container->getDb()->update('datastore', array(
				'data' => serialize($this->data_info)
			), array('id' => $this->loaded_data_id));
		} else {
			$this->container->getDb()->insert('datastore', array(
				'name' => 'sys.install.default_data',
				'auth' => Strings::random(15),
				'data' => serialize($this->data_info)
			));
		}
	}


	/**
	 * @param string $classname
	 * @return bool
	 */
	public function isInstalled($classname)
	{
		$this->initDataInfo();
		return in_array($classname, $this->data_info['installed']);
	}


	/**
	 * @return array
	 */
	public function getDataClasses()
	{
		if ($this->data_classes !== null) {
			return $this->data_classes;
		}

		$this->data_classes = array();

		$dir = dir($this->root_dir);
		while (($f = $dir->read()) !== false) {
			if ($f == '.' || $f == '..' || strpos($f, 'Abstract') !== false) continue;
			$classname = 'Application\\InstallBundle\\Data\\DefaultData\\' . str_replace('.php', '', $f);

			if (class_exists($classname)) {
				$this->data_classes[] = $classname;
			}
		}

		usort($this->data_classes, function($a, $b) {
			$pri_a = $a::PRIORITY;
			$pri_b = $b::PRIORITY;

			if ($pri_a == $pri_b) {
				return 0;
			}

			return $pri_a < $pri_b ? -1 : 1;
		});

		return $this->data_classes;
	}


	/**
	 * Installs new data classes that havent been marked as installed yet.
	 *
	 * @param string $specific_class Only run this specific data class
	 */
	public function runInstall($specific_class = null)
	{
		if ($specific_class) {
			$specific_class = strtolower($specific_class);
		}

		foreach ($this->getDataClasses() as $classname) {
			if ($this->isInstalled($classname)) continue;

			if ($specific_class) {
				if (strtolower($classname) != $specific_class && strtolower(Util::getBaseClassname($classname)) != $specific_class) {
					continue;
				}
			}

			$this->logger->info("Running install on " . Util::getBaseClassname($classname));
			$start_time = microtime(true);

			$obj = new $classname($this->container, $this->logger);
			$obj->runInstall();

			$this->data_info['installed'][] = $classname;
			$this->flushDataInfo();

			$this->logger->info(sprintf("... done in %.4fs", microtime(true)-$start_time));
		}
	}


	/**
	 * Installs new data classes that havent been marked as installed yet, or runs sync if it has.
	 *
	 * @param string $specific_class Only run this specific data class
	 */
	public function runSync($specific_class = null)
	{
		if ($specific_class) {
			$specific_class = strtolower($specific_class);
		}

		foreach ($this->getDataClasses() as $classname) {
			if ($specific_class) {
				if (strtolower($classname) != $specific_class && strtolower(Util::getBaseClassname($classname)) != $specific_class) {
					continue;
				}
			}

			if (!$this->isInstalled($classname)) {
				$this->logger->info("Running install via upgrade on " . Util::getBaseClassname($classname));
				$start_time = microtime(true);

				$obj = new $classname($this->container, $this->logger);
				$obj->runInstallViaUpgrade();

				$this->data_info['installed'][] = $classname;
				$this->flushDataInfo();

				$this->logger->info(sprintf("... done in %.4fs", microtime(true)-$start_time));
			} else {
				$this->logger->info("Running sync on " . Util::getBaseClassname($classname));
				$start_time = microtime(true);

				$obj = new $classname($this->container, $this->logger);
				$obj->runSync();

				$this->logger->info(sprintf("... done in %.4fs", microtime(true)-$start_time));
			}
		}
	}


	/**
	 * Resets data classes
	 *
	 * @param string $specific_class Only run this specific data class
	 */
	public function runReset($specific_class = null)
	{
		if ($specific_class) {
			$specific_class = strtolower($specific_class);
		}

		foreach ($this->getDataClasses() as $classname) {
			if ($specific_class) {
				if (strtolower($classname) != $specific_class && strtolower(Util::getBaseClassname($classname)) != $specific_class) {
					continue;
				}
			}

			if (!$this->isInstalled($classname)) {
				$this->logger->info("Running install via upgrade on " . Util::getBaseClassname($classname));
				$start_time = microtime(true);

				$obj = new $classname($this->container, $this->logger);
				$obj->runInstallViaUpgrade();

				$this->data_info['installed'][] = $classname;
				$this->flushDataInfo();

				$this->logger->info(sprintf("... done in %.4fs", microtime(true)-$start_time));
			} else {
				$this->logger->info("Running reset on " . Util::getBaseClassname($classname));
				$start_time = microtime(true);

				$obj = new $classname($this->container, $this->logger);
				$obj->runReset();

				$this->logger->info(sprintf("... done in %.4fs", microtime(true)-$start_time));
			}
		}
	}
}