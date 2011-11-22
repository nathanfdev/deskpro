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

	public function agentsAction($group_by = null)
	{
		$this->rememberLastPage();

		$all_agents = App::getOrm()->createQuery("
			SELECT p
			FROM DeskPRO:Person p INDEX BY p.id
			LEFT JOIN p.usergroups u
			WHERE p.is_agent = true
			ORDER BY p.first_name, p.last_name
		")->execute();

		foreach ($all_agents as $agent) {
			$agent->loadHelper('Agent');
		}

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

		$team_member_ids      = App::getEntityRepository('DeskPRO:AgentTeam')->getSortedMemberIds();
		$usergroup_member_ids = App::getEntityRepository('DeskPRO:Usergroup')->getSortedAgentIds();

		return $this->render('AdminBundle:Agents:list.html.twig', array(
			'all_agents'     => $all_agents,
			'all_teams'      => $all_teams,
			'all_usergroups' => $all_usergroups,

			'team_member_ids'      => $team_member_ids,
			'usergroup_member_ids' => $usergroup_member_ids,
		));
	}

	############################################################################
	# new-agent
	############################################################################

	public function newAgentAction()
	{
		if ($this->in->getBool('process')) {
			$email_address = $this->in->getString('email');
			$person = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($email_address);

			if ($person AND !$person['is_agent']) {
				$person['is_agent'] = true;
				App::getOrm()->beginTransaction();
				App::getOrm()->persist($person);
				App::getOrm()->flush();
				App::getOrm()->commit();
			}

			if (!$person) {
				$person = new Entity\Person();
				$email = new Entity\PersonEmail();
				$email['email'] = $email_address;
				$email['is_validated'] = true;

				$person->addEmailAddress($email);
				$person['is_agent'] = true;

				App::getOrm()->beginTransaction();
				App::getOrm()->persist($person);
				App::getOrm()->flush();
				App::getOrm()->commit();
			}

			return $this->redirectRoute('admin_agents_edit', array('person_id' => $person['id']));
		}

		return $this->render('AdminBundle:Agents:edit-new-agent.html.twig', array(

		));
	}

	############################################################################
	# edit-agent
	############################################################################

	public function editAgentAction($person_id)
	{
		$agent = App::getEntityRepository('DeskPRO:Person')->find($person_id);
		$agent->loadHelper('Agent');

		$all_usergroups = App::getOrm()->createQuery("
			SELECT ug
			FROM DeskPRO:Usergroup ug
			WHERE ug.is_agent_group = true
			ORDER BY ug.title ASC
		")->execute();

		return $this->render('AdminBundle:Agents:edit-agent.html.twig', array(
			'agent' => $agent,
			'all_usergroups' => $all_usergroups,

		));
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

				$this->em->getConnection()->commit();

			} catch (\Exception $e) {
				$this->em->getConnection()->rollback();
				throw $e;
			}

			return $this->redirectRoute('admin_agents');
		}

		if ($usergroup_id) {
			$members = $this->em->getRepository('DeskPRO:Person')->getUsergroupMembers($usergroup);
		} else {
			$members = array();
		}

		$agents = $this->em->getRepository('DeskPRO:Person')->getAgents();

		return $this->render('AdminBundle:Agents:edit-usergroup.html.twig', array(
			'usergroup' => $usergroup,
			'members' => $members,
			'agents' => $agents
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
	 * @return Application\DeskPRO\Entity\AgentTeam
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
	 * @return Application\DeskPRO\Entity\Usergroup
	 */
	protected function getAgentGroupOr404($id)
	{
		$ug = App::getEntityRepository('DeskPRO:Usergroup')->find($id);
		if (!$ug || !$ug->is_agent_group) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no usergroup with ID $id");
		}

		return $ug;
	}
}
