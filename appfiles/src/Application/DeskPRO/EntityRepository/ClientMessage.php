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

use Application\DeskPRO\App;

use Doctrine\ORM\EntityRepository;

class ClientMessage extends EntityRepository
{
	/**
	 * Get messages for a client for specific channels
	 * 
	 * @param  $client_id
	 * @param null $person_id
	 * @param array $channels
	 * @param null $since_id
	 * @return array|mixed
	 */
	public function getMessagesForClientInChannels($client_id, $person_id = null, array $channels, $since_id = null)
	{
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

		if ($person_id) {
			$qb->andWhere('m.for_client = :for_client OR m.for_person = :for_person OR (m.for_client IS NULL AND m.for_person IS NULL)');
			$params['for_client'] = $client_id;
			$params['for_person'] = $person_id;
		} else {
			$qb->andWhere('m.for_client = :for_client OR (m.for_client IS NULL AND m.for_person IS NULL)');
			$params['for_client'] = $client_id;
		}

		if ($since_id) {
			$qb->andWhere('m.id > :since_id');
			$params['since_id'] = $since_id;
		} else {
			$qb->setMaxResults(100);
		}

		$qb->orderBy('m.id', 'asc');

		return $qb->getQuery()->execute($params);
	}


	/**
	 * Get messages for a client based on their registered subscriptions
	 *
	 * @param string $client_id
	 * @param int|null $person_id
	 * @param int|null $since_id
	 * @return array
	 */
	public function getMessagesForClient($client_id, $person_id = null, $since_id = null)
	{
		$channels = App::getEntityRepository('DeskPRO:ClientChannelSubscription')->getSubscriptionsForClient($client_id);
		return self::getMessagesForClientInChannels($client_id, $person_id, $channels, $since_id);
	}
}