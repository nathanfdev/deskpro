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

namespace Application\DeskPRO\EntityRepository;

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class ChatConversation extends EntityRepository
{
	public function getAgentList($agent, $query_partial = null)
	{
		$qb = $this->createQueryBuilder('c');
		$qb->select('c')
		   ->where('c.is_agent = true AND p.person_id = ?')
		   ->setFirstResult(0)
		   ->setMaxResults(25)
		   ->orderBy('c.id', 'DESC');

		if ($query_partial) {
			$query_partial->applyToQueryBuilder($qb);
		}

		return $qb->getQuery()->execute(array($agent['id']));
	}

	/**
	 * Get a list of IPs suitable for display
	 */
	public function getList()
	{
		$list = App::getDb()->fetchAllCol("
			SELECT banned_ip
			FROM ban_ips
			ORDER BY ip_start ASC
		");

		return $list;
	}
}