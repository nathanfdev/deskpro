<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

class TechPmsStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Tech Private Messages';
	}

	public function run($page = 1)
	{
		$tech_ids = $this->getOldDb()->fetchAllCol("SELECT id FROM tech");

		$this->getDb()->beginTransaction();

		try {
			foreach ($tech_ids as $tech_id) {
				$this->importTechMessages($tech_id);
			}

			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
			throw $e;
		}
	}

	protected function importTechMessages($tech_id)
	{
		$agent_id = $this->getMappedNewId('tech', $tech_id);
		if (!$agent_id) {
			return;
		}

		$agent = $this->getEm()->find('DeskPRO:Person', $agent_id);

		$messages = $this->getOldDb()->fetchAll("
			SELECT *
			FROM tech_pms
			WHERE fromid = ?
		", array($tech_id));

		if (!$messages) {
			return;
		}

		$this->logMessage(sprintf("-- Importing %d messages for tech %d (agent %d)", count($messages), $tech_id, $agent_id));

		$start_time = microtime(true);

		foreach ($messages as $message) {
			$other_agent_id = $this->getMappedNewId('tech', $message['toid']);
			if (!$other_agent_id) {
				continue;
			}

			$other_agent = $this->getEm()->find('DeskPRO:Person', $other_agent_id);

			$convo = $this->getEm()->getRepository('DeskPRO:ChatConversation')->getRecentForPeople(array($agent_id, $other_agent_id));
			if (!$convo || !$convo->is_agent) {
				$convo = new \Application\DeskPRO\Entity\ChatConversation();
				$convo->is_agent = true;
				$convo->addParticipant($agent);
				$convo->addParticipant($other_agent);
				$this->getEm()->persist($convo);
				$this->getEm()->flush();
			}

			$chat_message = $convo->addNewMessage(
				strip_tags($message['message']),
				$agent
			);

			$this->getEm()->persist($chat_message);
			$this->getEm()->flush();
		}

		$end_time = microtime(true);
		$this->logMessage(sprintf("-- Done. Took %.3f seconds.", $end_time-$start_time));
	}
}
