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

namespace Application\DeskPRO\Import\Importer\Step;

use Application\DeskPRO\Import\Importer\AbstractImporter;

abstract class AbstractStep
{
	/**
	 * @var Application\DeskPRO\Import\Importer\AbstractImporter
	 */
	protected $importer;

	/**
	 * @param \Application\DeskPRO\Import\Importer\AbstractImporter $impoter
	 */
	public function __construct(AbstractImporter $impoter)
	{
		$this->importer = $impoter;
	}


	/**
	 * Get a short title for the step
	 * @return string
	 */
	public static function getTitle()
	{
		return get_called_class();
	}


	/**
	 * Actually do the import work
	 */
	abstract public function run($page = 1);


	/**
	 * @return int
	 */
	public function countPages()
	{
		return 1;
	}


	/**
	 * Get the ID for this step
	 *
	 * @return string
	 */
	public function getId()
	{
		$basename = \Orb\Util\Util::getBaseClassname($this);
		$name = strtolower(str_replace('Step', '', $basename));

		return $this->importer->getId() . '_' . $name;
	}


	/**
	 * @return \Orb\Log\Logger
	 */
	public function getLogger()
	{
		return $this->importer->getLogger();
	}


	/**
	 * @param $message
	 */
	public function logMessage($message)
	{
		return $this->importer->logMessage($message);
	}


	/**
	 * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	public function getContainer()
	{
		return $this->importer->getContainer();
	}


	/**
	 * @return \Application\DeskPRO\DBAL\Connection
	 */
	public function getDb()
	{
		return $this->importer->getContainer()->getDb();
	}


	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEm()
	{
		return $this->importer->getContainer()->getEm();
	}


	/**
	 * @param $type
	 * @param $old_id
	 * @param $new_id
	 */
	public function saveMappedId($type, $old_id, $new_id)
	{
		$this->importer->saveMappedId($type, $old_id, $new_id);
	}


	/**
	 * @param $type
	 * @param $old_id
	 * @return mixed
	 */
	public function getMappedNewId($type, $old_id)
	{
		return $this->importer->getMappedNewId($type, $old_id);
	}


	/**
	 * @param $type
	 * @param $old_id
	 * @return mixed
	 */
	public function getMappedOldId($type, $new_id)
	{
		return $this->importer->getMappedOldId($type, $new_id);
	}
}
