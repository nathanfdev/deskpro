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

use Orb\Util\Arrays;
use Doctrine\ORM\EntityRepository;

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
}