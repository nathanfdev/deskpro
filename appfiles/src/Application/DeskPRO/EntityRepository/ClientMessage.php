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

use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\HttpFoundation\Session as HttpSession;

use Doctrine\ORM\EntityRepository;

class ClientMessage extends EntityRepository
{
	/**
	 * Get message data suitable to return
	 * 
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @param \Application\DeskPRO\HttpFoundation\Session $session
	 * @param int $since
	 * @return array
	 */
	public function getMessageData(PersonEntity $person, HttpSession $session, $since = 0, $with_last_since = null)
	{
		// Automatically ping
		// AJAX clients dont send ping manually, it's just part of this call
		$person->loadHelper('ClientChannelSubscriptions', array('session' => $session));
		$person->getClientChannelSubs()->pingSubscriptions();

		$data = array('messages' => array(), 'last_id' => -1);
		$all_messages = false;

		if (!$since) {
			$last_id = App::getDb()->fetchColumn("SELECT id FROM client_messages ORDER BY id DESC LIMIT 1");
			if ($last_id) {
				$data['last_id'] = $last_id;
			}

		} else {
			$all_messages = $this->getMessagesForClient($session->getEntityId(), $person['id'], $since);
		}

		if ($with_last_since) {
			$all_messages = array_merge($all_messages, $this->getInitialMessagesForPerson($person, $with_last_since));
		}

		if ($all_messages) {
			foreach ($all_messages as $message) {
				$handler = $message->getHandler();

				// Mesasge is a numeric array
				// 0 => id
				// 1 => channel
				// 2 => data
				// 3 => (optional) flags

				$info = array(
					$message['id'],
					$message['channel'],
					$handler->getMessage('ajax')
				);

				if ($message['id'] < $since && $with_last_since) {
					$info[] = array(
						'offline_messsage' => true
					);
				}

				$data['messages'][] = $info;

				if ($message['id'] > $data['last_id']) {
					$data['last_id'] = $message['id'];
				}
			}
		}

		if ($data['last_id'] == -1) {
			unset($data['last_id']);
		}

		return $data;
	}

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
			$names[] = "'{$ch}'";
			$names_like[] = "m.channel LIKE '{$ch}.%'";
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

		$qb->andWhere('m.created_by_client != :n_created_by_client');
		$params['n_created_by_client'] = $client_id;

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
	 * Gets the initials messages to send to the client after they load the interface, and request
	 * messages for the first time.
	 *
	 * This is like getMessagesForClientInChannels() except we check specifically for a person,
	 * and $since is an ID from the preference from the last one the user got.
	 *
	 * @param $person_id
	 * @param $since_id
	 * @return array
	 */
	public function getInitialMessagesForPerson($person_id, $since_id = null)
	{
		$qb = $this->createQueryBuilder('m');
		$qb->select('m');
		$qb->andWhere('m.for_person = :for_person');
		$params['for_person'] = $person_id;

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
		$channels_obj = App::getEntityRepository('DeskPRO:ClientChannelSubscription')->getSubscriptionsForClient($client_id);
		$channels = array();
		foreach ($channels_obj as $ch) {
			$channels[] = $ch['channel'];
		}

		return self::getMessagesForClientInChannels($client_id, $person_id, $channels, $since_id);
	}
}