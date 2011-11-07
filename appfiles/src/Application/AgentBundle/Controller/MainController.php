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

		// Publish-info
		$idea_categories         = App::getEntityRepository('DeskPRO:IdeaCategory')->getCategoryHelper()->getFlatHierarchy();
		$idea_active_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getActiveCategories();
		$idea_closed_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getClosedCategories();

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

        return $this->render('AgentBundle:Main:index.html.twig', array(
			'has_raw_assets' => $has_raw_assets,
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
			'phone_country_info' => $phone_country_info,

			'idea_categories'      => $idea_categories,
			'idea_active_status_cats' => $idea_active_status_cats,
			'idea_closed_status_cats' => $idea_closed_status_cats,
			'open_chats' => $open_chats,
		));
    }
}
