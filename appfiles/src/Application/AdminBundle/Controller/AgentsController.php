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
			FROM DeskPRO:Person p
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

		return $this->render('AdminBundle:Agents:list.html.twig', array(
			'all_agents' => $all_agents,
			'all_teams' => $all_teams,
			'all_usergroups' => $all_usergroups
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
		$person = App::getEntityRepository('DeskPRO:Person')->find($person_id);
		$person->loadHelper('Agent');

		$agent_form_model = new AdminFormModel\EditAgent($person);

		$form = $this->get('form.factory')->create(new EditAgentType(), $agent_form_model);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$is_edited = true;

				App::getOrm()->beginTransaction();
				$agent_form_model->persist();
				App::getOrm()->flush();
				App::getOrm()->commit();

				// reset helper so it has correct ids etc
				$person->getHelperManager()->removeHelper('Agent');
				$person->loadHelper('Agent');

				$row_html = $this->renderView('AdminBundle:Agents:list-agents-row.html.twig', array('person' => $person));
			}
		}

		return $this->render('AdminBundle:Agents:edit-agent.html.twig', array(
			'person' => $person,
			'form' => $form->createView()
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

		$row_html = false;
		if ($this->in->getBool('process')) {
			$team['name'] = $this->in->getString('team.name');

			App::getOrm()->persist($team);
			App::getOrm()->flush();

			$row_html = $this->renderView('AdminBundle:Agents:list-teams-row.html.twig', array('team' => $team));
		}

		return $this->render('AdminBundle:Agents:edit-team.html.twig', array(
			'team' => $team,
			'row_html' => $row_html
		));
	}

	############################################################################
	# edit
	############################################################################

	public function editGroupAction($usergroup_id)
	{
		if (!$usergroup_id) {
			$usergroup = new Entity\Usergroup();
		} else {
			$usergroup = App::getEntityRepository('DeskPRO:Usergroup')->find($usergroup_id);
		}

		$row_html = false;
		if ($this->in->getBool('process')) {
			$usergroup['title'] = $this->in->getString('usergroup.title');
			$usergroup['note'] = $this->in->getString('usergroup.note');

			App::getOrm()->persist($usergroup);
			App::getOrm()->flush();

			$row_html = $this->renderView('AdminBundle:Agents:list-usergroups-row.html.twig', array('usergroup' => $usergroup));
		}

		$form = new Form\Form('usergroup');
		$form->add(new Form\TextField('title', array('data' => $usergroup['title'])));
		$form->add(new Form\TextareaField('note', array('data' => $usergroup['note'])));

		return $this->render('AdminBundle:Agents:edit-usergroup.html.twig', array(
			'usergroup' => $usergroup,
			'form'      => $form,
			'row_html'  => $row_html
		));
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
}
