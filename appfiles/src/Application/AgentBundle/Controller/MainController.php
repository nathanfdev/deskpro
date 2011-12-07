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
        $titles['languages'] = App::getEntityRepository('DeskPRO:Language')->getTitles();

		// Person menu needs these
		$people_fields = $this->container->getSystemService('person_fields_manager')->getDisplayArray();
		$org_fields = $this->container->getSystemService('org_fields_manager')->getDisplayArray();

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

		// Countr code
		$phone_country_info = \Orb\Data\CountryCallingCodes::getData();

		if (App::getConfig('debug.raw_assets')) {
			$has_raw_assets = true;
		} else {
			$has_raw_assets = false;
		}

		// Auto-load chats in tabs if assigned to an agent
		$open_chats = $this->em->getRepository('DeskPRO:ChatConversation')->getOpenChatsForAgent($this->person);

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);
		$ticket_options['custom_ticket_fields'] = $custom_fields;

		// People stuff
		$ticket_options['people_organizations'] = App::getEntityRepository('DeskPRO:Organization')->getOrganizationNames();
		$people_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$ticket_options['custom_people_fields'] = $custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($people_field_defs);

		$cutoff = date('Y-m-d H:m:s', time() - App::getSetting('core.sessions_lifetime'));
		$online_agent_ids = App::getDb()->fetchAllCol("
			SELECT p.id
			FROM sessions s
			LEFT JOIN people AS p ON p.id = s.person_id
			WHERE p.is_agent = true AND s.date_last > ?
		", array($cutoff));

        return $this->render('AgentBundle:Main:index.html.twig', array(
			'has_raw_assets' => $has_raw_assets,
			'show_listpane' => $this->person->getPref('agent.ui.show-listpane'),
			'agent_names' => App::getEntityRepository('DeskPRO:Person')->getAgentNames(),
			'online_agent_ids' => $online_agent_ids,
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
			'phone_country_info' => $phone_country_info,
			'open_chats' => $open_chats,
		));
    }


	public function getCombinedSectionDataAction()
	{
		$data = array();

		foreach ($this->in->getCleanValueArray('section_ids', 'str_simple', 'discard') as $name) {
			switch ($name) {
				case 'tickets_section':
					$data[$name] = @json_decode($this->forward('AgentBundle:TicketSearch:getSectionData')->getContent());
					break;

				case 'chat_section':
					$data[$name] = @json_decode($this->forward('AgentBundle:UserChat:getSectionData')->getContent());
					break;

				case 'twitter_section':
					$data[$name] = @json_decode($this->forward('AgentBundle:Twitter:getSectionData')->getContent());
					break;

				case 'people_section':
					$data[$name] = @json_decode($this->forward('AgentBundle:PeopleSearch:getSectionData')->getContent());
					break;

				case 'ideas_section':
					$data[$name] = @json_decode($this->forward('AgentBundle:Ideas:getSectionData')->getContent());
					break;

				case 'publish_section':
					$data[$name] = @json_decode($this->forward('AgentBundle:Publish:getSectionData')->getContent());
					break;

				case 'tasks_section':
					$data[$name] = @json_decode($this->forward('AgentBundle:Task:getSectionData')->getContent());
					break;

				case 'deals_section':
					$data[$name] = @json_decode($this->forward('AgentBundle:Deal:getSectionData')->getContent());
					break;

				case 'agent_chat_section':
					$data[$name] = @json_decode($this->forward('AgentBundle:AgentChat:getSectionData')->getContent());
					break;
			}
		}

		return $this->createJsonResponse($data);
	}
}
