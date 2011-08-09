<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;

class MainController extends AbstractController
{
    public function indexAction()
    {
		$this->person->loadPrefGroup('agent.ui');

		$last_message_id = App::getDb()->fetchColumn("
			SELECT id
			FROM client_messages
			ORDER BY id DESC
			LIMIT 1
		");
		if (!$last_message_id) {
			$last_message_id = -1;
		}

		// Used in some header menus for search options
        $titles = array();
        $titles['organizations'] = App::getEntityRepository('DeskPRO:Organization')->getOrganizationNames();
        $titles['usergroups'] = App::getEntityRepository('DeskPRO:Usergroup')->getUsergroupNames();
        $titles['locales'] = App::getEntityRepository('DeskPRO:Locale')->getLocaleNames();

		// Person menu needs these
		$people_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$people_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($people_field_defs);

		$org_field_defs = App::getApi('custom_fields.organizations')->getEnabledFields();
		$org_fields = App::getApi('custom_fields.organizations')->getFieldsDisplayArray($org_field_defs);

		// Ticket options for search pane of tickets menu
		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		$state_pref = App::getOrm()->getRepository('DeskPRO:PersonPref')->find(array('person' => $this->person['id'], 'name' => 'agent.ui.state'));
		$restore_state = null;
		if ($state_pref) {
			$restore_state = $state_pref['value'];
		}

		// Agent info
		$agent_info = array();
		$agents = App::getEntityRepository('DeskPRO:Person')->getAgents();

		$agent_teams = App::getEntityRepository('DeskPRO:AgentTeam')->findAll();
        return $this->render('AgentBundle:Main:index.html.twig', array(
			'show_listpane' => $this->person->getPref('agent.ui.show-listpane'),
			'agent_names' => App::getEntityRepository('DeskPRO:Person')->getAgentNames(),
			'is_demo' => $this->in->checkIsset('show-demo-bar'),
			'last_message_id' => $last_message_id,
			'js_debug' => App::getConfig('debug.js', array()),
			'titles' => $titles,
			'people_fields' => $people_fields,
			'org_fields' => $org_fields,
			'ticket_options' => $ticket_options,
			'restore_state' => $restore_state,
			'agents' => $agents,
			'agent_teams' => $agent_teams,
		));
    }
}
