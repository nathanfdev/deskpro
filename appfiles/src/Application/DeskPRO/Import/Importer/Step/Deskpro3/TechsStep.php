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

class TechsStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Techs';
	}

	public function run($page = 1)
	{
		$techs = $this->getOldDb()->fetchAllKeyed("SELECT * FROM tech");
		$this->logMessage(sprintf("Importing %d techs", count($techs)));

		$start_time = microtime(true);

		$this->getDb()->beginTransaction();

		try {
			foreach ($techs as $tech) {

				$check_exist = $this->getMappedNewId('tech', $tech['id']);
				if ($check_exist) {
					$this->getLogger()->log("{$tech['id']} already mapped, skipping", 'DEBUG');
					return;
				}

				// Check if the account already exists (ie from install, or the import was run late)
				$check_exist_email = $this->getDb()->fetchColumn("SELECT person_id FROM people_emails WHERE email = ?", array($tech['email']));
				if ($check_exist_email) {
					$agent = $this->getEm()->find('DeskPRO:Person', $check_exist_email);

				// Import the tech account
				} else {
					$agent = new \Application\DeskPRO\Entity\Person();
					$agent->setEmail($tech['email'], true);
					$agent->setRawPassword($tech['password']);
					$agent->password_scheme = 'deskpro3_tech';
					$agent->salt = $tech['salt'];
					$agent->can_agent = true;
					$agent->can_admin = (bool)$tech['is_admin'];
					$agent->can_billing = (bool)$tech['is_admin'];
					$agent->can_reports = (bool)$tech['is_admin'];
					$agent->name = $tech['name'];
				}

				$agent->is_user = true;
				$agent->is_confirmed = true;
				$agent->is_agent_confirmed = true;
				$agent->is_agent = true;

				$this->getEm()->persist($agent);
				$this->getEm()->flush();

				$this->saveMappedId('tech', $tech['id'], $agent->id);
			}

			$this->getEm()->flush();
			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
			throw $e;
		}

		$end_time = microtime(true);
		$this->logMessage(sprintf("-- Done. Took %.3f seconds.", $end_time-$start_time));
	}
}
