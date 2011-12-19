<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\Entity;
use Application\DeskPRO\App;

use Application\AdminBundle\Form\EditAgentType;
use Application\AdminBundle\FormModel as AdminFormModel;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

use Symfony\Component\Form;

class AgentsController extends AbstractController
{
	############################################################################
	# agents
	############################################################################

	public function agentsAction()
	{
		$all_agents = App::getOrm()->createQuery("
			SELECT p, pic, email
			FROM DeskPRO:Person p INDEX BY p.id
			LEFT JOIN p.primary_email email
			LEFT JOIN p.picture_blob pic
			WHERE p.is_agent = true
			ORDER BY p.first_name, p.last_name
		")->execute();

		foreach ($all_agents as $agent) {
			$agent->loadHelper('Agent');
		}

		$all_teams = App::getOrm()->createQuery("
			SELECT t
			FROM DeskPRO:AgentTeam t INDEX BY t.id
			ORDER BY t.name ASC
		")->execute();

		$all_usergroups = App::getOrm()->createQuery("
			SELECT ug
			FROM DeskPRO:Usergroup ug INDEX BY ug.id
			WHERE ug.is_agent_group = true
			ORDER BY ug.title ASC
		")->execute();

		$team_member_ids = App::getEntityRepository('DeskPRO:AgentTeam')->getSortedMemberIds();
		$usergroup_member_ids = App::getEntityRepository('DeskPRO:Usergroup')->getSortedAgentIds();

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
			FROM department_permissions
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

		return $this->render('AdminBundle:Agents:list.html.twig', array(
			'all_agents'     => $all_agents,
			'agent_to_groups' => $agent_to_groups,
			'agent_to_teams' => $agent_to_teams,
			'agents_to_deps' => $agents_to_deps,
			'all_teams'      => $all_teams,
			'all_usergroups' => $all_usergroups,
			'all_departments' => $all_departments,

			'team_member_ids'      => $team_member_ids,
			'usergroup_member_ids' => $usergroup_member_ids,
			'overrides_counts' => $overrides_counts,
		));
	}

	############################################################################
	# new-agent
	############################################################################

	public function newAgentAction()
	{
		$errors = array();

		if ($this->in->getBool('process')) {

			$email_address = $this->in->getString('agent.email');
			$first_name    = $this->in->getString('agent.first_name');
			$last_name     = $this->in->getString('agent.last_name');
			$password      = $this->in->getString('agent.password');
			$password2      = $this->in->getString('agent.password2');

			if (!\Orb\Validator\StringEmail::isValueValid($email_address)) {
				$errors['email'] = true;
			}
			if (!$first_name || !$last_name) {
				$errors['name'] = true;
			}

			if (!$password) {
				$errors['password'] = true;
			}
			if ($password != $password2) {
				$errors['password2'] = true;
			}

			if (!$errors) {
				$person = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($email_address);
				if (!$person) {
					$person = new \Application\DeskPRO\Entity\Person();
					$person->setEmail($email_address, true);
				}

				$person->setPassword($password);
				$person->first_name = $first_name;
				$person->last_name  = $last_name;
				$person->is_user = true;
				$person->is_confirmed = true;
				$person->is_agent = true;

				$this->em->getConnection()->beginTransaction();

				try {
					$this->em->persist($person);
					$this->em->flush();
					$this->em->getConnection()->commit();
				} catch (\Exception $e) {
					$this->em->getConnection()->rollback();
					throw $e;
				}

				return $this->redirectRoute('admin_agents_edit', array('person_id' => $person['id']));
			}
		}

		return $this->render('AdminBundle:Agents:edit-new-agent.html.twig', array(
			'errors' => $errors
		));
	}

	############################################################################
	# edit-agent
	############################################################################

	public function editAgentAction($person_id)
	{
		$agent = $this->getAgentOr404($person_id);

		$all_teams = App::getOrm()->createQuery("
			SELECT t
			FROM DeskPRO:AgentTeam t
			ORDER BY t.name ASC
		")->execute();

		$all_usergroups = App::getOrm()->createQuery("
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

		$departments = $this->em->getRepository('DeskPRO:Department')->getAll();

		$agent_usergroups = $this->db->fetchAllCol("SELECT usergroup_id FROM person2usergroups WHERE person_id = ?", array($agent->id));
		$agent_teams = $this->db->fetchAllCol("SELECT team_id FROM agent_team_members WHERE person_id = ?", array($agent->id));

		$agent_deps = $this->db->fetchAllGrouped("
			SELECT department_id, app
			FROM department_permissions
			WHERE person_id = ?
		", array($agent->id), 'department_id', 'app', 'app');

		$usergroup_values = $this->db->fetchAllGrouped("
			SELECT usergroup_id, name, value
			FROM permissions
		", array(), 'usergroup_id', 'name', 'value');

		$usergroup_values['override'] = $this->db->fetchAllKeyValue("
			SELECT name, value
			FROM permissions
			WHERE person_id = ?
		", array($agent->id));

		return $this->render('AdminBundle:Agents:edit-agent.html.twig', array(
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
		));
	}


	public function editAgentSaveAction($person_id)
	{
		$agent = $this->getAgentOr404($person_id);
		$agent->first_name = $this->in->getString('agent.first_name');
		$agent->last_name = $this->in->getString('agent.last_name');

		$errors = array();

		if (!$agent->first_name) {
			$errors[] = 'You did not enter a first name';
		}
		if (!$agent->last_name) {
			$errors[] = 'You did not enter a last name';
		}

		$set_email = $this->in->getString('agent.email');
		if (!$agent->findEmailAddress($set_email)) {
			if (!\Orb\Validator\StringEmail::isValueValid($set_email)) {
				$errors[] = 'The email address you entered is invalid';
			} else {
				$exist_check = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($set_email);
				if ($exist_check) {
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

		$this->em->getConnection()->beginTransaction();

		try {

			#------------------------------
			# Basic properties
			#------------------------------

			if ($set_email) {
				$old_email = $agent->getPrimaryEmail();
				$agent->removeEmailAddressId($old_email->id);

				$agent->setEmail($set_email, true);
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

			$ch = new \Application\DeskPRO\ORM\CollectionHelper($this->em, $agent, 'usergroups');
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

			$this->em->flush();
			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_agents_edit', array('person_id' => $agent->id));
	}

	public function getAgentPermissionsAction($person_id)
	{
		$agent = $this->getAgentOr404($person_id);

		$perms = $agent->getPermissionsManager()->get('Usergroups')->getAllPermissions();

		return $this->createJsonResponse($perms);
	}

	############################################################################
	# edit-team
	############################################################################

	/**
	 * Edit a team
	 */
	public function editTeamAction($team_id)
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

				$this->em->getConnection()->commit();
			} catch (\Exception $e) {
				$this->em->getConnection()->rollback();
				throw $e;
			}

			return $this->redirectRoute('admin_agents');
		}

		$agents = $this->em->getRepository('DeskPRO:Person')->getAgents();

		return $this->render('AdminBundle:Agents:edit-team.html.twig', array(
			'team' => $team,
			'agents' => $agents
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
		$team = App::getEntityRepository('DeskPRO:AgentTeam')->find($id);
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
		$ug = App::getEntityRepository('DeskPRO:Usergroup')->find($id);
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
