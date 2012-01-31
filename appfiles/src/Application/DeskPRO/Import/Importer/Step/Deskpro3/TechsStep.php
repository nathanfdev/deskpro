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

				$agent = new \Application\DeskPRO\Entity\Person();
				$agent->name = $tech['name'];
				$agent->setEmail($tech['email'], true);
				$agent->setRawPassword($tech['password']);
				$agent->password_scheme = 'deskpro3_tech';
				$agent->salt = $tech['salt'];
				$agent->is_user = true;
				$agent->is_confirmed = true;
				$agent->is_agent_confirmed = true;
				$agent->is_agent = true;
				$agent->can_agent = true;
				$agent->can_admin = (bool)$tech['is_admin'];
				$agent->can_billing = (bool)$tech['is_admin'];
				$agent->can_reports = (bool)$tech['is_admin'];

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
