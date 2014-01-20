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
 */

namespace Application\DeskPRO\Usergroups;

use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\People\UserPermissions\GroupsDbLoader;
use Doctrine\ORM\EntityManager;

class Usergroups
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	protected $em;

    /**
     * @var \Application\DeskPRO\Entity\Usergroup[]
     */
    protected $usergroups;


	/**
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}


	/**
	 * Loads twitter accounts data from the database
	 */
	private function preload()
	{
		if ($this->usergroups !== null) {
			return;
		}

		$this->usergroups = $this->em->getRepository('DeskPRO:Usergroup')->getUserUsergroups();
	}


	/**
	 * Resets this repository so the next time data is requested form it, it will
	 * be queried again.
	 */
	public function reset()
	{
		$this->usergroups = null;
	}


	/**
	 * @param int $id
	 * @return \Application\DeskPRO\Entity\Usergroup
	 */
	public function getById($id)
	{
		$usergroup = $this->em->getRepository('DeskPRO:Usergroup')->get($id);
		return $usergroup;
	}


	/**
	 * @param int $id
	 * @return \Application\DeskPRO\People\UserPermissions\UserPermissions
	 * @throws \InvalidArgumentException
	 */
	public function getPermissionsById($id)
	{
		$usergroup = $this->em->getRepository('DeskPRO:Usergroup')->get($id);
		if (!$usergroup) {
			throw new \InvalidArgumentException("Invalid group id");
		}
		return $this->getPermissions($usergroup);
	}


	/**
	 * @param Usergroup $group
	 * @return \Application\DeskPRO\People\UserPermissions\UserPermissions
	 */
	public function getPermissions(Usergroup $group)
	{
		$db_loader = new GroupsDbLoader(array($group->id), $this->em);
		return $db_loader->getGroupPermissions($group->id);
	}


    /**
     * @return \Application\DeskPRO\Entity\Usergroup[]
     */
    public function getAll()
    {
        $this->preload();

        return $this->usergroups;
    }


	/**
	 * @return int
	 */
	public function count()
	{
		$this->preload();

		return count($this->usergroups);
	}


	/**
	 * @return \Application\DeskPRO\Entity\Usergroup
	 */
	public function createNew()
	{
		return Usergroup::createUsergroup();
	}


	/**
	 * @param string $id
	 * @param bool   $enabled
	 */
	public function setFieldEnabledById($id, $enabled = true)
	{
		$usergroup             = $this->em->find('DeskPRO:Usergroup', $id);
		$usergroup->is_enabled = $enabled;
		$this->em->persist($usergroup);
		$this->em->flush($usergroup);
	}
}