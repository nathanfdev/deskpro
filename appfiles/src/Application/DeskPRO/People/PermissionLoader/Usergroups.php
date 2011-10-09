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
use Application\DeskPRO\Entity\Permission;

use Orb\Util\Arrays;

/**
 * Loads general usergroup permissions likes flags and the like.
 */
class Usergroups extends AbstractLoader implements \Application\DeskPRO\People\PersonContextInterface
{
	protected $perms = null;

	/**
	 * @var int
	 */
	protected $person_id = 0;

	public function setPersonContext(Person $person)
	{
		$this->person_id = $person->id;
	}

	/**
	 * Get a permission value
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getPermission($name)
	{
		if ($this->perms === null) {
			if ($this->person_id) {
				$perms = App::getOrm()->createQuery('
					SELECT DeskPRO:Permission p
					WHERE usergroup IN ?1 OR person = ?2
				')->setParameter(1, $this->usergroup_ids)
				  ->setParameter(2, $this->person_id)
				  ->getResult();
			} else {
				$perms = App::getOrm()->createQuery('
					SELECT DeskPRO:Permission p
					WHERE usergroup IN ?1
				')->setParameter(1, $this->usergroup_ids)
				  ->getResult();
			}

			$this->perms = Permission::getEffectivePermissions($perms);
		}

		if (!isset($this->perms[$name])) {
			return null;
		}

		return $this->perms[$name];
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
