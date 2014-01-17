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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\People\AgentNotifPrefs\Prefs as AgentNotifPrefs;
use Application\DeskPRO\People\AgentNotifPrefs\PrefsLoader as AgentNotifPrefsLoader;
use Application\DeskPRO\People\AgentNotifPrefs\PrefsTable as AgentNotifPrefsTable;
use Application\DeskPRO\People\AgentPermissions\PersonDbLoader as AgentPermsPersonDbLoader;
use Application\DeskPRO\People\Agents\AgentDelete;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class AgentsController extends AbstractController
{
	####################################################################################################################
	# list-agents
	####################################################################################################################

	public function listAgentsAction()
	{
		$data = array('agents' => array());

		foreach ($this->container->getAgentData()->getAgents() as $agent) {
			$agent_data = $agent->toApiData();
			$agent_data['is_online_now'] = $this->container->getAgentData()->isAgentOnline($agent);

			$data['agents'][] = $agent_data;
		}

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# list-deleted-agents
	####################################################################################################################

	public function listDeletedAgentsAction()
	{
		$deleted_agents = $this->em->getRepository('DeskPRO:Person')->getDeletedAgents();

		$data = array('agents' => array());

		foreach ($deleted_agents as $agent) {
			$data['agents'][] = $agent->toApiData();
		}

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# get-agent
	####################################################################################################################

	public function getAgentAction($id)
	{
		$agent = $this->container->getAgentData()->get($id);

		if (!$agent) {
			throw $this->createNotFoundException();
		}

		$agent_data = $agent->toApiData();
		$agent_data['teams'] = array();

		$agent->loadHelper('Agent');
		$agent->loadHelper('AgentTeam');
		$agent->loadHelper('AgentPermissions');
		$agent->loadHelper('PermissionsManager');

		foreach ($this->container->getAgentData()->getTeamsByIds($agent->getHelper('AgentTeam')->getAgentTeamIds()) as $t) {
			$agent_data['teams'][] = $t->toApiData();
		}

		$perm_loader = new AgentPermsPersonDbLoader($this->person, $this->em);

		return $this->createApiResponse(array(
			'agent'           => $agent_data,
			'perms'           => $perm_loader->getEffectivePermissions()->toArray(),
		));
	}


	####################################################################################################################
	# get-deleted-agent
	####################################################################################################################

	public function getDeletedAgentAction($id)
	{
		$agent = $this->em->find('DeskPRO:Person', $id);

		if (!$agent || !$agent->is_agent || !$agent->is_deleted) {
			throw $this->createNotFoundException();
		}

		$agent_data = $agent->toApiData();
		$agent_data['teams'] = array();

		$agent->loadHelper('Agent');
		$agent->loadHelper('AgentTeam');
		$agent->loadHelper('AgentPermissions');
		$agent->loadHelper('PermissionsManager');

		foreach ($this->container->getAgentData()->getTeamsByIds($agent->getHelper('AgentTeam')->getAgentTeamIds()) as $t) {
			$agent_data['teams'][] = $t->toApiData();
		}

		$perm_loader = new AgentPermsPersonDbLoader($this->person, $this->em);

		return $this->createApiResponse(array(
			'agent'           => $agent_data,
			'perms'           => $perm_loader->getEffectivePermissions()->toArray(),
		));
	}


	####################################################################################################################
	# save-agent
	####################################################################################################################

	public function saveAgentAction($id = null)
	{
		if ($id) {
			$is_new = false;
			$agent = $this->container->getAgentData()->get($id);

			if (!$agent) {
				throw $this->createNotFoundException();
			}
		} else {
			$is_new = true;
			$agent = new Person();

			$agent->is_user = true;
			$agent->is_confirmed = true;
			$agent->is_agent = true;
			$agent->can_agent = true;
			$agent->setPassword(Strings::random(20));

			$agent->addEmailAddressString($this->in->getString('agent.primary_email_address'));
		}

		$agent->setName($this->in->getString('agent.name'));
		$agent->override_display_name = $this->in->getString('agent.override_display_name') ?: '';
		$agent->can_admin   = $this->in->getBool('agent.zones.admin');
		$agent->can_billing = $agent->can_admin;
		$agent->can_reports = $this->in->getBool('agent.zones.reports');

		$this->em->persist($agent);
		$this->em->flush();

		if ($is_new) {
			// Send welcome email
			$message = $this->container->getMailer()->createMessage();
			$message->setToPerson($agent);
			$message->setTemplate('DeskPRO:emails_agent:agent-welcome.html.twig', array('agent' => $agent));
			$this->container->getMailer()->send($message);
		}

		if ($is_new) {
			return $this->createApiCreateResponse(array(
				'person_id' => $agent->id
			), $this->generateUrl('api_agents_get', array('id' => $agent->id), true));
		} else {
			return $this->createSuccessResponse();
		}
	}


	####################################################################################################################
	# reset-password
	####################################################################################################################

	public function resetPasswordAction($id)
	{
		$agent = $this->container->getAgentData()->get($id);

		if (!$agent) {
			throw $this->createNotFoundException();
		}

		$password = $this->in->getString('set_password');
		if (!$password) {
			$password = Strings::randomPronounceable(20);
		}

		$agent->setPassword($password);
		$this->em->persist($agent);
		$this->em->flush();

		// Clear possible active sessions
		$this->db->delete('sessions', array('person_id' => $agent->id));

		$did_email = false;
		if (!$this->in->getBool('skip_email')) {
			$did_email = $agent->getPrimaryEmailAddress();
			$message = $this->container->getMailer()->createMessage();
			$message->setToPerson($agent);
			$message->setTemplate('DeskPRO:emails_agent:password-reset-alert.html.twig', array(
				'agent'        => $agent,
				'performer'    => $this->person,
				'new_password' => $password,
			));
			$this->container->getMailer()->send($message);
		}

		return $this->createSuccessResponse(array(
			'emailed' => $did_email
		));
	}


	####################################################################################################################
	# delete-agent
	####################################################################################################################

	public function deleteAgentAction($id, $mode)
	{
		$agent = $this->container->getAgentData()->get($id);

		if (!$agent) {
			throw $this->createNotFoundException();
		}

		$deleter = new AgentDelete($agent, $this->em);

		switch ($mode) {
			case 'user':
				$deleter->deleteToUser();
				break;

			case 'delete':
				$deleter->softDelete();
				break;

			default:
				throw $this->createNotFoundException();
		}

		return $this->createSuccessResponse();
	}


	####################################################################################################################
	# undelete-agent
	####################################################################################################################

	public function undeleteAgentAction($id)
	{
		$agent = $this->em->find('DeskPRO:Person', $id);

		if (!$agent || !$agent->is_agent || !$agent->is_deleted) {
			throw $this->createNotFoundException();
		}

		$agent->is_deleted = false;
		$this->em->persist($agent);
		$this->em->flush();

		return $this->createSuccessResponse(array('person_id' => $agent->id));
	}


	####################################################################################################################
	# generate-login-token
	####################################################################################################################

	public function generateLoginTokenAction($id)
	{
		$agent = $this->container->getAgentData()->get($id);

		if (!$agent) {
			throw $this->createNotFoundException();
		}

		$tmp = TmpData::create('admin_agent_login', array(
			'admin_id' => $this->person->getId(),
			'agent_id' => $agent->id
		), '+5 minutes');
		$this->em->persist($tmp);
		$this->em->flush();

		return $this->createApiResponse(array(
			'login_token'    => $tmp->getCode(),
			'valid_until'    => $tmp->date_expire->format('Y-m-d H:i:s'),
			'valid_until_ts' => $tmp->date_expire->getTimestamp(),
		));
	}


	####################################################################################################################
	# get-notify-prefs-tables
	####################################################################################################################

	public function getNotifyPrefsAction($id)
	{
		if ($id) {
			$agent = $this->container->getAgentData()->get($id);

			if (!$agent) {
				throw $this->createNotFoundException();
			}

			$loader    = new AgentNotifPrefsLoader($agent, $this->em);
			$prefs     = $loader->getPrefs();
			$filters   = $this->em->getRepository('DeskPRO:TicketFilter')->getFiltersForPerson($agent);
		} else {
			$prefs = new AgentNotifPrefs();
			$filters = $this->em->getRepository('DeskPRO:TicketFilter')->getFiltersForPerson($this->person);
		}

		$table_gen = new AgentNotifPrefsTable($prefs, $this->container->getTranslator());

		$sys_filters = array();
		$custom_filters = array();

		foreach ($filters as $f) {
			if ($f->sys_name) {
				if (strpos($f->sys_name, '_w_hold') !== false || strpos($f->sys_name, 'archive_') === 0) continue;
				$sys_filters[] = $f;
			} else {
				$custom_filters[] = $f;
			}
		}

		$tables = array();
		$tables['sys_filters_email'] = $table_gen->buildSystemFiltersTable('email', $sys_filters);
		$tables['sys_filters_alert'] = $table_gen->buildSystemFiltersTable('alert', $sys_filters);

		if ($custom_filters) {
			$tables['custom_filters_email'] = $table_gen->buildCustomFiltersTable('email', $sys_filters);
			$tables['custom_filters_alert'] = $table_gen->buildCustomFiltersTable('alert', $sys_filters);
		}

		$tables['chat']     = $table_gen->buildChatTable();
		$tables['task']     = $table_gen->buildTaskTable();
		$tables['twitter']  = $table_gen->buildTwitterTable();
		$tables['feedback'] = $table_gen->buildFeedbackTable();
		$tables['publish']  = $table_gen->buildPublishTable();
		$tables['crm']      = $table_gen->buildCrmTable();
		$tables['account']  = $table_gen->buildAccountTable();

		return $this->createApiResponse(array(
			'subs'         => $tables,
			'sub_options'  => array(
				'email' => $prefs->getFilterNotifyPrefs('email'),
				'alert' => $prefs->getFilterNotifyPrefs('alert'),
			),
			'mention_mode' => $prefs->getEmailMentionMode(),
		));
	}
}