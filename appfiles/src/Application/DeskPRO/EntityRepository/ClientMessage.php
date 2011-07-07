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
	public function getMessageData(PersonEntity $person, HttpSession $session, $since = 0)
	{
		// Automatically ping
		// AJAX clients dont send ping manually, it's just part of this call
		$person->loadHelper('ClientChannelSubscriptions', array('session' => $session));
		$person->getClientChannelSubs()->pingSubscriptions();

		// if $since is 0, the client is new and asking for us to send it the last id
		if (!$since) {
			$data = array('messages' => array(), 'last_id' => -1);
			$last_id = App::getDb()->fetchColumn("SELECT id FROM client_messages ORDER BY id DESC LIMIT 1");
			if ($last_id) {
				$data['last_id'] = $last_id;
			}
			
		} else {

			$data = array();
			if ($since) {
				$data = array('messages' => array(), 'last_id' => -1);

				$all_messages = App::getEntityRepository('DeskPRO:ClientMessage')->getMessagesForClient($session->getEntityId(), $person['id'], $since);
				foreach ($all_messages as $message) {
					$handler = $message->getHandler();

					if ($message['created_by_client'] != $session->getEntityId()) {
						$data['messages'][] = array(
							$message['id'],
							$message['channel'],
							$handler->getMessage('ajax')
						);
					}

					if ($message['id'] > $data['last_id']) {
						$data['last_id'] = $message['id'];
					}
				}

				if ($data['last_id'] == -1) {
					unset($data['last_id']);
				}
			}
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
		$channels_obj = App::getEntityRepository('DeskPRO:ClientChannelSubscription')->getSubscriptionsForClient($client_id);
		$channels = array();
		foreach ($channels_obj as $ch) {
			$channels[] = $ch['channel'];
		}

		return self::getMessagesForClientInChannels($client_id, $person_id, $channels, $since_id);
	}
}