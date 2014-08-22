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
 * @subpackage AgentBundle
 */
namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PrefNoticeSet;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Orb\Util\Util;

class MainController extends AbstractController
{
	public function requireRequestToken($action, $arguments = null)
	{
		if ($action == 'indexAction') {
			return false;
		}

		return parent::requireRequestToken($action, $arguments);
	}

    public function indexAction()
    {
		$this->person->loadPrefGroup('agent.ui');

		$last_message_id = $this->db->fetchColumn("
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
		$titles['organizations'] = $this->container->getDataService('Organization')->getOrganizationNames();
		$titles['usergroups']    = $this->container->getDataService('Usergroup')->getUsergroupNames();

		if ($this->container->getDataService('Language')->isMultiLang()) {
			$titles['languages'] = $this->container->getDataService('Language')->getTitles();
		}

		// Person menu needs these
		$people_fields = $this->container->getSystemService('person_fields_manager')->getDisplayArray();
		$org_fields = $this->container->getSystemService('org_fields_manager')->getDisplayArray();

		// Ticket options for search pane of tickets menu
		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		// Agent info
		$agents = $this->em->getRepository('DeskPRO:Person')->getAgents();
		$agent_teams = $this->em->getRepository('DeskPRO:AgentTeam')->findAll();

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
		$ticket_options['people_organizations'] = $this->em->getRepository('DeskPRO:Organization')->getOrganizationNames();
		$people_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$ticket_options['custom_people_fields'] = $custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($people_field_defs);

		$people_options = $titles;
		$people_options['custom_people_fields'] = $ticket_options['custom_people_fields'];

		$org_options = array(
			'custom_org_fields' => $this->container->getSystemService('org_fields_manager')->getDisplayArray()
		);

		$cutoff = date('Y-m-d H:i:s', time() - $this->container->getSetting('core_chat.agent_timeout'));
		$online_agent_ids = $this->db->fetchAllCol("
			SELECT p.id
			FROM sessions s
			LEFT JOIN people AS p ON p.id = s.person_id
			WHERE p.is_agent = true AND s.date_last > ?
		", array($cutoff));

		$agent_chat_depmap = $this->db->fetchAllGrouped("
			SELECT department_permissions.person_id, department_permissions.department_id
			FROM department_permissions
			WHERE
				department_permissions.person_id IS NOT NULL
				AND department_permissions.app = 'chat' AND department_permissions.value = 1
		", array(), 'person_id', null, 'department_id');

		foreach ($agent_chat_depmap as &$v) {
			if ($v) {
				$v = array_unique($v, \SORT_NUMERIC);
			}
		}

		$is_first_login = false;
		$is_first_login_name = false;

		if ($this->person->getPref('agent.first_login')) {
			$is_first_login = true;
			$is_first_login_name = $this->person->getPref('agent.first_login_name');
		}

		$chat_dep_ids = $this->person->getHelper('PermissionsManager')->get('Departments')->getAllowed('chat');

		\Application\DeskPRO\Chat\UserChat\AvailableTrigger::update();

		$version_notices = new PrefNoticeSet(
			$this->db,
			$this->person,
			'agent.ui.version_notices',
			DP_ROOT.'/docs/changelog/docs.php'
		);

		$ticket_snippet_cats = $this->em->getRepository('DeskPRO:TextSnippetCategory')->getCatsForAgent('tickets', $this->person);
		$chat_snippet_cats   = $this->em->getRepository('DeskPRO:TextSnippetCategory')->getCatsForAgent('chat', $this->person);

		/** @var \Application\DeskPRO\People\PasswordPolicyValidator $password_validator */
		$password_validator = App::$container->getSystemService('password_policy_validator');

		return $this->render('AgentBundle:Main:index.html.twig', array(
			'has_raw_assets'      => $has_raw_assets,
			'password_expired'    => $password_validator->isPasswordExpired($this->person),
			'show_listpane'       => $this->person->getPref('agent.ui.show-listpane'),
			'agent_names'         => $this->em->getRepository('DeskPRO:Person')->getAgentNames(),
			'online_agent_ids'    => $online_agent_ids,
			'agent_chat_depmap'   => $agent_chat_depmap,
			'chat_dep_ids'        => $chat_dep_ids,
			'is_demo'             => $this->in->checkIsset('show-demo-bar'),
			'last_message_id'     => $last_message_id,
			'js_debug'            => App::getConfig('debug.js', array()),
			'titles'              => $titles,
			'people_fields'       => $people_fields,
			'org_fields'          => $org_fields,
			'ticket_options'      => $ticket_options,
			'agents'              => $agents,
			'agent_teams'         => $agent_teams,
			'phone_country_info'  => $phone_country_info,
			'open_chats'          => $open_chats,
			'people_options'      => $people_options,
			'org_options'         => $org_options,
			'is_first_login'      => $is_first_login,
			'is_first_login_name' => $is_first_login_name,
			'timezones'           => \DateTimeZone::listIdentifiers(),
			'version_notices'     => $version_notices,
			'ticket_snippet_cats' => $ticket_snippet_cats,
			'chat_snippet_cats'   => $chat_snippet_cats,
		));
	}

	public function loadVersionNoticeAction($id)
	{
		$id = preg_replace('#[^a-zA-Z0-9_\-]#', 'x', $id);
		$target_dir = DP_ROOT.'/docs/changelog/' . $id;
		if (!is_dir($target_dir)) {
			throw $this->createNotFoundException();
		}

		$html = file_get_contents($target_dir . '/log.html');
		$html = Strings::extractRegexMatch('#<body>(.*?)</body>#s', $html, 1);

		if (preg_match_all('#<[^>]+src=(\'|")(.*?)(\'|")[^>]+>#', $html, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $m) {
				$attach_path = $target_dir . '/' . $m[2];
				if (file_exists($attach_path)) {
					if (Strings::getExtension($attach_path) == 'png') {
						$type = 'image/png;';
					} elseif (Strings::getExtension($attach_path) == 'gif') {
						$type = 'image/gif;';
					} else {
						$type = '';
					}
					$url = "data:{$type}base64," . base64_encode(file_get_contents($attach_path));

					$str = $m[0];
					$str = str_replace($m[2], $url, $str);
					$html = str_replace($m[0], $str, $html);
				}
			}
		}

		return $this->createResponse($html);
	}

	public function dismissVersionNoticeAction($id)
	{
		$version_notices = new PrefNoticeSet(
			$this->db,
			$this->person,
			'agent.ui.version_notices',
			DP_ROOT.'/docs/changelog/docs.php'
		);

		if ($id == 'ALL') {
			foreach ($version_notices->getWaitingIds() as $id) {
				$version_notices->dismiss($id);
			}
		} else {
			$version_notices->dismiss($id);
		}
		$version_notices->save();

		return $this->createJsonResponse(array('success' => true));
	}

	public function getCombinedSectionDataAction()
	{
		$data = array();

		foreach ($this->in->getCleanValueArray('section_ids', 'str_simple', 'discard') as $name) {
			switch ($name) {
				case 'tickets_section':
					$data[$name] = json_decode($this->forward('AgentBundle:TicketSearch:getSectionData')->getContent());
					break;

				case 'chat_section':
					$data[$name] = json_decode($this->forward('AgentBundle:UserChat:getSectionData')->getContent());
					break;

				case 'twitter_section':
					$data[$name] = json_decode($this->forward('AgentBundle:Twitter:getSectionData')->getContent());
					break;

				case 'people_section':
					$data[$name] = json_decode($this->forward('AgentBundle:PeopleSearch:getSectionData')->getContent());
					break;

				case 'feedback_section':
					$data[$name] = json_decode($this->forward('AgentBundle:Feedback:getSectionData')->getContent());
					break;

				case 'publish_section':
					$data[$name] = json_decode($this->forward('AgentBundle:Publish:getSectionData')->getContent());
					break;

				case 'tasks_section':
					$data[$name] = json_decode($this->forward('AgentBundle:Task:getSectionData')->getContent());
					break;

				case 'deals_section':
					$data[$name] = json_decode($this->forward('AgentBundle:Deal:getSectionData')->getContent());
					break;

				case 'twitter_section':
					$data[$name] = json_decode($this->forward('AgentBundle:Twitter:getSectionData')->getContent());
					break;

				case 'agent_chat_section':
					$data[$name] = json_decode($this->forward('AgentBundle:AgentChat:getSectionData')->getContent());
					break;
			}
		}

		return $this->createJsonResponse($data);
	}

	public function loadRecentTabsAction()
	{
		$recent_tabs = $this->db->fetchColumn("
			SELECT value_array
			FROM people_prefs
			WHERE person_id = ? AND name = 'agent.ui.recent_tabs_collection'
		", array($this->person->getId()));

		if ($recent_tabs) {
			$recent_tabs = @unserialize($recent_tabs);
		}

		if (!$recent_tabs) {
			$recent_tabs = array();
		} else {
			uasort($recent_tabs, function($a, $b) {
				if ($a[4] == $b[4]) {
					return 0;
				}
				return ($a[4] < $b[4]) ? -1 : 1;
			});
		}

		return $this->createJsonResponse(array_values($recent_tabs));
	}

	public function quickSearchAction()
	{
		$q = $this->in->getString('q');

        $results = array(
            'article'              => array(),
            'download'             => array(),
            'feedback'             => array(),
            'news'                 => array(),
            'ticket'               => array(),
            'person'               => array(),
            'person_related'       => array(),
            'organization'         => array(),
            'organization_related' => array(),
            'chat'                 => array()
        );

        $result_meta = array();
        $people_top  = false;

        if (!$q) {
            return $this->render('AgentBundle:Main:quicksearch.json.jsonphp', array(
                'q'           => $q,
                'router'      => App::getRouter(),
                'results'     => $results,
                'result_meta' => $result_meta,
                'people_top'  => $people_top,
            ));
        }

        if ($this->container->getSetting('elastica.enabled')) {
            return $this->searchInElasticsearch($q);
        } else {
            return $this->searchInDB($q);
        }
	}

    private function searchInElasticsearch($q)
    {
        $elasticsearch = $this->container->get('deskpro.search_manager.elasticsearch');
        $elasticsearch->setPersonContext($this->person);

        list($results, $result_meta, $people_top) = $elasticsearch->quickSearch($q);

		$return_results = array();

		if ($results) {
			foreach ($results as $type => $raw_rows) {
				$rows = array();
				foreach ($raw_rows as $r) {
					$rows[] = $r;
				}

				$return_results[] = array(
					'type'    => $type,
					'title'   => $type,
					'results' => $rows
				);
			}
		}

		foreach ($return_results as &$group) {
			$group['results'] = $this->renderSearchResults($group['type'], $group['results']);
		}

		return $this->createJsonResponse(array(
			'grouped_results' => $return_results
		));
    }

    private function searchInDB($q)
    {
        $doctrine = $this->container->get('deskpro.search_manager.doctrine');
        $doctrine->setPersonContext($this->person);

        list($results, $result_meta, $people_top) = $doctrine->quickSearch($q);

		$return_results = array();

		if ($results) {
			foreach ($results as $type => $raw_rows) {
				$rows = array();
				foreach ($raw_rows as $r) {
					if (is_object($r)) {
						$rows[] = $r;
					}
				}

				$return_results[] = array(
					'type'    => $type,
					'title'   => $type,
					'results' => $rows
				);
			}
		}

		foreach ($return_results as &$group) {
			$group['results'] = $this->renderSearchResults($group['type'], $group['results']);
		}

		return $this->createJsonResponse(array(
			'grouped_results' => $return_results
		));
    }


	/**
	 * @param string $type
	 * @param array $results
	 * @return array
	 */
	private function renderSearchResults($type, array $results)
	{
		$rows = array();

		$render_person = function(Person $person) {
			$data = array();
			$data['picture_url']    = $person->getPictureUrl();
			$data['picture_url_80'] = $person->getPictureUrl(80);
			$data['picture_url_64'] = $person->getPictureUrl(64);
			$data['picture_url_50'] = $person->getPictureUrl(50);
			$data['picture_url_45'] = $person->getPictureUrl(45);
			$data['picture_url_32'] = $person->getPictureUrl(32);
			$data['picture_url_22'] = $person->getPictureUrl(22);
			$data['picture_url_16'] = $person->getPictureUrl(16);
			foreach (array('id', 'first_name', 'last_name', 'name', 'display_name', 'override_display_name') as $k) {
				$data[$k] = $person[$k];
			}

			if ($person->primary_email) {
				$data['primary_email'] = array(
					'id'    => (int)$person->primary_email->id,
					'email' => $person->primary_email->email
				);
			} else {
				$data['primary_email'] = null;
			}
			return $data;
		};

		switch ($type) {
			case 'ticket':
				$ticket_display = new \Application\DeskPRO\Tickets\TicketResultsDisplay($results);
				$ticket_display->setPersonContext($this->person);

				foreach ($results as $r) {
					$ticket_info = array(
						'id'      => $r->id,
						'subject' => $r->subject,
						'status'  => $r->status,
						'urgency' => $r->urgency,
						'person'  => null,
						'agent'   => null
					);

					$agent= $ticket_display->getAgent($r);
					if ($agent) {
						$ticket_info['agent'] = $render_person($agent);
					}

					$person = $ticket_display->getPerson($r);
					if ($person) {
						$ticket_info['person'] = $render_person($person);
					}

					$rows[] = $ticket_info;
				}
				break;

			case 'person':
				foreach ($results as $r) {
					$rows[] = $render_person($r);
				}
				break;

			case 'organization':
				foreach ($results as $r) {
					$rows[] = array(
						'id'   => $r->id,
						'name' => $r->name
					);
				}
				break;

			case 'article':
			case 'news':
			case 'feedback':
			case 'download':
				foreach ($results as $r) {
					$rows[] = array(
						'id'    => $r->id,
						'title' => $r->title
					);
				}
			break;
		}

		return $rows;
	}
}
