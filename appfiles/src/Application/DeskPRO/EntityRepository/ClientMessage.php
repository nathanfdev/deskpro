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

class ClientMessage extends EntityRepository
{
	public function getMessagesForClient($session_id, $since_id = null)
	{
		$channels = App::getEntityRepository('DeskPRO:ClientChannelSubscription')->getSubscriptionsForClient($session_id);
		$names = array();
		$names_like = array();
		foreach ($channels as $ch) {
			$names[] = "'{$ch['channel']}'";
			$names_like[] = "m.channel LIKE '{$ch['channel']}.%'";
		}

		if (!$names) {
			return array();
		}

		$names = implode(',', $names);
		$names_like = implode(' OR ', $names_like);

		$params = array();

		$qb = $this->createQueryBuilder('m');
		$qb->select('m');
		$qb->where('m.channel IN (' . $names . ') OR ('. $names_like . ')');

		if ($since_id) {
			$qb->andWhere('m.id > :since_id');
			$params['since_id'] = $since_id;
		} else {
			$qb->setMaxResults(100);
		}

		$qb->orderBy('m.id', 'asc');

		return $qb->getQuery()->execute($params);
	}
}