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

	/**
	 * @var array
	 */
	protected $cached_maps = null;

	/**
	 * Which maps to cache totally
	 *
	 * @var array
	 */
	protected $cache_map_types = array(
		'ticket_category' => true,
		'ticket_workflow' => true,
		'ticket_priority' => true,
		'company' => true,
		'tech' => true,
		'ticket_def_choice' => true,
		'people_def_choice' => true,
		'usergroup' => true,
		'usergroup_sys' => true,
	);

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
	 * Get a step title
	 *
	 * @param int $step
	 * @return string
	 */
	abstract public function getStepTitle($step);


	/**
	 * Called before a step is run
	 *
	 * @param $step
	 */
	public function preRunStep($step)
	{

	}


	/**
	 * Called after a step is run
	 *
	 * @param $step
	 */
	public function postRunStep($step)
	{

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
		$id = $this->container->getDb()->insert('import_map', array(
			'typename' => $type,
			'old_id' => $old_id,
			'new_id' => $new_id
		));

		if (isset($this->cache_map_types[$type])) {
			if (!isset($this->cached_maps[$type])) {
				$this->cached_maps[$type] = array();
			}
			$this->cached_maps[$type][$old_id] = $new_id;
		}
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
		$cache = false;
		if (isset($this->cache_map_types[$type])) {
			if (!$this->cached_maps) {
				$data = $this->getDb()->fetchAll("
					SELECT typename, old_id, new_id
					FROM import_map
					WHERE typename IN ('" . implode("','", array_keys($this->cache_map_types)) . "')
				");
				$this->cached_maps = array();
				foreach ($data as $d) {
					if (!isset($this->cached_maps[$d['typename']])) {
						$this->cached_maps[$d['typename']] = array();
					}
					$this->cached_maps[$d['typename']][$d['old_id']] = $d['new_id'];
				}
			}
			$cache = true;
			if (isset($this->cached_maps[$type]) && array_key_exists($old_id, $this->cached_maps[$type])) {
				return $this->cached_maps[$type][$old_id];
			}
		}

		$id = $this->container->getDb()->fetchColumn("
			SELECT new_id
			FROM import_map
			WHERE typename = ? AND old_id = ?
		", array($type, $old_id));

		if ($cache) {
			$this->cached_maps[$type][$old_id] = $id;
		}

		return $id;
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
