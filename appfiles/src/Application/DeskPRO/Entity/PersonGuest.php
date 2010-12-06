<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\Entity\UsergroupPropertyPermission;

/**
 * A website visitor when we have no information about them
 */
class PersonGuest extends Person
{
	public function __construct()
	{
		$this->id = 0;
		$this->_usergroup_ids = array(App::getSetting('core.guest_usergroup_id'));
		$this->timezone = App::getSetting('core.default_timezone');
	}

	public function getUsergroups()
	{
		if ($this->usergroups->count()) return $this->usergroups;

		$em = App::getOrm();

		$this->usergroups = $em->createQuery('
			SELECT DeskPRO:Usergroup u
			WHERE usergroup_id = ?1
		')->setParameter(1, $this->_usergroup_ids[0])->getResult();

		return $this->usergroups;
	}

	/** @orm:PrePersist */
	public function noPersist()
	{
		throw new \BadMethodCallException('A PersonGuest cannot be persisted');
	}
}
