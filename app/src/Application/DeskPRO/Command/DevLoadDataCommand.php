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
 */

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

// Usage: php cmd.php dpdev:load-data --count=# --types=a,b,c
// Count defaults to 100, types must be explicitly specified. If no
// types are specified, a list of available ones is given. If you want
// to insert into everythign, use --types=*

class DevLoadDataCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('dpdev:load-data');
		$this->addOption('count', null, InputOption::VALUE_REQUIRED, 'Amount of data for each type to create', 0);
		$this->addOption('types', null, InputOption::VALUE_REQUIRED, 'Comma separated list of data types (* for all)', '');
	}

	protected $_data_cache = array();

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if (!dp_get_config('debug.dev')) {
			$output->write("Dev mode is not enabled");
			return 1;
		}

		// todo: triggers, escalations, banned emails, banned IPs, agent teams, perm groups

		$available_types = array(
			'org_field', 'organization', 'usergroup',
			'person_field', 'person',
			'sla', 'ticket_department', 'ticket_field', 'ticket', 'ticket_filter',
			'ticket_snippet_category', 'ticket_snippet', 'ticket_macro',
			'chat_snippet_category', 'chat_snippet',
			'chat_department',
			'feedback_status', 'feedback_type', 'feedback',
			'article_field', 'article_category','article',
			'news_category','news',
			'download_category', 'download',
			'glossary',
			'task'
		);
		// agent creation?, chat

		$amount = intval($input->getOption('count'));
		if ($amount <= 0) {
			$amount = 100;
		}

		$type_input = $input->getOption('types');
		if ($type_input === '*') {
			$types = $available_types;
		} else {
			$types = preg_split('/,\s*/', $type_input, -1, PREG_SPLIT_NO_EMPTY);
		}

		if (!$types) {
			sort($available_types);
			echo "No types given. Cannot continue. Available types:\n\t" . implode(', ', $available_types) . "\n";
			return;
		}

		$db = App::getDb();

		$this->_data_cache['agents'] = App::getEntityRepository('DeskPRO:Person')->getAgents();
		$this->_data_cache['agent_teams'] = App::getEntityRepository('DeskPRO:AgentTeam')->getTeams();
		$this->_data_cache['random_people_ids'] = $db->fetchAllCol('
			SELECT id
			FROM people
			WHERE is_agent = 0
			ORDER BY RAND()
			LIMIT 1000
		');
		if (!$this->_data_cache['random_people_ids']) {
			$this->_data_cache['random_people_ids'] = array_keys($this->_data_cache['agents']);
		}

		$this->_data_cache['random_org_ids'] = $db->fetchAllCol('
			SELECT id
			FROM organizations
			ORDER BY RAND()
			LIMIT 1000
		');

		$total = count($types);

		// loop through all to keep the order the same as we create some dependent stuff first
		foreach ($available_types AS $i => $type) {
			if (!in_array($type, $types)) {
				continue;
			}

			$start = microtime(true);
			$count = $i+1;

			$method = '_load' . str_replace('_', '', $type);
			if (!method_exists($this, $method)) {
				echo str_pad(
					sprintf("[%02d/%02d] %s is unknown, skipping.", $count, $total, $type),
					60
				) . "\n";
				continue;
			}

			echo str_pad(
				sprintf("[%02d/%02d] %s... 0/%d", $count, $total, $type, $amount),
				60
			) . "\r";

			for ($i = 0; $i < $amount; $i++) {
				$this->$method();

				if ($i > 0 && $i % 10 == 0) {
					$time = microtime(true) - $start;
					echo str_pad(
						sprintf("[%02d/%02d] %s... %d/%d (%.2f seconds)", $count, $total, $type, $i, $amount, $time),
						60
					) . "\r";
				}
			}

			$time = microtime(true) - $start;
			echo str_pad(
				sprintf("[%02d/%02d] %s... completing (%.2f seconds)", $count, $total, $type, $time),
				60
			) . "\r";

			App::getOrm()->flush();

			$complete_method = '_complete' . str_replace('_', '', $type);
			if (method_exists($this, $complete_method)) {
				$this->$complete_method();
			}

			$time = microtime(true) - $start;
			echo str_pad(
				sprintf("[%02d/%02d] %s... Done, inserted %d (%.2f seconds)", $count, $total, $type, $amount, $time),
				60
			) . "\n";
		}

		echo "\nData load completed.\n";
	}

	protected function _loadOrgField()
	{
		$org_field = new Entity\CustomDefOrganization();
		$org_field->title = $this->_getRandomText(2);
		$org_field->description = $this->_getRandomText(rand(1, 10));
		$org_field->handler_class = 'Application\DeskPRO\CustomFields\Handler\Text';

		App::getOrm()->persist($org_field);
	}

	protected function _loadOrganization()
	{
		if (!isset($this->_data_cache['usergroups'])) {
			$this->_data_cache['usergroups'] = App::getEntityRepository('DeskPRO:Usergroup')->findAll();
		}
		if (!isset($this->_data_cache['org_fields'])) {
			$this->_data_cache['org_fields'] = App::getEntityRepository('DeskPRO:CustomDefOrganization')->findAll();
		}

		$org = new Entity\Organization();
		$org->name = $this->_getRandomText(rand(1, 3));

		if (rand(1, 4) == 1) {
			$count = rand(1, 3);
			for ($i = 0; $i < $count; $i++) {
				$key = array_rand($this->_data_cache['usergroups']);
				if (!$org->usergroups->contains($this->_data_cache['usergroups'][$key])) {
					$org->usergroups->add($this->_data_cache['usergroups'][$key]);
				}
			}
		}

		foreach ($this->_data_cache['org_fields'] AS $field) {
			if ($field->getTypeName() == 'text') {
				$data = new Entity\CustomDataOrganization();
				$data->field = $field;
				$data->root_field = $field;
				$data->value = 0;
				$data->input = $this->_getRandomText(rand(1, 5));
				$org->addCustomData($data);
			}
		}

		// todo: contact data

		App::getOrm()->persist($org);
		$this->_applyLabels($org);
	}

	protected function _completeOrganization()
	{
		$this->_data_cache['random_org_ids'] = App::getDb()->fetchAllCol('
			SELECT id
			FROM organizations
			ORDER BY RAND()
			LIMIT 1000
		');
	}

	protected function _loadUsergroup()
	{
		$usergroup = new Entity\Usergroup();
		$usergroup->title = $this->_getRandomText(2);
		$usergroup->note = $this->_getRandomText(rand(1, 5));

		App::getOrm()->persist($usergroup);
	}

	protected function _loadPersonField()
	{
		$field = new Entity\CustomDefPerson();
		$field->title = $this->_getRandomText(2);
		$field->description = $this->_getRandomText(rand(1, 10));
		$field->handler_class = 'Application\DeskPRO\CustomFields\Handler\Text';

		App::getOrm()->persist($field);
	}

	protected function _loadPerson()
	{
		if (!isset($this->_data_cache['usergroups'])) {
			$this->_data_cache['usergroups'] = App::getEntityRepository('DeskPRO:Usergroup')->findAll();
		}
		if (!isset($this->_data_cache['person_fields'])) {
			$this->_data_cache['person_fields'] = App::getEntityRepository('DeskPRO:CustomDefPerson')->findAll();
		}

		$person = new Entity\Person();
		$person->name = $this->_getRandomText(2);
		$person->setEmail($this->_getRandomText(1) . microtime(true) . '@example.com', true);
		if (rand(1, 3) == 1) {
			$person->setOrganizationId($this->_getRandomFromCache('random_org_ids'));
		}

		if (rand(1, 4) == 1) {
			$count = rand(1, 3);
			for ($i = 0; $i < $count; $i++) {
				$key = array_rand($this->_data_cache['usergroups']);
				if (!$person->usergroups->contains($this->_data_cache['usergroups'][$key])) {
					$person->usergroups->add($this->_data_cache['usergroups'][$key]);
				}
			}
		}

		foreach ($this->_data_cache['person_fields'] AS $field) {
			if ($field->getTypeName() == 'text') {
				$data = new Entity\CustomDataPerson();
				$data->field = $field;
				$data->root_field = $field;
				$data->value = 0;
				$data->input = $this->_getRandomText(rand(1, 5));
				$person->addCustomData($data);
			}
		}

		// todo: contact data, secondary emails

		App::getOrm()->persist($person);
		$this->_applyLabels($person);
	}

	protected function _completePerson()
	{
		$this->_data_cache['random_people_ids'] = App::getDb()->fetchAllCol('
			SELECT id
			FROM people
			WHERE is_agent = 0
			ORDER BY RAND()
			LIMIT 1000
		');
	}

	protected function _loadSla()
	{
		$sla = new Entity\Sla();
		$sla->title = $this->_getRandomText(rand(1, 4));
		$types = array(
			\Application\DeskPRO\Entity\Sla::TYPE_FIRST_RESPONSE,
			\Application\DeskPRO\Entity\Sla::TYPE_RESOLUTION,
			\Application\DeskPRO\Entity\Sla::TYPE_WAITING_TIME
		);
		$sla->sla_type = $types[array_rand($types)];
		$sla->active_time = \Orb\Util\WorkHoursSet::ACTIVE_24X7;
		$sla->apply_type = rand(1, 6) == 1 ? 'all' : 'manual';

		App::getOrm()->persist($sla);
		App::getOrm()->flush();

		$warning_trigger = new Entity\TicketTrigger();
		$warning_trigger->title = $sla->title . " - SLA Warning";
		$warning_trigger->event_trigger = 'sla.warning';
		$warning_time = rand(30, 500);
		$time = $warning_time . ' minutes';
		$warning_trigger->setEventTriggerOption('time', $time);
		$warning_trigger->terms = array(
			array('type' => 'sla_status', 'op' => 'is', 'options' => array('sla_status' => 'warn', 'sla_id' => $sla->id)),
		);
		$warning_trigger->actions = array(
			array('type' => 'recalculate_sla_status', 'options' => array())
		);

		App::getOrm()->persist($warning_trigger);

		$fail_trigger = new Entity\TicketTrigger();
		$fail_trigger->title = $sla->title . " - SLA Failure";
		$fail_trigger->event_trigger = 'sla.fail';
		$time = rand($warning_time, 600) . ' minutes';
		$fail_trigger->setEventTriggerOption('time', $time);
		$fail_trigger->terms = array(
			array('type' => 'sla_status', 'op' => 'is', 'options' => array('sla_status' => 'fail', 'sla_id' => $sla->id)),
		);
		$fail_trigger->actions = array(
			array('type' => 'recalculate_sla_status', 'options' => array())
		);

		App::getOrm()->persist($fail_trigger);

		$sla->warning_trigger = $warning_trigger;
		$sla->fail_trigger = $fail_trigger;
		App::getOrm()->persist($sla);
	}

	protected function _loadTicketField()
	{
		$field = new Entity\CustomDefTicket();
		$field->title = $this->_getRandomText(2);
		$field->description = $this->_getRandomText(rand(1, 10));
		$field->handler_class = 'Application\DeskPRO\CustomFields\Handler\Text';

		App::getOrm()->persist($field);
	}

	protected function _loadTicketDepartment()
	{
		$department = new Entity\Department();
		$department->title = $this->_getRandomText(rand(2, 4));
		$department->is_tickets_enabled = true;
		$department->is_chat_enabled = false;
		$department->display_order = rand(1, 1000000);
		if (!empty($this->_data_cache['ticket_department_parent'])) {
			$department->parent = $this->_data_cache['ticket_department_parent'];
		}
		
		App::getOrm()->persist($department);
		App::getOrm()->flush($department);

		$dep_perms = array();

		foreach ($this->_data_cache['agents'] AS $agent) {
			$dep_perms[] = array(
				'department_id' => $department->getId(),
				'usergroup_id' => null,
				'person_id' => $agent->getId(),
				'app' => 'tickets',
				'name' => 'full',
				'value' => 1
			);
			$dep_perms[] = array(
				'department_id' => $department->getId(),
				'usergroup_id' => null,
				'person_id' => $agent->getId(),
				'app' => 'tickets',
				'name' => 'assign',
				'value' => 1
			);
		}

		$dep_perms[] = array(
			'department_id' => $department->getId(),
			'usergroup_id' => 1,
			'person_id' => null,
			'app' => 'tickets',
			'name' => 'full',
			'value' => 1
		);

		App::getDb()->batchInsert('department_permissions', $dep_perms);

		if (empty($this->_data_cache['ticket_department_parent'])) {
			$this->_data_cache['ticket_department_parent'] = $department;
		}
	}

	protected function _loadTicket()
	{
		if (!isset($this->_data_cache['ticket_departments'])) {
			$this->_data_cache['ticket_departments'] = App::getEntityRepository('DeskPRO:Department')->getChildDepartments('ticket');
		}
		if (!isset($this->_data_cache['ticket_fields'])) {
			$this->_data_cache['ticket_fields'] = App::getEntityRepository('DeskPRO:CustomDefTicket')->findAll();
		}

		$ticket = new Entity\Ticket(false);
		$ticket->subject = $this->_getRandomText(rand(2, 6));
		$ticket->setPersonId($this->_getRandomFromCache('random_people_ids'));
		if (rand(0, 2) == 0) {
			$ticket->setAgentId($this->_getRandomFromCache('agents')->id);
		}
		$ticket->setDepartment($this->_getRandomFromCache('ticket_departments'));
		$ticket->creation_system = Entity\Ticket::CREATED_WEB_API;
		$ticket->status = (rand(0, 1) ? 'awaiting_user' : 'awaiting_agent');
		$ticket->language = $ticket->person->getRealLanguage();

		$message = new Entity\TicketMessage();
		$message->person = $ticket->person;
		$message->creation_system = Entity\TicketMessage::CREATED_WEB_API;
		$message->setMessageText($this->_getRandomText(rand(50, 500)));

		foreach ($this->_data_cache['ticket_fields'] AS $field) {
			if ($field->getTypeName() == 'text') {
				$data = new Entity\CustomDataTicket();
				$data->field = $field;
				$data->root_field = $field;
				$data->value = 0;
				$data->input = $this->_getRandomText(rand(1, 5));
				$ticket->addCustomData($data);
			}
		}

		$ticket->addMessage($message);

		// todo: attachments

		$message_count = rand(0, 10);
		if ($message_count > 0) {
			for ($i = 0; $i < $message_count; $i++) {
				$is_agent = $ticket->agent && rand(0, 1);
				$message = new Entity\TicketMessage();
				$message->person = $is_agent ? $ticket->agent : $ticket->person;
				$message->is_agent_note = ($is_agent && rand(0, 1));
				$message->creation_system = Entity\TicketMessage::CREATED_WEB_API;
				$message->setMessageText($this->_getRandomText(rand(50, 500)));
			}
		}

		App::getOrm()->persist($ticket);
		$this->_applyLabels($ticket);
	}

	protected function _loadTicketFilter()
	{
		$filter = new Entity\TicketFilter();
		$filter->title = $this->_getRandomText(2);
		$filter->is_global = true;
		$filter->terms = array(
			array('type' => 'status', 'op' => 'is', 'options' => array('status' => 'awaiting_agent'))
		);

		App::getOrm()->persist($filter);
	}

	protected function _loadTicketMacro()
	{
		$macro = new Entity\TicketMacro();
		$macro->title = $this->_getRandomText(rand(2, 4));
		$macro->is_global = (rand(0, 1) == 1);
		$macro->is_enabled = true;
		$macro->actions = array(
			array('type' => 'agent', 'options' => array('agent' => '-1'))
		);
		$agent_id = array_rand($this->_data_cache['agents']);
		$macro->person = $this->_data_cache['agents'][$agent_id];

		App::getOrm()->persist($macro);
	}

	protected function _loadTicketSnippetCategory()
	{
		$category = new Entity\TicketSnippetCategory();
		$category->is_global = true;
		$category->title = $this->_getRandomText(rand(1, 4));
		$agent_id = array_rand($this->_data_cache['agents']);
		$category->person = $this->_data_cache['agents'][$agent_id];

		App::getOrm()->persist($category);
	}

	protected function _loadTicketSnippet()
	{
		if (!isset($this->_data_cache['ticket_snippet_categories'])) {
			$this->_data_cache['ticket_snippet_categories'] = App::getEntityRepository('DeskPRO:TicketSnippetCategory')->findAll();
		}

		$snippet = new Entity\TicketSnippet();
		$snippet->title = $this->_getRandomText(rand(2, 5));
		$text = $this->_getRandomText(rand(10, 200));
		$snippet->snippet = $text;
		$snippet->snippet_html = '<p>' . $text . '</p>';

		$category_id = array_rand($this->_data_cache['ticket_snippet_categories']);
		$snippet->category = $this->_data_cache['ticket_snippet_categories'][$category_id];
		$agent_id = array_rand($this->_data_cache['agents']);
		$snippet->person = $this->_data_cache['agents'][$agent_id];

		App::getOrm()->persist($snippet);
	}

	protected function _loadChatSnippetCategory()
	{
		$category = new Entity\TextSnippetCategory();
		$category->typename = 'chat';
		$category->is_global = true;
		$category->title = $this->_getRandomText(rand(1, 4));
		$agent_id = array_rand($this->_data_cache['agents']);
		$category->person = $this->_data_cache['agents'][$agent_id];

		App::getOrm()->persist($category);
	}

	protected function _loadChatSnippet()
	{
		if (!isset($this->_data_cache['chat_snippet_categories'])) {
			$this->_data_cache['chat_snippet_categories'] = App::getEntityRepository('DeskPRO:TextSnippetCategory')->getAllByType('chat');
		}

		$snippet = new Entity\TextSnippet();
		$snippet->title = $this->_getRandomText(rand(2, 5));
		$snippet->snippet = $this->_getRandomText(rand(10, 200));

		$category_id = array_rand($this->_data_cache['chat_snippet_categories']);
		$snippet->category = $this->_data_cache['chat_snippet_categories'][$category_id];
		$agent_id = array_rand($this->_data_cache['agents']);
		$snippet->person = $this->_data_cache['agents'][$agent_id];

		App::getOrm()->persist($snippet);
	}

	protected function _loadChatDepartment()
	{
		$department = new Entity\Department();
		$department->title = $this->_getRandomText(rand(2, 4));
		$department->is_tickets_enabled = false;
		$department->is_chat_enabled = true;
		$department->display_order = rand(1, 1000000);
		if (!empty($this->_data_cache['chat_department_parent'])) {
			$department->parent = $this->_data_cache['chat_department_parent'];
		}

		App::getOrm()->persist($department);
		App::getOrm()->flush($department);

		$dep_perms = array();

		foreach ($this->_data_cache['agents'] AS $agent) {
			$dep_perms[] = array(
				'department_id' => $department->getId(),
				'usergroup_id' => null,
				'person_id' => $agent->getId(),
				'app' => 'chat',
				'name' => 'full',
				'value' => 1
			);
		}

		$dep_perms[] = array(
			'department_id' => $department->getId(),
			'usergroup_id' => 1,
			'person_id' => null,
			'app' => 'chat',
			'name' => 'full',
			'value' => 1
		);

		App::getDb()->batchInsert('department_permissions', $dep_perms);

		if (empty($this->_data_cache['chat_department_parent'])) {
			$this->_data_cache['chat_department_parent'] = $department;
		}
	}

	protected function _loadFeedbackType()
	{
		$category = new Entity\FeedbackCategory();
		$category->title = $this->_getRandomText(rand(1, 4));
		$category->display_order = rand(1, 1000000);

		App::getOrm()->persist($category);
		App::getOrm()->flush();

		App::getDb()->insert('feedback_category2usergroup', array(
			'category_id'  => $category->getId(),
			'usergroup_id' => 1
		));
	}

	protected function _loadFeedbackStatus()
	{
		$category = new Entity\FeedbackStatusCategory();
		$category->title = $this->_getRandomText(rand(1, 4));
		$category->display_order = rand(1, 1000000);
		$category->status_type = rand(1, 2) == 1 ? 'active' : 'closed';

		App::getOrm()->persist($category);
	}

	protected function _loadFeedback()
	{
		if (!isset($this->_data_cache['feedback_types'])) {
			$this->_data_cache['feedback_types'] = App::getEntityRepository('DeskPRO:FeedbackCategory')->findAll();
		}
		if (!isset($this->_data_cache['feedback_statuses'])) {
			$this->_data_cache['feedback_statuses'] = App::getEntityRepository('DeskPRO:FeedbackStatusCategory')->findAll();
		}

		$feedback = new Entity\Feedback();
		$feedback->title = $this->_getRandomText(rand(2, 6));
		$feedback->content = htmlspecialchars($this->_getRandomText(30));
		if (rand(1, 3) == 1) {
			$feedback->setStatusCode('new');
		} else {
			$status = $this->_getRandomFromCache('feedback_statuses');
			$feedback->setStatusCode($status->status_type . '.' . $status->id);
		}
		$feedback->category = $this->_getRandomFromCache('feedback_types');
		$feedback->person = $this->_getPerson($this->_getRandomFromCache('random_people_ids'));

		// todo: attachments, user categories, validation?, comments

		App::getOrm()->persist($feedback);
		$this->_applyLabels($feedback);
	}

	protected function _loadArticleField()
	{
		$field = new Entity\CustomDefArticle();
		$field->title = $this->_getRandomText(2);
		$field->description = $this->_getRandomText(rand(1, 10));
		$field->handler_class = 'Application\DeskPRO\CustomFields\Handler\Text';

		App::getOrm()->persist($field);
	}

	protected function _loadArticleCategory()
	{
		$category = new Entity\ArticleCategory();
		$category->title = $this->_getRandomText(rand(1, 4));
		$category->display_order = rand(1, 1000000);

		App::getOrm()->persist($category);
		App::getOrm()->flush();

		App::getDb()->insert('article_category2usergroup', array(
			'category_id'  => $category->getId(),
			'usergroup_id' => 1
		));
	}

	protected function _loadArticle()
	{
		if (!isset($this->_data_cache['article_categories'])) {
			$this->_data_cache['article_categories'] = App::getEntityRepository('DeskPRO:ArticleCategory')->findAll();
		}
		if (!isset($this->_data_cache['article_fields'])) {
			$this->_data_cache['article_fields'] = App::getEntityRepository('DeskPRO:CustomDefArticle')->findAll();
		}

		$article = new Entity\Article();
		$article->title = $this->_getRandomText(rand(2, 6));
		$article->content = htmlspecialchars($this->_getRandomText(30));
		$article->setStatus('published');
		$article->addToCategory($this->_getRandomFromCache('article_categories'));
		$article->person = $this->_getPerson($this->_getRandomFromCache('random_people_ids'));

		foreach ($this->_data_cache['article_fields'] AS $field) {
			if ($field->getTypeName() == 'text') {
				$data = new Entity\CustomDataArticle();
				$data->field = $field;
				$data->root_field = $field;
				$data->value = 0;
				$data->input = $this->_getRandomText(rand(1, 5));
				$article->addCustomData($data);
			}
		}

		// todo: products, attachments, comments (with validation), varied statuses

		App::getOrm()->persist($article);
		$this->_applyLabels($article);
	}

	protected function _loadNewsCategory()
	{
		$category = new Entity\NewsCategory();
		$category->title = $this->_getRandomText(rand(1, 4));
		$category->display_order = rand(1, 1000000);

		App::getOrm()->persist($category);
		App::getOrm()->flush();

		App::getDb()->insert('news_category2usergroup', array(
			'category_id'  => $category->getId(),
			'usergroup_id' => 1
		));
	}

	protected function _loadNews()
	{
		if (!isset($this->_data_cache['news_categories'])) {
			$this->_data_cache['news_categories'] = App::getEntityRepository('DeskPRO:NewsCategory')->findAll();
		}

		$news = new Entity\News();
		$news->title = $this->_getRandomText(rand(2, 6));
		$news->content = htmlspecialchars($this->_getRandomText(30));
		$news->setStatus('published');
		$news->category = $this->_getRandomFromCache('news_categories');
		$news->person = $this->_getPerson($this->_getRandomFromCache('random_people_ids'));

		// todo: attachments, comments (with validation)

		App::getOrm()->persist($news);
		$this->_applyLabels($news);
	}

	protected function _loadDownloadCategory()
	{
		$category = new Entity\DownloadCategory();
		$category->title = $this->_getRandomText(rand(1, 4));
		$category->display_order = rand(1, 1000000);

		App::getOrm()->persist($category);
		App::getOrm()->flush();

		App::getDb()->insert('download_category2usergroup', array(
			'category_id'  => $category->getId(),
			'usergroup_id' => 1
		));
	}

	protected function _loadDownload()
	{
		if (!isset($this->_data_cache['download_categories'])) {
			$this->_data_cache['download_categories'] = App::getEntityRepository('DeskPRO:DownloadCategory')->findAll();
		}

		$download = new Entity\Download();
		$download->title = $this->_getRandomText(rand(2, 6));
		$download->content = htmlspecialchars($this->_getRandomText(30));
		$download->setStatus('published');
		$download->category = $this->_getRandomFromCache('download_categories');
		$download->person = $this->_getPerson($this->_getRandomFromCache('random_people_ids'));

		// todo: attachments, comments (with validation)

		App::getOrm()->persist($download);
		$this->_applyLabels($download);
	}

	protected function _loadGlossary()
	{
		$def = new Entity\GlossaryWordDefinition();
		$def->definition = $this->_getRandomText(rand(5, 10));
		$word_count = rand(1, 5);
		for ($i = 0; $i < $word_count; $i++) {
			$start = chr(rand(64, 90)); // @ and A-Z
			$def->addWord($start . $this->_getRandomText(1));
		}

		if (count($def->words)) {
			App::getOrm()->persist($def);
			App::getOrm()->flush(); // need to flush each as might get a dupe error
		}
	}

	protected function _loadTask()
	{
		$task = new Entity\Task();
		$task->title = $this->_getRandomText(rand(2, 8));
		$task->person = $this->_getRandomFromCache('agents');
		$task->setVisibility(rand(1, 3) == 1 ? 0 : 1);
		if (rand(0, 1)) {
			$task->due_date = time() + rand(10000, 10000000);
		}
		if (rand(1, 3) == 1) {
			$task->assigned_agent = $this->_getRandomFromCache('agents');
		} else if (rand(1, 3) == 1) {
			$task->assigned_agent_team = $this->_getRandomFromCache('agent_teams');
		}

		$task->setCompleted(rand(1, 3) == 1);

		// todo: comments, ticket linking

		App::getOrm()->persist($task);
		$this->_applyLabels($task);
	}

	protected function _getRandomFromCache($key)
	{
		if (!isset($this->_data_cache[$key]) || empty($this->_data_cache[$key])) {
			return null;
		}

		$rand = array_rand($this->_data_cache[$key]);
		return $this->_data_cache[$key][$rand];
	}

	protected function _applyLabels($entity)
	{
		if (!method_exists($entity, 'getLabelManager')) {
			return;
		}

		/** @var $manager \Application\DeskPRO\Labels\LabelManager */
		$manager = $entity->getLabelManager();

		$labels = rand(0, 4);
		if ($labels) {
			App::getOrm()->flush(); // must generate an ID first

			for ($i = 0; $i < $labels; $i++) {
				$label = $manager->addLabel($this->_getRandomText(1));
				App::getOrm()->persist($label);
			}
		}
	}

	protected $_words = null;

	protected function _getRandomText($word_length = 1)
	{
		if (!is_array($this->_words)) {
			$this->_words = explode(' ', 'Lorem ipsum dolor sit amet consectetur adipiscing elit Morbi ac semper lorem Mauris ut suscipit leo Suspendisse orci sem consequat a venenatis quis volutpat sit amet lorem Nulla sed sodales leo Duis erat magna commodo nec consectetur quis rhoncus ac arcu Suspendisse egestas metus id nunc interdum nec volutpat orci laoreet Ut porttitor nisi vel urna congue eleifend Fusce semper justo sit amet elit tempor ut ultrices neque pharetra In at tellus at dolor consectetur dapibus in eleifend est Aenean sed neque id sapien aliquet semper id at velit Nullam laoreet est vitae dui pulvinar consectetur Aenean ipsum ipsum convallis ac pellentesque nec ullamcorper sit amet ipsum Fusce accumsan orci in bibendum ornare dolor nunc condimentum massa eget aliquam lectus tortor sed est Proin tempor quam congue mi tempus vitae cursus orci interdum Aliquam aliquet vulputate cursus Etiam hendrerit lorem vitae ipsum lacinia feugiat Fusce ornare purus et felis placerat ut venenatis nisl dignissim Mauris sed lacus nunc Curabitur et metus quis orci molestie sodales Suspendisse interdum cursus ullamcorper Donec pretium consequat lacus ac condimentum Fusce lacinia faucibus urna eu varius Etiam volutpat porta nisi in euismod sapien consequat vitae Ut feugiat porttitor dui nec vehicula Suspendisse sed nibh id leo euismod scelerisque Praesent malesuada sagittis dui et iaculis ante vulputate id Quisque a risus nec orci eleifend volutpat sit amet sit amet lectus Aliquam ut felis felis a mattis turpis Nulla eget orci lorem id rutrum orci Donec neque nisl tristique ac fringilla vel ullamcorper vitae erat Praesent erat metus tristique in gravida id tempus fringilla diam Integer vitae aliquet nulla Sed dictum lectus ac sem rhoncus et laoreet augue volutpat Ut venenatis laoreet mauris non pulvinar Etiam lacinia augue vel elit facilisis quis molestie sapien congue Praesent eu lacus justo vitae iaculis libero Curabitur a nibh massa Aenean sed dui orci Suspendisse vehicula nibh eu dictum bibendum lorem nisl congue felis ac dictum mauris nisl vitae orci Phasellus et turpis a massa tempor sodales eget eget quam Cras ut purus nisl sit amet ultricies lacus Nunc congue molestie accumsan Sed ut volutpat dui Donec sit amet nunc rhoncus risus convallis adipiscing Aenean tincidunt tempor consequat Vivamus blandit lacus quam a ornare tortor Vestibulum a tellus in orci ultrices semper Aenean sit amet libero a ipsum aliquet condimentum Quisque volutpat congue felis vel hendrerit Proin congue enim et mi mattis tempor Praesent nec ante nec mauris suscipit pulvinar condimentum eu massa Aliquam iaculis ipsum sed ligula condimentum sed ultrices odio iaculis Nulla viverra ipsum et auctor viverra dolor est condimentum nisl in tincidunt erat massa vitae lacus Donec convallis tincidunt nisl vitae laoreet Mauris ligula mauris lacinia quis dictum volutpat tincidunt ac neque Phasellus dapibus suscipit pulvinar Fusce lacus est ultrices a adipiscing sed condimentum sit amet leo Proin mauris ante tempor non tempor at commodo id mi Quisque ac massa justo Quisque lacinia malesuada ipsum hendrerit facilisis Nulla a metus a augue viverra placerat dapibus ac lacus Integer lectus metus laoreet a semper eget dictum at purus Sed');
		}

		$output = array();
		for ($i = 0; $i < $word_length; $i++) {
			$key = array_rand($this->_words);
			$output[] = $this->_words[$key];
		}

		return implode(' ', $output);
	}

	protected function _getPerson($id)
	{
		return App::getEntityRepository('DeskPRO:Person')->find($id);
	}
}
