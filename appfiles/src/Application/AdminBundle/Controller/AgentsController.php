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

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;
use \Orb\Util\Util;

class AgentsController extends AbstractController
{
	############################################################################
	# agents
	############################################################################

	public function agentsAction($group_by = null)
	{
		$all_agents = App::getOrm()->createQuery("
			SELECT p
			FROM DeskPRO:Person p
			LEFT JOIN p.usergroups u
			WHERE p.is_agent = true
			ORDER BY p.first_name, p.last_name
		")->execute();

		foreach ($all_agents as $agent) {
			$agent->loadHelper('Agent');
		}

		return $this->render('AdminBundle:Agents:list.html.twig', array(
			'all_agents' => $all_agents
		));
	}

	############################################################################
	# teams
	############################################################################

	public function teamsAction()
	{
		$all_teams = App::getOrm()->createQuery("
			SELECT t
			FROM DeskPRO:AgentTeam t
			ORDER BY t.name ASC
		")->execute();

		return $this->render('AdminBundle:Agents:teams.html.twig', array(
			'all_teams' => $all_teams
		));
	}



	############################################################################
	# edit
	############################################################################

	/**
	 * Edit a team
	 */
	public function editTeamAction($team_id)
	{
		if ($team_id) {
			$team = $this->getAgentTeamKeyOr404($team_id);
		} else {
			$team = new Entity\AgentTeam();
		}

		$current_ids = array();
		if ($team['id']) {
			$current_ids = App::getDb()->fetchAllCol("
				SELECT person_id
				FROM agent_team_members
				WHERE team_id = ?
			", array($team['id']));
		}

		if ($this->isPostRequest()) {
			$team['name'] = $this->in->getString('team.name');

			// Not using ORM here for sake of efficency
			$member_ids = $this->in->getCleanValueArray('team.members', 'uint', 'discard');

			App::getOrm()->beginTransaction();

			try {
				App::getOrm()->persist($team);
				APp::getOrm()->flush();

				$added_ids = array_diff($member_ids, $current_ids);
				$removed_ids = array_diff($current_ids, $member_ids);

				foreach ($added_ids as $id) {
					App::getDb()->insert('agent_team_members', array(
						'team_id' => $team['id'],
						'person_id' => $id
					));
				}
				foreach ($removed_ids as $id) {
					App::getDb()->delete('agent_team_members', array(
						'team_id' => $team['id'],
						'person_id' => $id
					));
				}

				App::getOrm()->flush();
				App::getOrm()->commit();

				$current_ids = $member_ids;

			} catch (\Exception $e) {
				App::getOrm()->rollback();
				throw $e;
			}
		}

		$form = new \Symfony\Component\Form\Form('team');
		$form_members = new \Symfony\Component\Form\ChoiceField('members', array(
			'multiple' => true,
			'choices' => App::getEntityRepository('DeskPRO:Person')->getAgentNames()
		));
		$form_members->setData($current_ids);
		$form->add($form_members);

		return $this->render('AdminBundle:Agents:edit-team.html.twig', array(
			'team' => $team,
			'form' => $form
		));
	}


	############################################################################

	/**
	 * @return Application\DeskPRO\Entity\AgentTeam
	 */
	protected function getAgentTeamKeyOr404($id)
	{
		$team = App::getEntityRepository('DeskPRO:AgentTeam')->find($id);
		if (!$team) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no team with ID $id");
		}

		return $team;
	}
}