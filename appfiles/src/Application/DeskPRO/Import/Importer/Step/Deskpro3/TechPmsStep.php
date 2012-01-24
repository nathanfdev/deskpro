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

use Application\DeskPRO\Import\Importer\Step\AbstractStep;

class TechPmsStep extends AbstractStep
{
	/**
	 * @var \Application\DeskPRO\Import\Importer\Deskpro3Importer
	 */
	protected $importer;

	public function getTitle()
	{
		return 'Import Tech Private Messages';
	}

	public function run()
	{
		$tech_ids = $this->importer->getOldDb()->fetchAllCol("SELECT id FROM tech");

		$this->importer->getDb()->beginTransaction();

		try {
			foreach ($tech_ids as $tech_id) {
				$this->importTechMessages($tech_id);
			}

			$this->importer->getDb()->commit();
		} catch (\Exception $e) {
			$this->importer->getDb()->rollback();
			throw $e;
		}
	}

	protected function importTechMessages($tech_id)
	{
		$agent_id = $this->importer->getMappedNewId('tech', $tech_id);
		if (!$agent_id) {
			return;
		}

		$agent = $this->importer->getContainer()->getEm()->find('DeskPRO:Person', $agent_id);

		$messages = $this->importer->getOldDb()->fetchAll("
			SELECT *
			FROM tech_pms
			WHERE fromid = ?
		", array($tech_id));

		$this->importer->logMessage(sprintf("-- Importing %d messages for tech %d (agent %d)", count($message), $tech_id, $agent_id));

		$start_time = microtime(true);

		foreach ($messages as $message) {
			$other_agent_id = $this->importer->getMappedNewId('tech', $message['toid']);
			if (!$other_agent_id) {
				continue;
			}

			$other_agent = $this->importer->getContainer()->getEm()->find('DeskPRO:Person', $other_agent_id);

			$convo = $this->importer->getContainer()->getEm()->getRepository('DeskPRO:ChatConversation')->getChatsForPeople(array($agent_id, $other_agent_id));
			if (!$convo) {
				$convo = new \Application\DeskPRO\Entity\ChatConversation();
				$convo->is_agent = true;
				$convo->addParticipant($agent);
				$convo->addParticipant($other_agent);
				$this->importer->getContainer()->getEm()->persist($convo);
				$this->importer->getContainer()->getEm()->flush();
			}

			$chat_message = $conversation->addNewMessage(
				strip_tags($message['message']),
				$agent
			);

			$this->importer->getContainer()->getEm()->persist($chat_message);
			$this->importer->getContainer()->getEm()->flush();
		}

		$end_time = microtime(true);
		$this->importer->logMessage(sprintf("-- Done. Took %.3f seconds.", $end_time-$start_time));
	}
}
