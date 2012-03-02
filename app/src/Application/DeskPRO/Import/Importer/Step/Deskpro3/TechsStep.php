<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

class TechsStep extends AbstractDeskpro3Step
{
	/**
	 * @var int[]
	 */
	protected $dep_ids;

	public static function getTitle()
	{
		return 'Import Techs';
	}

	public function run($page = 1)
	{
		$techs = $this->getOldDb()->fetchAllKeyed("SELECT * FROM tech");
		$this->logMessage(sprintf("Importing %d techs", count($techs)));

		$start_time = microtime(true);

		$this->dep_ids = $this->getDb()->fetchAllCol("SELECT id FROM departments");

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

					$name_parts = \Application\DeskPRO\People\Util::guessNameParts($tech['name'], $tech['email']);
					$agent->first_name = $name_parts[0];
					$agent->last_name = $name_parts[1];
				}

				$agent->is_user = true;
				$agent->is_confirmed = true;
				$agent->is_agent_confirmed = true;
				$agent->is_agent = true;

				$this->getEm()->persist($agent);
				$this->getEm()->flush();

				$this->saveMappedId('tech', $tech['id'], $agent->id);

				// Save old password string because its used in gateways
				$this->getDb()->insert('import_datastore', array(
					'typename' => 'dp3_techpass_' . $tech['id'],
					'data' => serialize(array('new_id' => $agent->id, 'old_pass' => $tech['password']))
				));

				#------------------------------
				# Category (department) permissions
				#------------------------------

				// DP3: Cats in cats_admin are ones that are *denied*
				// DP4: Theres an entry in department_permissions for each cat *allowed*

				$deny_cat_ids = explode(',', (string)$tech['cats_admin']);

				foreach ($this->dep_ids as $did) {
					$mapped_id = $this->getMappedOldId('ticket_category', $did);
					if (in_array($mapped_id, $deny_cat_ids)) {
						continue;
					}

					$this->getDb()->insert('department_permissions', array(
						'department_id' => $did,
						'person_id' => $agent->id,
						'app' => 'tickets',
					));

					$this->getDb()->insert('department_permissions', array(
						'department_id' => $did,
						'person_id' => $agent->id,
						'app' => 'chat',
					));
				}
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
