<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

namespace Application\DeskPRO\EntityRepository;

use Doctrine\ORM\EntityRepository;

/**
 * Class AppInstance
 * @package Application\DeskPRO\EntityRepository
 */
class AppInstance extends EntityRepository
{
	public function getInstanceByName($name)
	{
		return $this->createQueryBuilder('a')
			->select('a')
			->where('a.package = ?0')
			->setParameter(0, $name)
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * @param \Application\DeskPRO\Entity\AppInstance $app
	 * @return array
	 */
	public function getPermissionsForInstance(\Application\DeskPRO\Entity\AppInstance $app)
	{
		$ret = array('usergroup_ids' => array(), 'person_ids' => array());

		if ($app->perm_type != 'set') {
			return $ret;
		}

		$perms = $this->_em->getConnection()->fetchAll("SELECT * FROM app_instance_permissions WHERE app_instance_id = ?", array($app->id));

		foreach ($perms as $p) {
			if ($p['usergroup_id']) {
				$ret['usergroup_ids'][] = (int)$p['usergroup_id'];
			} elseif ($p['person_id']) {
				$ret['person_ids'][] = (int)$p['person_id'];
			}
		}

		return $ret;
	}
}
