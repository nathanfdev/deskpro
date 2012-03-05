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

		$scanner = new \Application\InstallBundle\Data\AgentGroupPermScanner();
		$perms = $scanner->getNames();

		$has_admin = false;

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

				if ($agent->can_admin && !$has_admin) {
					$has_admin = true;
					$this->saveMappedId('first_admin', 0, $agent->id);
				}

				// Save old password string because its used in gateways
				$this->getDb()->insert('import_datastore', array(
					'typename' => 'dp3_techpass_' . $tech['id'],
					'data' => serialize(array('new_id' => $agent->id, 'old_pass' => $tech['password']))
				));

				#------------------------------
				# Copy Permissions
				#------------------------------

				// Admins just have everything
				if ($agent->can_admin) {
					$this->getDb()->insert('person2usergroups', array(
						'person_id' => $agent->id,
						'usergroup_id' => 2
					));

				// Otherwise we'll import perms into overrides
				} else {
					$insert_perms = array();
					foreach ($perms as $n) {
						$insert_perms[$n] = 1;
					}

					//-----
					// Tickets (Own)
					//-----

					if (!$tech['p_delete_own']) {
						unset($tech['agent_tickets.delete_own']);
					}
					if (!$tech['p_start_ticket']) {
						unset($tech['agent_tickets.create']);
					}

					if (!$tech['p_close_ticket']) {
						unset($tech['agent_tickets.modify_set_resolved_own']);
						unset($tech['agent_tickets.modify_set_resolved_unassigned']);
						unset($tech['agent_tickets.modify_set_resolved_others']);
					}

					if (!$tech['p_merge_ticket']) {
						unset($tech['agent_tickets.modify_set_merge_own']);
						unset($tech['agent_tickets.modify_set_merge_unassigned']);
						unset($tech['agent_tickets.modify_set_merge_others']);
					}


					//-----
					// Tickets (Unassigned)
					//-----

					if (!$tech['p_unassigned_view']) {
						unset(
							$insert_perms['agent_tickets.view_unassigned'],
							$insert_perms['agent_tickets.reply_unassigned'],
							$insert_perms['agent_tickets.modify_unassigned'],
							$insert_perms['agent_tickets.modify_department_unassigned'],
							$insert_perms['agent_tickets.modify_fields_unassigned'],
							$insert_perms['agent_tickets.modify_assign_agent_unassigned'],
							$insert_perms['agent_tickets.modify_assign_team_unassigned'],
							$insert_perms['agent_tickets.modify_assign_self_unassigned'],
							$insert_perms['agent_tickets.modify_cc_unassigned'],
							$insert_perms['agent_tickets.modify_merge_unassigned'],
							$insert_perms['agent_tickets.modify_labels_unassigned'],
							$insert_perms['agent_tickets.modify_notes_unassigned'],
							$insert_perms['agent_tickets.modify_set_hold_unassigned'],
							$insert_perms['agent_tickets.modify_set_awaiting_user_unassigned'],
							$insert_perms['agent_tickets.modify_set_awaiting_agent_unassigned'],
							$insert_perms['agent_tickets.modify_set_resolved_unassigned'],
							$insert_perms['agent_tickets.delete_unassigned']
						);
					}

					//-----
					// Tickets (Others)
					//-----

					if (!$tech['p_tech_view']) {
						unset(
							$insert_perms['agent_tickets.view_others'],
							$insert_perms['agent_tickets.reply_others'],
							$insert_perms['agent_tickets.modify_others'],
							$insert_perms['agent_tickets.modify_department_others'],
							$insert_perms['agent_tickets.modify_fields_others'],
							$insert_perms['agent_tickets.modify_assign_agent_others'],
							$insert_perms['agent_tickets.modify_assign_team_others'],
							$insert_perms['agent_tickets.modify_assign_self_others'],
							$insert_perms['agent_tickets.modify_cc_others'],
							$insert_perms['agent_tickets.modify_merge_others'],
							$insert_perms['agent_tickets.modify_labels_others'],
							$insert_perms['agent_tickets.modify_notes_others'],
							$insert_perms['agent_tickets.modify_set_hold_others'],
							$insert_perms['agent_tickets.modify_set_awaiting_user_others'],
							$insert_perms['agent_tickets.modify_set_awaiting_agent_others'],
							$insert_perms['agent_tickets.modify_set_resolved_others'],
							$insert_perms['agent_tickets.delete_others']
						);
					} else {
						if (!$tech['p_delete_other']) {
							unset($insert_perms['agent_tickets.delete_others']);
						}
						if (!$tech['p_tech_reply']) {
							unset($insert_perms['agent_tickets.reply_others']);
						}
						if (!$tech['p_tech_edit']) {
							unset(
								$insert_perms['agent_tickets.modify_others'],
								$insert_perms['agent_tickets.modify_department_others'],
								$insert_perms['agent_tickets.modify_fields_others'],
								$insert_perms['agent_tickets.modify_assign_agent_others'],
								$insert_perms['agent_tickets.modify_assign_team_others'],
								$insert_perms['agent_tickets.modify_assign_self_others'],
								$insert_perms['agent_tickets.modify_cc_others'],
								$insert_perms['agent_tickets.modify_merge_others'],
								$insert_perms['agent_tickets.modify_labels_others'],
								$insert_perms['agent_tickets.modify_labels_others'],
								$insert_perms['agent_tickets.modify_set_hold_others'],
								$insert_perms['agent_tickets.modify_set_awaiting_user_others'],
								$insert_perms['agent_tickets.modify_set_awaiting_agent_others'],
								$insert_perms['agent_tickets.modify_set_resolved_others']
							);
						}
					}

					//-----
					// Users
					//-----

					if (!$tech['p_create_users']) {
						unset($insert_perms['agent_people.create']);
					}

					if (!$tech['p_edit_users']) {
						unset(
							$insert_perms['agent_people.edit'],
							$insert_perms['agent_people.validate'],
							$insert_perms['agent_people.manage_emails'],
							$insert_perms['agent_people.reset_password'],
							$insert_perms['agent_people.delete']
						);
					}

					if (!$tech['p_delete_users']) {
						unset($insert_perms['agent_people.delete']);
					}

					if (!$tech['p_approve_new_registrations']) {
						unset($insert_perms['agent_people.validate']);
					}

					//-----
					// Chat
					//-----

					if (!$tech['p_chat']) {
						unset(
							$insert_perms['agent_chat.use'],
							$insert_perms['agent_chat.view_unassigned'],
							$insert_perms['agent_chat.view_others'],
							$insert_perms['agent_chat.delete']
						);
					} else {
						if (isset($tech['p_chat_del_logs']) && !$tech['p_chat_del_logs']) {
							unset($insert_perms['agent_chat.delete']);
						}
					}

					foreach ($insert_perms as $k => $v) {
						$this->getDb()->insert('permissions', array(
							'person_id' => $agent->id,
							'name' => $k,
							'value' => 1
						));
					}
				}

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
