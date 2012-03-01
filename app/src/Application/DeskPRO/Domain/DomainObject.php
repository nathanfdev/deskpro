<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Domain;

use Application\DeskPRO\App;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;

use Orb\Util\Util;

/**
 * The basic entitiy class
 */
abstract class DomainObject extends BasicDomainObject
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $_em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $_db;


	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getOrm()
	{
		if (!$this->_em) {
			$this->_em = App::getOrm();
		}

		return $this->_em;
	}


	/**
	 * @return \Application\DeskPRO\DBAL\Connection
	 */
	public function getDb()
	{
		if (!$this->_db) {
			$this->_db = App::getDb();
		}

		return $this->_db;
	}


	/**
	 * @return Doctrine\ORM\EntityRepository
	 */
	public static function getRepository()
	{
		$entity = get_called_class();
		$entity = explode('\\', $entity);
		$entity = array_pop($entity);

		$em = App::getOrm();

		return $em->getRepository("DeskPRO:$entity");
	}

	/**
	 * Get the table name for this entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return App::getOrm()->getClassMetadata(get_called_class())->getTableName();
	}

	public static function getEntityName()
	{
		$name = Util::getBaseClassname(get_called_class());
		if (preg_match('#^ApplicationDeskPROEntity(.*?)Proxy$#', $name, $m)) {
			$name = $m[1];
		}

		$name = 'DeskPRO:' . $name;

		return $name;
	}


	/**
	 * Sets the value of a field, and calls the property changed tracker
	 *
	 * @param $field
	 * @param $value
	 * @return void
	 */
	protected function setModelField($field, $value)
	{
		$old = $this->$field;

		// Detect fields that did not change
		if (is_null($value) && is_null($old)) {
			return;
		} elseif (is_scalar($value)) {
			if ($value == $old) {
				return;
			}
		} elseif ($value instanceof \DateTime) {
			if ($old instanceof \DateTime && $value->getTimestamp() == $old->getTimestamp()) {
				return;
			}
		} elseif (is_object($value) && isset($value->id) && is_object($old) && isset($old->id)) {
			if ($value->id == $old->id) {
				return;
			}
		}

		$this->$field = $value;

		$this->_onPropertyChanged($field, $old, $value);
	}
}
