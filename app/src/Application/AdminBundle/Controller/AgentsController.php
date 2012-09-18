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
 * @subpackage AdminBundle
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\Entity;
use Application\DeskPRO\App;

use Application\AdminBundle\Form\EditAgentType;
use Application\AdminBundle\FormModel as AdminFormModel;
use Application\DeskPRO\Entity\Usersource;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

use Symfony\Component\Form;

class AgentsController extends AbstractController
{
	protected $num_agents = 0;
	protected $max_agents = 0;

	protected function init()
	{
		parent::init();

		$this->num_agents = $this->db->fetchColumn("SELECT COUNT(*) FROM people WHERE is_agent = 1 AND is_deleted = 0");
		$this->max_agents = \DeskPRO\Kernel\License::getLicense()->getMaxAgents();
		if (!$this->max_agents) {
			$this->max_agents = 999999999;
		}

		$this->get('templating.globals')->setVariable('num_agents', $this->num_agents);
		$this->get('templating.globals')->setVariable('max_agents', $this->max_agents);
	}

	public function canAddAgent($context)
	{
		return $this->num_agents < $this->max_agents;
	}

	public function showLicenseError()
	{
		$billing_admins = $this->em->createQuery("
			SELECT p
			FROM DeskPRO:Person p
			WHERE p.can_admin = true AND p.can_billing = true
		")->execute();

		return $this->render('AdminBundle:Agents:error-max-agents.html.twig', array(
			'billing_admins' => $billing_admins,
			'num_agents' => $this->num_agents,
			'max_agents' => $this->max_agents,
		));
	}

	############################################################################
	# agents
	############################################################################

	public function agentsAction()
	{
		$all_agents = $this->em->createQuery("
			SELECT p, pic, email
			FROM DeskPRO:Person p INDEX BY p.id
			LEFT JOIN p.primary_email email
			LEFT JOIN p.picture_blob pic
			WHERE p.is_agent = true AND p.is_deleted = false
			ORDER BY p.first_name, p.last_name
		")->execute();

		$count_deleted = $this->db->fetchColumn("SELECT COUNT(*) FROM people WHERE is_agent = 1 AND is_deleted = 1");

		foreach ($all_agents as $agent) {
			$agent->loadHelper('Agent');
		}

		$all_teams = $this->em->createQuery("
			SELECT t
			FROM DeskPRO:AgentTeam t INDEX BY t.id
			ORDER BY t.name ASC
		")->execute();

		$all_usergroups = $this->em->createQuery("
			SELECT ug
			FROM DeskPRO:Usergroup ug INDEX BY ug.id
			WHERE ug.is_agent_group = true
			ORDER BY ug.title ASC
		")->execute();

		$team_member_ids = $this->em->getRepository('DeskPRO:AgentTeam')->getSortedMemberIds();
		$usergroup_member_ids = $this->em->getRepository('DeskPRO:Usergroup')->getSortedAgentIds();

		$agent_to_groups = array();
		foreach ($usergroup_member_ids as $ug_id => $members) {
			foreach ($members as $pid) {
				if (!isset($agent_to_groups[$pid])) $agent_to_groups[$pid] = array();
				$agent_to_groups[$pid][] = $ug_id;
			}
		}

		$agent_to_teams = array();
		foreach ($team_member_ids as $team_id => $members) {
			foreach ($members as $pid) {
				if (!isset($agent_to_teams[$pid])) $agent_to_teams[$pid] = array();
				$agent_to_teams[$pid][] = $team_id;
			}
		}

		$agents_to_deps = $this->db->fetchAllGrouped("
			SELECT department_id, person_id
			FROM department_permissions WHERE person_id IS NOT NULL
		", array(), 'person_id', null, 'department_id');

		$overrides_counts = $this->db->fetchAllKeyValue("
			SELECT person_id, COUNT(*)
			FROM permissions
			WHERE person_id IS NOT NULL
			GROUP BY person_id
		");

		$all_departments = $this->em->createQuery("
			SELECT d
			FROM DeskPRO:Department d INDEX BY d.id
			ORDER BY d.display_order
		")->execute();

		$add_from_usersource = array();
		$usersources = $this->em->createQuery("
			SELECT us
			FROM DeskPRO:Usersource us
			ORDER BY us.display_order ASC, us.title ASC
		")->execute();
		foreach ($usersources as $us) {
			if (in_array($us['source_type'], array('ldap', 'active_directory'))) {
				$add_from_usersource[] = $us;
			}
		}

		return $this->render('AdminBundle:Agents:list.html.twig', array(
			'all_agents'     => $all_agents,
			'agent_to_groups' => $agent_to_groups,
			'agent_to_teams' => $agent_to_teams,
			'agents_to_deps' => $agents_to_deps,
			'all_teams'      => $all_teams,
			'all_usergroups' => $all_usergroups,
			'all_departments' => $all_departments,
			'add_from_usersource' => $add_from_usersource,

			'team_member_ids'      => $team_member_ids,
			'usergroup_member_ids' => $usergroup_member_ids,
			'overrides_counts' => $overrides_counts,

			'count_deleted' => $count_deleted,
		));
	}

	public function deletedAgentsAction()
	{
		$all_agents = $this->em->createQuery("
			SELECT p, pic, email
			FROM DeskPRO:Person p INDEX BY p.id
			LEFT JOIN p.primary_email email
			LEFT JOIN p.picture_blob pic
			WHERE p.is_agent = true AND p.is_deleted = true
			ORDER BY p.first_name, p.last_name
		")->execute();

		if (!$all_agents) {
			return $this->redirectRoute('admin_agents');
		}

		return $this->render('AdminBundle:Agents:list-deleted.html.twig', array(
			'all_agents'     => $all_agents,
		));
	}

	############################################################################
	# add-from
	############################################################################

	public function newFromUsersourceAction($usersource_id)
	{
		if (!$this->canAddAgent('view_uc')) return $this->showLicenseError();

		$usersource = $this->em->find('DeskPRO:Usersource', $usersource_id);

		return $this->render('AdminBundle:Agents:add-from-usersource.html.twig', array(
			'usersource' => $usersource
		));
	}

	public function newFromUsersourceMakeAction($usersource_id)
	{
		if (!$this->canAddAgent('save_uc')) return $this->showLicenseError();

		$username = $this->in->getString('search_term');

		/** @var $usersource \Application\DeskPRO\Entity\Usersource */
		$usersource = $this->em->find('DeskPRO:Usersource', $usersource_id);

		$identity = $usersource->getAdapter()->findIdentityByInput($username);

		if (!$identity) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$this->db->beginTransaction();
		try {
			$login_processor = new \Application\DeskPRO\Auth\LoginProcessor($usersource, $identity);
			$person = $login_processor->getPerson();

			$person->is_agent = true;
			$person->can_agent = true;

			$this->em->persist($person);
			$this->em->flush();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_agents_edit', array('person_id' => $person->getId()));
	}

	public function newFromUsersourceSearchAction($usersource_id)
	{
		$username = $this->in->getString('search_term');

		/** @var $usersource \Application\DeskPRO\Entity\Usersource */
		$usersource = $this->em->find('DeskPRO:Usersource', $usersource_id);

		$identity = $usersource->getAdapter()->findIdentityByInput($username);
		$raw_info = null;
		if ($identity) {
			$raw_info = $identity->getRawData();
		}

		return $this->render('AdminBundle:Agents:add-from-usersource-result.html.twig', array(
			'usersource' => $usersource,
			'raw_info' => $raw_info,
			'search_term' => $username,
		));
	}

	############################################################################
	# edit-agent
	############################################################################

	public function editAgentAction($person_id)
	{
		if ($person_id) {
			$agent = $this->getAgentOr404($person_id);
		} else {
			if (!$this->canAddAgent('view')) return $this->showLicenseError();
			$agent = new \Application\DeskPRO\Entity\Person();
		}

		$all_teams = $this->em->createQuery("
			SELECT t
			FROM DeskPRO:AgentTeam t
			ORDER BY t.name ASC
		")->execute();

		$all_usergroups = $this->em->createQuery("
			SELECT ug
			FROM DeskPRO:Usergroup ug
			WHERE ug.is_agent_group = true
			ORDER BY ug.title ASC
		")->execute();

		$ug_perms = $this->db->fetchAllGrouped("
			SELECT usergroup_id, name, value
			FROM permissions
			LEFT JOIN usergroups ON (usergroups.id = permissions.id)
			WHERE usergroups.is_agent_group = 1
		", array(), 'usergroup_id', 'name', 'value');

		$override_perms = $this->db->fetchAllKeyValue("
			SELECT name, value
			FROM permissions
			WHERE person_id = ?
		", array($agent->id));

		$departments = $this->container->getDataService('Department')->getAll();

		$agent_usergroups = $this->db->fetchAllCol("SELECT usergroup_id FROM person2usergroups WHERE person_id = ?", array($agent->id));
		$agent_teams = $this->db->fetchAllCol("SELECT team_id FROM agent_team_members WHERE person_id = ?", array($agent->id));

		$agent_deps = $this->db->fetchAllGrouped("
			SELECT department_id, app
			FROM department_permissions
			WHERE person_id = ?
		", array($agent->id), 'department_id', 'app', 'app');

		$all = $this->db->fetchAll("
			SELECT usergroup_id, name, value
			FROM permissions
			WHERE usergroup_id IS NOT NULL
		");
		$usergroup_values = array();
		foreach ($all as $r) {
			if (!isset($usergroup_values[$r['usergroup_id']])) {
				$usergroup_values[$r['usergroup_id']] = array();
			}
			$usergroup_values[$r['usergroup_id']][$r['name']] = $r['value'];
		}

		$usergroup_values['override'] = $this->db->fetchAllKeyValue("
			SELECT name, value
			FROM permissions
			WHERE person_id = ?
		", array($agent->id));

		return $this->render('@Agents:edit-agent.html.twig', array(
			'agent' => $agent,
			'all_usergroups' => $all_usergroups,
			'all_teams' => $all_teams,
			'agent_usergroups' => $agent_usergroups,
			'agent_teams' => $agent_teams,
			'usergroup_values' => $usergroup_values,
			'ug_perms' => $ug_perms,
			'override_perms' => $override_perms,
			'departments' => $departments,
			'agent_deps' => $agent_deps,
			'random_password' => Strings::randomPronounceable(10)
		));
	}

	public function quickEditFormValidateAction($person_id)
	{
		$agent = null;
		if ($person_id) {
			$agent = $this->getAgentOr404($person_id);
		}

		$errors = array();

		$email = $this->in->getString('agent.email');
		if (!$email or !\Orb\Validator\StringEmail::isValueValid($email)) {
			$errors[] = 'The email address you entered is not valid.';
		} elseif (!$agent or !$agent->findEmailAddress($email)) {
			$exist_check = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email);
			if ($exist_check) {
				if ($person_id) {
					$errors[] = "The email address you entered already belongs to an existing user.";
				} else {
					if ($exist_check->is_agent) {
						$errors[] = "The email address you entered already belongs to an existing user.";
					} else {
						if (!$this->in->getBool('confirm_email_dupe')) {
							$errors[] = 'show_dupe_confirm';
						}
					}
				}
			}
		}

		if (!$this->in->getString('agent.first_name')) {
			$errors[] = 'You did not enter a first name';
		}
		if (!$this->in->getString('agent.last_name')) {
			$errors[] = 'You did not enter a last name';
		}

		if ($errors) {
			return $this->createJsonResponse(array('error' => true, 'error_messages' => $errors));
		}

		return $this->createJsonResponse(array('success' => true));
	}

	public function editAgentSaveAction($person_id)
	{
		$set_email = $this->in->getString('agent.email');
		$exist_check = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($set_email);

		if (!$person_id && $exist_check && !$exist_check->is_agent && $this->in->getBool('confirm_email_dupe')) {
			$person_id = $exist_check->getId();
			$agent = $exist_check;
			$agent->is_user = true;
			$agent->is_confirmed = true;
			$agent->is_agent = true;
			$is_new = false;

		} elseif ($person_id) {
			$agent = $this->getAgentOr404($person_id);
			$is_new = false;
		} else {

			$agent = new \Application\DeskPRO\Entity\Person();

			$agent->setPassword(Strings::random(20));
			$agent->is_user = true;
			$agent->is_confirmed = true;
			$agent->is_agent = true;

			$is_new = true;
		}

		if ($is_new) {
			if (!$this->canAddAgent('save')) return $this->showLicenseError();
		}

		$agent->first_name = $this->in->getString('agent.first_name');
		$agent->last_name = $this->in->getString('agent.last_name');

		$errors = array();

		if (!$agent->first_name) {
			$errors[] = 'You did not enter a first name';
		}
		if (!$agent->last_name) {
			$errors[] = 'You did not enter a last name';
		}

		foreach (array('can_agent', 'can_admin', 'can_billing', 'can_reports') as $prop) {
			$agent->$prop = $this->in->getBool('agent.' . $prop);
		}

		if (!$agent->findEmailAddress($set_email)) {
			if (!\Orb\Validator\StringEmail::isValueValid($set_email)) {
				$errors[] = 'The email address you entered is invalid';
			} else {
				if ($exist_check && $exist_check->id != $agent->id) {
					$errors[] = 'The new email address you entered already belongs to a different user.';
				}
			}
		} else {
			$set_email = null;
		}

		// We do client-side validation, so errors checks here are
		// a backup check.
		if ($errors) {
			return $this->renderStandardError(
				"Please correct these errors and try again.",
				"Errors with your form",
				200,
				array('error_list' => $errors)
			);
		}

		if ($this->in->getString('agent.password')) {
			$agent->setPassword($this->in->getString('agent.password'));
		}

		$this->em->getConnection()->beginTransaction();

		try {

			#------------------------------
			# Basic properties
			#------------------------------

			if ($set_email) {
				$old_email = $agent->getPrimaryEmail();
				if ($old_email && $old_email->email != $set_email) {
					$agent->removeEmailAddressId($old_email->id);
				}

				$email = $agent->setEmail($set_email, true);
				$this->em->persist($email);
			}

			$this->em->persist($agent);
			$this->em->flush();

			#------------------------------
			# Teams
			#------------------------------

			$this->db->delete('agent_team_members', array('person_id' => $agent->id));

			$team_ids = $this->in->getCleanValueArray('agent.teams', 'uint', 'discard');
			if ($team_ids) {
				$team_ids = $this->db->fetchAllCol("SELECT id FROM agent_teams WHERE id IN (" . implode(',', $team_ids) . ")");
			}

			foreach ($team_ids as $tid) {
				$this->db->insert('agent_team_members', array('person_id' => $agent->id, 'team_id' => $tid));
			}

			$this->em->flush();

			#------------------------------
			# Usergroups
			#------------------------------

			$ug_ids = $this->in->getCleanValueArray('agent.usergroups', 'uint', 'discard');
			$usergroups = $this->em->getRepository('DeskPRO:Usergroup')->getByIds($ug_ids);

			$ch = new \Application\DeskPRO\ORM\CollectionHelper($agent, 'usergroups');
			$ch->setCollection($usergroups);

			$this->em->flush();

			#------------------------------
			# Departments
			#------------------------------

			$dep_matrix = $this->in->getCleanValueArray('agent.departments', 'raw', 'uint');

			$this->db->delete('department_permissions', array('person_id' => $agent->id));
			foreach ($dep_matrix as $dep_id => $apps) {
				foreach ($apps as $app => $v) {
					if (!$v) continue;
					$this->db->insert('department_permissions', array('department_id' => $dep_id, 'person_id' => $agent->id, 'app' => $app));
				}
			}

			#------------------------------
			# Permissions on groups and overrides, oh my
			#------------------------------

			$ug_perm_matrix = $this->in->getCleanValueArray('permissions', 'raw', 'raw');

			$ug_perms_set = array();

			$overrides = array();
			foreach ($ug_perm_matrix as $group => $ug_perms) {
				foreach ($ug_perms as $ug_id => $perms) {

					// Not one we enabled so we dont care
					if ($ug_id != 'override' && !isset($usergroups[$ug_id])) {
						continue;
					}

					foreach ($perms as $perm => $v) {

						if (!$v) {
							continue; //dont care about non 1's
						}

						$perm_name = "{$group}.{$perm}";

						if ($ug_id == 'override') {
							$overrides[$perm_name] = 1;
						} else {
							if (!isset($ug_perms[$ug_id])) {
								$ug_perms_set[$ug_id] = array();
							}
							$ug_perms_set[$ug_id][$perm_name] = 1;
						}
					}
				}
			}

			// Figure out if we have any superfluous overrides
			foreach ($overrides as $perm_name => $v) {
				foreach ($ug_perms as $perms) {
					if (isset($perms[$perm_name])) {
						unset($overrides[$perm_name]);
						break;
					}
				}
			}

			// Save overrides
			$this->db->delete('permissions', array('person_id' => $agent->id));
			foreach ($overrides as $perm_name => $v) {
				$this->db->insert('permissions', array('person_id' => $agent->id, 'name' => $perm_name, 'value' => 1));
			}

			// For each of the usergroups we might also have to update those perms now
			foreach ($ug_perms_set as $ug_id => $perms) {
				$this->db->delete('permissions', array('usergroup_id' => $ug_id));
				foreach ($perms as $perm_name => $v) {
					$this->db->insert('permissions', array('usergroup_id' => $ug_id, 'name' => $perm_name, 'value' => 1));
				}
			}

			// If they're new, enable notifications for them by default
			if ($is_new) {
				$agent_id = $agent->getId();
				$this->db->executeUpdate("
					INSERT INTO `ticket_filter_subscriptions` (`id`, `filter_id`, `person_id`, `email_created`, `email_new`, `email_user_activity`, `email_agent_activity`, `email_property_change`, `alert_new`, `alert_user_activity`, `alert_agent_activity`, `alert_property_change`)
					VALUES
						(NULL, 1, $agent_id, 1, 1, 1, 1, 1, 1, 1, 1, 1),
						(NULL, 2, $agent_id, 1, 1, 1, 1, 1, 1, 1, 1, 1),
						(NULL, 3, $agent_id, 1, 1, 1, 1, 1, 1, 1, 1, 1),
						(NULL, 4, $agent_id, 1, 1, 1, 1, 1, 1, 1, 1, 1),
						(NULL, 5, $agent_id, 1, 1, 1, 1, 1, 1, 1, 1, 1)
				");

				$this->db->executeUpdate("
					INSERT INTO `people_prefs` (`person_id`, `name`, `value_str`, `value_array`, `date_expire`)
					VALUES
						($agent_id, 'agent_notif.chat_message.email', '1', X'4E3B', NULL),
						($agent_id, 'agent_notif.login_attempt_fail.email', '1', X'4E3B', NULL),
						($agent_id, 'agent_notif.new_comment.alert', '1', X'4E3B', NULL),
						($agent_id, 'agent_notif.new_comment.email', '1', X'4E3B', NULL),
						($agent_id, 'agent_notif.new_comment_validate.alert', '1', X'4E3B', NULL),
						($agent_id, 'agent_notif.new_comment_validate.email', '1', X'4E3B', NULL),
						($agent_id, 'agent_notif.new_feedback.alert', '1', X'4E3B', NULL),
						($agent_id, 'agent_notif.new_feedback.email', '1', X'4E3B', NULL),
						($agent_id, 'agent_notif.new_feedback_validate.alert', '1', X'4E3B', NULL),
						($agent_id, 'agent_notif.new_feedback_validate.email', '1', X'4E3B', NULL),
						($agent_id, 'agent_notif.new_user.alert', '1', X'4E3B', NULL),
						($agent_id, 'agent_notif.new_user_validate.alert', '1', X'4E3B', NULL),
						($agent_id, 'agent_notif.new_user_validate.email', '1', X'4E3B', NULL)
				");
			}

			$this->em->flush();
			$this->em->getConnection()->commit();

		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		if ($is_new) {

			// Send welcome email
			$message = $this->container->getMailer()->createMessage();
			$message->setToPerson($agent);
			$message->setTemplate('DeskPRO:emails_agent:agent-welcome.html.twig', array('agent' => $agent));
			$this->container->getMailer()->send($message);

			$this->em->getRepository('DeskPRO:Setting')->updateSetting('core.task_completed_add_agents', time());
		}

		$this->session->setFlash('saved_agent', 1);
		$this->session->save();

		return $this->redirectRoute('admin_agents_edit', array('person_id' => $agent->id));
	}

	public function getAgentPermissionsAction($person_id)
	{
		$agent = $this->getAgentOr404($person_id);

		$perms = $agent->getPermissionsManager()->get('Usergroups')->getAllPermissions();

		return $this->createJsonResponse($perms);
	}

	public function setVacationModeAction($person_id, $set_to = 0)
	{
		$this->ensureRequestToken();

		$agent = $this->getAgentOr404($person_id);
		$agent->is_vacation_mode = $set_to;

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($agent);
			$this->em->flush();
			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_agents_edit', array('person_id' => $agent->id));
	}

	public function setDeletedAction($person_id, $set_to = 0)
	{
		$this->ensureRequestToken();

		$agent = $this->getAgentOr404($person_id);
		$agent->is_deleted = $set_to;

		if (!$set_to) {
			if (!$this->canAddAgent('save_deleted')) return $this->showLicenseError();
		}

		$this->em->getConnection()->beginTransaction();

		try {

			// Remove their permissions
			App::getDb()->delete('department_permissions', array('person_id' => $agent->getId()));
			App::getDb()->delete('permissions', array('person_id' => $agent->getId()));

			$this->em->persist($agent);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		if ($set_to) {
			return $this->redirectRoute('admin_agents');
		} else {
			return $this->redirectRoute('admin_agents_edit', array('person_id' => $agent->id));
		}
	}

	############################################################################
	# edit-team
	############################################################################

	/**
	 * Edit a team
	 */
	public function editTeamAction($team_id = 0)
	{
		if ($team_id) {
			$team = $this->getAgentTeamOr404($team_id);
		} else {
			$team = new Entity\AgentTeam();
		}

		if ($this->in->getBool('process')) {

			$this->em->getConnection()->beginTransaction();

			try {
				$team->name = $this->in->getString('team.name');
				if (!$team->name) {
					$team->name = 'New Team';
				}

				$this->em->persist($team);
				$this->em->flush();

				$this->db->delete('agent_team_members', array('team_id' => $team->id));

				foreach ($this->in->getCleanValueArray('team.members', 'uint', 'discard') as $pid) {
					$this->db->insert('agent_team_members', array('team_id' => $team->id, 'person_id' => $pid));
				}

				$this->em->getRepository('DeskPRO:Setting')->updateSetting('core.use_agent_team', '1');

				$this->em->getConnection()->commit();
			} catch (\Exception $e) {
				$this->em->getConnection()->rollback();
				throw $e;
			}

			return $this->redirectRoute('admin_agents');
		}

		$agents = $this->em->getRepository('DeskPRO:Person')->getAgents();

		$team_members = array();
		if ($team_id) {
			$team_members = App::getDb()->fetchAllKeyValue("
				SELECT person_id, 1
				FROM agent_team_members
				WHERE team_id = ?
			", array($team_id), 0, 1);
		}

		return $this->render('AdminBundle:Agents:edit-team.html.twig', array(
			'team' => $team,
			'agents' => $agents,
			'team_members' => $team_members,
		));
	}

	public function deleteTeamAction($team_id, $security_token)
	{
		$team = $this->getAgentTeamOr404($team_id);

		if (!$this->session->getEntity()->checkSecurityToken('delete_team', $security_token)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->remove($team);
			$this->em->flush();

			$count = $this->db->fetchColumn("SELECT COUNT(*) FROM agent_teams");
			if (!$count) {
				$this->em->getRepository('DeskPRO:Setting')->updateSetting('core.use_agent_team', '0');
			}

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_agents');
	}

	############################################################################
	# edit
	############################################################################

	public function editGroupAction($usergroup_id)
	{
		if (!$usergroup_id) {
			$usergroup = new Entity\Usergroup();
		} else {
			$usergroup = $this->getAgentGroupOr404($usergroup_id);
		}

		if ($this->in->getBool('process')) {

			$this->em->getConnection()->beginTransaction();

			try {
				$usergroup->title = $this->in->getString('usergroup.title');
				$usergroup->is_agent_group = true;

				$this->em->persist($usergroup);
				$this->em->flush();

				$this->db->delete('person2usergroups', array('usergroup_id' => $usergroup->id));

				foreach ($this->in->getCleanValueArray('usergroup.members', 'uint', 'discard') as $pid) {
					$this->db->insert('person2usergroups', array('usergroup_id' => $usergroup->id, 'person_id' => $pid));
				}

				$this->db->delete('permissions', array('usergroup_id' => $usergroup->id));
				$perms_groups = $this->in->getCleanValueArray('permissions', 'raw', 'string');
				foreach ($perms_groups as $perm_group => $perms) {
					foreach ($perms as $name => $v) {
						$perm_name = "{$perm_group}.$name";
						$this->db->insert('permissions', array('usergroup_id' => $usergroup->id, 'name' => $perm_name, 'value' => 1));
					}
				}

				$this->em->getConnection()->commit();

			} catch (\Exception $e) {
				$this->em->getConnection()->rollback();
				throw $e;
			}

			return $this->redirectRoute('admin_agents_groups_edit', array('usergroup_id' => $usergroup->id));
		}

		if ($usergroup_id) {
			$members = $this->em->getRepository('DeskPRO:Person')->getUsergroupMemberIds($usergroup);
		} else {
			$members = array();
		}

		$agents = $this->em->getRepository('DeskPRO:Person')->getAgents();

		$usergroup_values = $this->db->fetchAllKeyValue("
			SELECT name, value
			FROM permissions
			WHERE usergroup_id = ?
		", array($usergroup->id));

		return $this->render('AdminBundle:Agents:edit-usergroup.html.twig', array(
			'usergroup' => $usergroup,
			'members' => $members,
			'agents' => $agents,
			'usergroup_values' => $usergroup_values,
		));
	}

	public function deleteGroupAction($usergroup_id, $security_token)
	{
		$usergroup = $this->getAgentGroupOr404($usergroup_id);

		if (!$this->session->getEntity()->checkSecurityToken('delete_group', $security_token)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->remove($usergroup);
			$this->em->flush();
			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_agents');
	}

	############################################################################

	/**
	 * @return \Application\DeskPRO\Entity\AgentTeam
	 */
	protected function getAgentTeamOr404($id)
	{
		$team = $this->em->getRepository('DeskPRO:AgentTeam')->find($id);
		if (!$team) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no team with ID $id");
		}

		return $team;
	}

	/**
	 * @return \Application\DeskPRO\Entity\Usergroup
	 */
	protected function getAgentGroupOr404($id)
	{
		$ug = $this->em->getRepository('DeskPRO:Usergroup')->find($id);
		if (!$ug || !$ug->is_agent_group) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no usergroup with ID $id");
		}

		return $ug;
	}

	/**
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function getAgentOr404($id)
	{
		$agent = $this->em->find('DeskPRO:Person', $id);
		if (!$agent || !$agent->is_agent) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no agent with ID $id");
		}

		$agent->loadHelper('Agent');

		return $agent;
	}
}
