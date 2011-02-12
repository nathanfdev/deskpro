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

class CustomDataTicket extends EntityRepository
{
	public function getMessagesForPrivateId($private_id, $since = null)
	{
		$params = array($private_id);

		$qb = $this->createQueryBuilder('m');
		$qb->select('m');
		$qb->where('m.private_id IS NULL OR m.private_id = ?');

		if ($since) {
			$qb->andWhere('m.date_created > ?');
			$params[] = $since;
		}

		$qb->orderBy('m.id', 'asc');

		return $qb->getQuery()->execute($params);
	}
}