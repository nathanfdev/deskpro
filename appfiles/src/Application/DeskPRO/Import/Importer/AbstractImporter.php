<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Import\Importer;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Orb\Log\Logger;

/**
 * We have importers for each platform, and each Importer has a number of Steps.
 *
 * - Importer: A specific platform that we're importing stuff from. For example, DeskPRO v3.
 * - Steps: To import objects from the platform into the local DeskPRO is done through one or more steps.
 */
abstract class AbstractImporter
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	protected $container;

	/**
	 * @var \Orb\Util\OptionsArray
	 */
	protected $config;

	/**
	 * @var \Orb\Log\Logger
	 */
	protected $logger;

	public function __construct(DeskproContainer $container, $config, Logger $logger = null)
	{
		if (is_array($config)) {
			$config = new \Orb\Util\OptionsArray($config);
		}

		$this->container = $container;
		$this->config = $config;

		if (!$logger) {
			$logger = new \Application\DeskPRO\Log\Logger();
			$logger->addWriter(new \Orb\Log\Writer\Output());
		}

		$this->logger = $logger;
	}


	/**
	 * Called before initalizing. Use this to check $config to ensure
	 * everything is alright.
	 *
	 * @return array An array of error messages, if any
	 */
	abstract public function validateOptions();


	/**
	 * Called before the first step to initialize anything.
	 */
	abstract public function setupImport();


	/**
	 * Called after all steps are finished to cleanup anything.
	 */
	abstract public function cleanupImport();


	/**
	 * The number of steps this importer has.
	 *
	 * @return int
	 */
	abstract public function countSteps();


	/**
	 * Get a step
	 *
	 * @param int $step
	 * @return \Application\DeskPRO\Import\Importer\Step\AbstractStep
	 */
	abstract public function getStep($step);


	/**
	 * Run a step
	 *
	 * @param $step
	 */
	public function runStep($step)
	{
		$step = $this->getStep($step);
		$step->run();
	}


	/**
	 * @param int $step
	 * @return bool
	 */
	public function hasStep($step)
	{
		return ($step <= $this->countSteps());
	}


	/**
	 * @return \Orb\Log\Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}


	/**
	 * @param $message
	 */
	public function logMessage($message)
	{
		$this->logger->log($message, 'INFO');
	}


	/**
	 * @return string $name Get a value for $name fom config, otherwise get the config object itself
	 * @return \Orb\Util\OptionsArray
	 */
	public function getConfig($name = null)
	{
		if ($name !== null) {
			return $this->config->get($name);
		}

		return $this->config;
	}


	/**
	 * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	public function getContainer()
	{
		return $this->container;
	}


	/**
	 * The ID of the importer
	 *
	 * @return string
	 */
	public function getId()
	{
		$basename = \Orb\Util\Util::getBaseClassname($this);
		return strtolower(str_replace('Importer', '', $basename));
	}


	/**
	 * @return \Application\DeskPRO\DBAL\Connection
	 */
	public function getDb()
	{
		return $this->getContainer()->getDb();
	}


	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEm()
	{
		return $this->getContainer()->getEm();
	}


	/**
	 * Save an ID mapping
	 *
	 * @param $type
	 * @param $old_id
	 * @param $new_id
	 */
	public function saveMappedId($type, $old_id, $new_id)
	{
		$this->container->getDb()->insert('import_map', array(
			'typename' => $type,
			'old_id' => $old_id,
			'new_id' => $new_id
		));
	}


	/**
	 * Get the new Id by looking up the old one
	 *
	 * @param $type
	 * @param $old_id
	 * @return mixed
	 */
	public function getMappedNewId($type, $old_id)
	{
		return $this->container->getDb()->fetchColumn("
			SELECT new_id
			FROM import_map
			WHERE typename = ? AND old_id = ?
		", array($type, $old_id));
	}


	/**
	 * Get the old Id by looking up the new one
	 *
	 * @param $type
	 * @param $old_id
	 * @return mixed
	 */
	public function getMappedOldId($type, $new_id)
	{
		return $this->container->getDb()->fetchColumn("
			SELECT old_id
			FROM import_map
			WHERE typename = ? AND new_id = ?
		", array($type, $new_id));
	}
}
