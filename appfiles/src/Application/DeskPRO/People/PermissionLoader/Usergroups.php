<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People\PermissionLoader;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\UsergroupPropertyPermission;

use Orb\Util\Arrays;

/**
 * Loads general usergroup permissions likes flags and the like.
 */
class Usergroups extends AbstractLoader
{
	protected $perms = array();

	protected function init()
	{
		$properties = App::getOrm()->createQuery('
			SELECT DeskPRO:UsergroupProperty p
			WHERE usergroup_id IN ?1 AND property_type = ?2
		')->setParameter(1, $this->usergroup_ids)
		  ->setParameter(2, UsergroupPropertyPermission::PROPERTY_TYPE)
		  ->getResult();

		$this->perms = UsergroupPropertyPermission::coalescePermissionProperties($properties);
	}



	/**
	 * Get a permission value
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getPermission($name)
	{
		$this->_loadEffectivePermissions();

		if (!isset($this->_effective_permissions[$name])) {
			return null;
		}

		return $this->_effective_permissions[$name];
	}


	/**
	 * Get an array of data we'll serialize
	 *
	 * @return array
	 */
	protected function serializeData()
	{
		return array('perms' => $this->perms);
	}


	/**
	 * Initialize this object with an array of saved data
	 *
	 * @param array $data
	 */
	protected function unserializeData(array $data)
	{
		$this->perms = $data['perms'];
	}
}
