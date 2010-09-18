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

namespace Application\CoreBundle\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\CoreBundle\Entity\UsergroupPropertyPermission;

/**
 * A website visitor when we have no information about them
 */
class PersonGuest extends Person
{
	public function init(array $params)
	{
		parent::init($params);

		$settings = $this->getContainer()->get('deskpro.core.settings');

		$this->id = 0;
		$this->_usergroup_ids = array($settings->get('core.guest_usergroup_id'));
		$this->timezone = $settings->get('core.default_timezone');
	}

	public function getUsergroups()
	{
		if ($this->usergroups->count()) return $this->usergroups;

		$em = $this->getContainer()->get('doctrine.orm.entity_manager');

		$this->usergroups = $em->createQuery('
			SELECT CoreBundle:Usergroup u
			WHERE usergroup_id = ?1
		')->setParameter(1, $this->_usergroup_ids[0])
			->getResult();

		return $this->usergroups;
	}

	/** @PrePersist */
	public function incCreatedAt()
	{
		throw new \BadMethodCallException('A PersonGuest cannot be persisted');
	}
}
