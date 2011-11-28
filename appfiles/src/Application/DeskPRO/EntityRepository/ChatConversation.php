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
use Application\DeskPRO\Entity\Visitor as VisitorEntity;
use Application\DeskPRO\Entity\ChatConversation as ChatConversationEntity;

use Orb\Util\Arrays;
use Doctrine\ORM\EntityRepository;

class ChatConversation extends EntityRepository
{
	public function getOpenChatsForAgent(PersonEntity $person)
	{
		$chats = $this->getEntityManager()->createQuery("
			SELECT c
			FROM DeskPRO:ChatConversation c
			WHERE c.agent = ?1 AND c.status = 'open'
		")->setParameter(1, $person)->execute();

		return $chats;
	}

	/**
	 * Gets an array of agent_id=>num that counts how many chats they have open.
	 *
	 * @return array
	 */
	public function getOpenChatsForAgents()
	{
		$counts = App::getDb()->fetchAllKeyValue("
			SELECT agent_id, COUNT(*) AS cnt
			FROM chat_conversations
			WHERE status = ? AND is_agent = 0
			GROUP BY agent_id
		", array('open'));

		return $counts;
	}

	public function getConversationsForAgent($agent)
	{
		if ($agent == null) {
			$convos = $this->getEntityManager()->createQuery("
				SELECT c
				FROM DeskPRO:ChatConversation c
				WHERE c.status = ?1 AND c.agent IS NULL AND c.is_agent = false
				ORDER BY c.id DESC
			")->setParameter(1, 'open')->execute();
		} else {
			$convos = $this->getEntityManager()->createQuery("
				SELECT c
				FROM DeskPRO:ChatConversation c
				WHERE c.status = ?1 AND c.agent = ?2
				ORDER BY c.id DESC
			")->setParameter(1, 'open')->setParameter(2, $agent)->execute();
		}

		return $convos;
	}

	public function getOpenForAgentAndDepartment($agent, $department)
	{
		$params = array();

		$qb = $this->createQueryBuilder('c');
		$qb->orderBy('c.id', 'DESC');
		$qb->where('c.status = :status');
		$params['status'] = 'open';

		if ($agent) {
			$qb->andWhere('c.agent = :agent');
			$params['agent'] = $agent;
		} else {
			$qb->andWhere('c.agent IS NULL');
		}
		if ($department) {
			$qb->andWhere('c.department = :dep');
			$params['dep'] = $department;
		}

		return $qb->getQuery()->execute($params);
	}


	public function getAgentList($agent)
	{
		$agent_ids = App::getDb()->fetchAllCol("
			SELECT people.id
			FROM chat_conversation_to_person convo
			LEFT JOIN chat_conversation_to_person AS convo2 ON (convo2.conversation_id = convo.conversation_id)
			LEFT JOIN people ON (people.id = convo2.person_id)
			WHERE convo.person_id = {$agent['id']} AND people.is_agent = 1 AND people.id != {$agent['id']}
		");

		return App::getEntityRepository('DeskPRO:Person')->getPeopleFromIds($agent_ids);
	}

	public function getChatsForPeople(array $participant_ids)
	{
		$participant_ids = Arrays::removeFalsey($participant_ids);
		$count = count($participant_ids);

		$person1 = $participant_ids[0];
		$person2 = $participant_ids[1];

		$sql = "
			SELECT convo.conversation_id
			FROM chat_conversation_to_person convo
			LEFT JOIN chat_conversation_to_person AS convo2 ON (convo2.conversation_id = convo.conversation_id)
			LEFT JOIN people ON (people.id = convo2.person_id)
			WHERE convo.person_id = $person1 AND convo2.person_id = $person2
		";

		$conversation_ids = App::getDb()->fetchAllCol($sql);

		if (!$conversation_ids) {
			return null;
		}

		$conversations = $this->getEntityManager()->createQuery("
			SELECT c
			FROM DeskPRO:ChatConversation c
			WHERE c.id IN(" . implode(',', $conversation_ids) . ")
			ORDER BY c.id ASC
		")->execute();

		return $conversations;
	}


	/**
	 * This fetches the latest conversation where all $participants participated, and only
	 * they participated. Usually this is used to find a private conversation between two people
	 * (for agent chats see the ChatController).
	 *
	 * @param array $participant_ids
	 * @param null $date_limit
	 * @return void
	 */
	public function getRecentForPeople(array $participant_ids, $date_limit = null)
	{
		if (count($participant_ids) < 2) {
			throw new \InvalidArgumentException('$participant_ids should be an array of at least two people');
		}

		if ($date_limit !== null AND !($date_limit instanceof \DateTime)) {
			$date_limit = new \DateTime($date_limit);
		}
		if ($date_limit) {
			$date_limit = $date_limit->format('Y-m-d H:m:s');
		}

		array_walk($participant_ids, function(&$item) {
			if ($item instanceof PersonEntity) {
				$item = $item['id'];
			} elseif (!ctype_digit($item)) {
				$item = null;
			}
		});

		$participant_ids = Arrays::removeFalsey($participant_ids);
		$count = count($participant_ids);

		$sql = "
			SELECT c.id
			FROM chat_conversations c
			LEFT JOIN chat_conversation_to_person p ON (p.conversation_id = c.id)
			WHERE
				" . ($date_limit ? "c.date_created > '$date_limit' AND" : '') . "
				p.person_id IN (" . implode(',', $participant_ids) . ")
			GROUP BY c.id
			HAVING COUNT(*) = $count
			ORDER BY c.id DESC
			LIMIT 1
		";

		$conversation_id = App::getDb()->fetchColumn($sql);
		if (!$conversation_id) {
			return null;
		}

		return $this->find($conversation_id);
	}


	public function getActiveChatForVisitor($visitor)
	{
		try {
			$conversation = $this->getEntityManager()->createQuery("
				SELECT c
				FROM DeskPRO:ChatConversation c
				WHERE c.visitor = ?1
				ORDER BY c.id ASC
			")->setParameter(1, $visitor)->setMaxResults(1)->getSingleResult();
		} catch (\Exception $e) {
			$conversation = null;
		}

		return $conversation;
	}

	public function getPastChatsForVisitor($visitor)
	{
		return $this->getEntityManager()->createQuery("
			SELECT c
			FROM DeskPRO:ChatConversation c
			WHERE c.visitor = ?1 AND c.status = 'ended'
			ORDER BY c.id ASC
		")->setParameter(1, $visitor)->execute();
	}

	public function getPastChatsForPerson($person)
	{
		return $this->getEntityManager()->createQuery("
			SELECT c
			FROM DeskPRO:ChatConversation c
			WHERE (c.person = ?1 OR c.person_email = ?2) AND c.status = 'ended'
			ORDER BY c.id ASC
		")->setParameter(1, $person)
		  ->setParameter(2, $person->getPrimaryEmailAddress())
		  ->execute();
	}

	public function getLatestChatForSession($session, $active = true)
	{
		try {
			$conversation = $this->getEntityManager()->createQuery("
				SELECT c
				FROM DeskPRO:ChatConversation c
				WHERE c.session = ?1
				ORDER BY c.id DESC
			")->setParameter(1, $session)->setMaxResults(1)->getSingleResult();
		} catch (\Exception $e) {
			$conversation = null;
		}

		if ($conversation AND ($active AND $conversation['status'] != ChatConversationEntity::STATUS_AWAITING_AGENT)) {
			$conversation = null;
		}

		return $conversation;
	}
}
