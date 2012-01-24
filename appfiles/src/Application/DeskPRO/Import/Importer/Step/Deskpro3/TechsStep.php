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

class TechsStep extends AbstractStep
{
	/**
	 * @var \Application\DeskPRO\Import\Importer\Deskpro3Importer
	 */
	protected $importer;

	public function getTitle()
	{
		return 'Import Techs';
	}

	public function run()
	{
		$techs = $this->importer->getOldDb()->fetchAllKeyed("SELECT * FROM tech");
		$this->importer->logMessage(sprintf("Importing %d techs", count($techs)));

		$start_time = microtime(true);

		$this->importer->getDb()->beginTransaction();

		try {
			foreach ($techs as $tech) {
				$agent = new \Application\DeskPRO\Entity\Person();
				$agent->name = $tech['name'];
				$agent->setEmail($tech['email'], true);
				$agent->setRawPassword($tech['password']);
				$agent->password_scheme = 'deskpro3';
				$agent->salt = $tech['salt'];
				$agent->is_user = true;
				$agent->is_confirmed = true;
				$agent->is_agent_confirmed = true;
				$agent->is_agent = true;
				$agent->can_agent = true;
				$agent->can_admin = (bool)$tech['is_admin'];
				$agent->can_billing = (bool)$tech['is_admin'];
				$agent->can_reports = (bool)$tech['is_admin'];

				$this->getOrm()->persist($agent);
				$this->getOrm()->flush();

				$this->importer->saveMappedId('tech', $tech['id'], $agent->id);
			}

			$this->importer->getEm()->flush();
			$this->importer->getDb()->commit();
		} catch (\Exception $e) {
			$this->importer->getDb()->rollback();
			throw $e;
		}

		$end_time = microtime(true);
		$this->importer->logMessage(sprintf("-- Done. Took %.3f seconds.", $end_time-$start_time));
	}
}
