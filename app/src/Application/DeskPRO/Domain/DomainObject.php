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
	 * @return \Doctrine\ORM\EntityRepository
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
