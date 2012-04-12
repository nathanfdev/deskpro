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
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\ClientMessage;
use Application\DeskPRO\Searcher\SearcherAbstract;
use Application\DeskPRO\Searcher\ChatConversationSearch;

use Application\DeskPRO\ClientMessage\Generator\Chat as ChatClientMessageGenerator;
use Application\DeskPRO\Chat\UserChat\GroupingCounter;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Dates;
use Orb\Util\Util;

class UserChatController extends AbstractController
{
	protected $filters  = array('all', 'mine');
	protected $groups = array('none', 'department', 'agent', 'date_created');

	public function viewAction($conversation_id)
	{
		$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		if (!$this->person->PermissionsManager->ChatChecker->canView($convo)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $this->session->getEntity()));

		if ($convo->status == 'open') {
			$chat_manager->personJoined($convo, $this->person);

			if (!$convo['agent']) {
				$chat_manager->assignAgent($convo, $this->person);
			}
		}

		$convo_messages = App::getOrm()->createQuery("
			SELECT m
			FROM DeskPRO:ChatMessage m
			WHERE m.conversation = ?1
			ORDER BY m.id DESC
		")->setParameter(1, $convo)->execute();

		$session = $convo->session;
		$visitor = $convo->visitor;
		$other_chats = $this->em->getRepository('DeskPRO:ChatConversation')->getPastChatsForVisitor($visitor);

		// For selector
		$agents = App::getEntityRepository('DeskPRO:Person')->getAgents();

		return $this->render('AgentBundle:UserChat:view.html.twig', array(
			'convo_messages' => $convo_messages,
			'convo' => $convo,
			'session' => $session,
			'visitor' => $visitor,
			'other_chats' => $other_chats,
			'agents' => $agents
		));
	}


	/**
	 * Reassign a chat
	 *
	 * @param  $conversation_id
	 * @param  $quick_reply_id
	 */
	public function assignChatAction($conversation_id, $agent_id)
	{
		$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		if (!$this->person->PermissionsManager->ChatChecker->canView($convo)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $this->session->getEntity()));

		if ($agent_id) {
			$agent = App::findEntity('DeskPRO:Person', $agent_id);
		} else {
			$agent = null;
		}
		if ($agent) {
			$chat_manager->assignAgent($convo, $agent);
		} else {
			$chat_manager->unassignAgent($convo);
		}

		return $this->createJsonCmResponse();
	}

	public function getGroupByCountsAction()
	{
		$user_groups = $this->in->getArrayValue('filters');
		$filters = $this->getFilters();
		$groups = $this->getGroups();
		$group_counts = array();

		foreach($user_groups as $filter_id => $group_id)
		{
			if(!$filter_id || !in_array($filter_id, $filters))
				$filter_id = $filters[0];

			if(!$group_id || !in_array($group_id, $groups))
				$group_id = $groups[0];

			$searcher = new ChatConversationSearch();
			$searcher->setPersonContext($this->person);
			$searcher->addTerm(ChatConversationSearch::TERM_STATUS, SearcherAbstract::OP_IS, 'ended');
			$this->updateSearcherFilter($searcher, $filter_id);

			$grouper = new GroupingCounter($group_id);
			$counts = $grouper->getCounts($searcher);
			$group_counts[$filter_id] = $this->renderView('AgentBundle:UserChat:window-filter-groupresult.html.twig',
				array('groups' => $counts, 'filter_id' => $filter_id, 'group_by' => $group_id));
		}

		return $this->createJsonResponse($group_counts);
	}

	/**
	 * Reassign a chat
	 *
	 * @param  $conversation_id
	 * @param  $quick_reply_id
	 */
	public function sendInviteAction($conversation_id, $agent_id)
	{
		$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		if (!$this->person->PermissionsManager->ChatChecker->canView($convo)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$agent = App::findEntity('DeskPRO:Person', $agent_id);

		$cm = new ClientMessage();
		$cm->fromArray(array(
			'channel' => 'chat.invited',
			'data' => $convo->getInfo(),
			'for_person' => $agent,
			'created_by_client' => $this->session->getId()
		));
		$this->em->persist($cm);
		$this->em->flush();

		return $this->createJsonResponse(array('success' => true));
	}

	/**
	 * Changes properties
	 *
	 * @param  $conversation_id
	 * @param  $quick_reply_id
	 */
	public function changePropertiesAction($conversation_id)
	{
		$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		if (!$this->person->PermissionsManager->ChatChecker->canView($convo)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $this->session->getEntity()));

		$props = $this->in->getCleanValueArray('props', 'raw', 'string');

		if (isset($props['department_id'])){
			$dep = null;
			if ($props['department_id']) {
				$dep = $this->em->find('DeskPRO:Department', $props['department_id']);
			}
			$chat_manager->setDepartment($convo, $dep, $this->person);
		}

		return $this->createJsonCmResponse();
	}


	/**
	 * Add a participant
	 *
	 * @param  $conversation_id
	 * @param  $quick_reply_id
	 */
	public function addPartAction($conversation_id, $agent_id)
	{
		$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		if (!$this->person->PermissionsManager->ChatChecker->canView($convo)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$agent = App::findEntity('DeskPRO:Person', $agent_id);
		if (!$agent OR $convo->hasParticipant($agent)) {
			return $this->createJsonResponse(array());
		}

		$convo->addParticipant($agent);

		$client_messages = array();
		foreach ($convo->getCreatedMessages() as $msg) {
			$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createNewMessageMessages(App::getSession()->getEntityId(), $msg));
		}

		$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createNewAddedPartMessage(
			App::getSession()->getEntityId(),
			$convo,
			$agent
		));

		$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createPartisipatedUpdatedMessages(
			App::getSession()->getEntityId(),
			$convo
		));

		App::getOrm()->transactional(function ($em) use ($convo, $client_messages) {
			$em->persist($convo);

			if ($client_messages) {
				foreach ($client_messages as $cm) {
					$em->persist($cm);
				}
			}

			$em->flush();
		});

		return $this->createJsonCmResponse(array(
			'client_messages' => $client_messages
		));
	}


	/**
	 * @param $conversation_id
	 */
	public function syncPartsAction($conversation_id)
	{
		$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		if (!$this->person->PermissionsManager->ChatChecker->canView($convo)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$have = array();
		foreach ($convo->participants as $part) {
			if ($convo->agent && $convo->agent->id == $part->id) {
				continue;
			}

			$have[] = $part->id;
		}

		$target = $this->container->getIn()->getCleanValueArray('agent_ids', 'uint', 'discard');

		$add = array_diff($have, $target);
		$rem = array_diff($target, $have);

		$client_messages = array();
		if ($add) {
			foreach ($add as $pid) {
				$agent = $this->em->getRepository('DeskPRO:Person')->find($pid);
				if (!$agent || !$agent->is_agent) {
					continue;
				}

				$convo->addParticipant($agent);

				foreach ($convo->getCreatedMessages() as $msg) {
					$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createNewMessageMessages(App::getSession()->getEntityId(), $msg));
				}

				$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createNewAddedPartMessage(
					App::getSession()->getEntityId(),
					$convo,
					$agent
				));

				$client_messages = array_merge($client_messages, ChatClientMessageGenerator::createPartisipatedUpdatedMessages(
					App::getSession()->getEntityId(),
					$convo
				));
			}

			App::getOrm()->transactional(function ($em) use ($convo, $client_messages) {
				$em->persist($convo);

				if ($client_messages) {
					foreach ($client_messages as $cm) {
						$em->persist($cm);
					}
				}

				$em->flush();
			});
		}

		return $this->createJsonCmResponse(array(
			'client_messages' => $client_messages
		));
	}


	/**
	 * End a chat
	 *
	 * @param  $conversation_id
	 */
	public function endChatAction($conversation_id)
	{
		$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		if (!$this->person->PermissionsManager->ChatChecker->canView($convo)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $this->session->getEntity()));
		$chat_manager->endChat($convo, $this->person, '');

		return $this->createJsonCmResponse();
	}


	/**
	 * Accepts a POST of a new message to a conversation
	 */
	public function sendMessageAction($conversation_id)
	{
		if ($conversation_id instanceof ChatConversation) {
			// sendAgentMessageAction calls this with the convo already
			$convo = $conversation_id;
		} else {
			$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);
		}

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $this->session->getEntity()));
		$chat_manager->addMessage($convo, $this->person, $this->in->getString('content'));

		return $this->createJsonCmResponse();
	}


	/**
	 * End a chat
	 *
	 * @param  $conversation_id
	 */
	public function leaveChatAction($conversation_id)
	{
		$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);

		if (!$this->person->PermissionsManager->ChatChecker->canView($convo)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		if ($convo->status == 'open') {
			$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $this->session->getEntity()));
			$chat_manager->personLeft($convo, $this->person);
		}

		return $this->createJsonCmResponse();
	}


	public function sendFileAction($conversation_id)
	{
		if ($conversation_id instanceof ChatConversation) {
			// sendAgentMessageAction calls this with the convo already
			$convo = $conversation_id;
		} else {
			$convo = App::findEntity('DeskPRO:ChatConversation', $conversation_id);
		}

		$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($this->in->getUint('send_blob_id'));

		$msg = "File: <a href=\"{$blob->getDownloadUrl(true)}\" target=\"_blank\">" . htmlspecialchars($blob->filename) . "</a> (" . $blob->getReadableFilesize() . ")";
		if ($blob->isImage()) {
			$msg .= '<div class="file-thumb"><img src="' . $blob->getThumbnailUrl(50, true) . '" /></div>';
		}

		/** @var $chat_manager \Application\DeskPRO\Chat\UserChat\UserChatManager */
		$chat_manager = $this->container->getSystemObject('user_chat_manager', array('session' => $this->session->getEntity()));
		$chat_manager->addMessage(
			$convo,
			$this->person,
			$msg,
			array('is_html' => true, 'type' => 'file', 'blob_id' => $blob->id)
		);

		return $this->createJsonCmResponse();
	}


	/**
	 * List the articles
	 */
	public function getSectionDataAction()
	{
		$agent_names = App::getEntityRepository('DeskPRO:Person')->getAgentNames();
		$searcher = new ChatConversationSearch();
		$searcher->setPersonContext($this->person);
		$searcher->setColumns('IF(agent_id, agent_id, -1) AS agent_id, COUNT(*) AS count');
		$searcher->setGroupBy('chat_conversations.agent_id');
		$searcher->addTerm(ChatConversationSearch::TERM_STATUS, SearcherAbstract::OP_IS, 'open');

		// Initial counts
		$initial_counts = App::getDb()->fetchAllKeyValue($searcher->getSql());
		$initial_counts['total'] = array_sum(array_values($initial_counts));

		$searcher = new ChatConversationSearch();
		$searcher->setPersonContext($this->person);
		$searcher->setColumns('IF(department_id, department_id, -1) AS department_id, COUNT(*) AS count');
		$searcher->setGroupBy('chat_conversations.agent_id');
		$searcher->addTerm(ChatConversationSearch::TERM_STATUS, SearcherAbstract::OP_IS, 'open');
		// Possible permissions bug!
		$searcher->addTerm(ChatConversationSearch::TERM_AGENT_ID, SearcherAbstract::OP_IS, 0);

		$dep_counts = App::getDb()->fetchAllKeyValue($searcher->getSql());

		$dep_counts['none_total'] = isset($dep_counts[-1]) ? $dep_counts[-1] : 0;
		$dep_counts['none'] = isset($dep_counts[-1]) ? $dep_counts[-1] : 0;

		$dep_counts['0_total'] = $dep_counts['none'];

		// Departments
		$departments = App::getEntityRepository('DeskPRO:Department')->getDepartmentsInHierarchy();
		$single_dep_mode = false;
		if ($this->em->getRepository('DeskPRO:Department')->countAll() == 1) {
			$single_dep_mode = true;
		}

		foreach ($departments as $dep) {
			$c_id = $dep['id'];
			$total = 0;
			if (isset($dep_counts[$c_id])) {
				$total = $dep_counts[$c_id];
			}

			foreach ($dep['children'] as $child_dep) {
				$child_id = $child_dep['id'];
				$dep_counts[$child_id . '_total'] = 0;
				if (isset($dep_counts[$child_id])) {
					$dep_counts[$child_id . '_total'] = $dep_counts[$child_id];
					$total += $dep_counts[$child_id];
				}
			}

			$dep_counts["{$c_id}_total"] = $total;
			$dep_counts['0_total'] += $total;
		}

		$filters = array();
		$tr = App::getTranslator();

		foreach($this->getFilters() as $filter_id)
		{
			$searcher = new ChatConversationSearch();
			$searcher->setPersonContext($this->person);
			$searcher->setColumns('COUNT(*)');
			$searcher->addTerm(ChatConversationSearch::TERM_STATUS, SearcherAbstract::OP_IS, 'ended');
			$this->updateSearcherFilter($searcher, $filter_id);

			$filter = array();
			$filter['id'] = $filter_id;

			$filter['count'] = $this->container->getDb()->fetchColumn($searcher->getSQL());
			$filter['title'] = $tr->phrase('agent.chat.filter_title_' . $filter_id);
			$filter['disallowed'] = implode(',', $this->getDisallowedGroupsForFilter($filter_id));
			$filters[] = $filter;
		}

		$groupers = array();

		foreach($this->getGroups() as $grouper_id)
		{
			$grouper = array();
			$grouper['id'] = $grouper_id;
			$grouper['title'] = $tr->phrase('agent.general.group_' . $grouper_id);
			$groupers[] = $grouper;
		}

		$html = $this->renderView('AgentBundle:UserChat:window-section.html.twig', array(
			'counts' => $initial_counts,
			'dep_counts' => $dep_counts,
			'agent_names' => $agent_names,
			'departments' => $departments,
			'single_dep_mode' => $single_dep_mode,
			'ended_filters' => $filters,
			'ended_groups' => $groupers,
            'agent_id' => $this->getPerson()->id,
		));

		return $this->createJsonResponse(array('section_html' => $html));
	}



	public function listChatsAction()
	{
		$agent_id = $this->in->getInt('agent_id');
		$agent = null;
		if ($agent_id) {
			$agent = App::findEntity('DeskPRO:Person', $agent_id);
		}

		$department_id = $this->in->getInt('department_id');
		$department = null;
		if ($department_id) {
			$department = App::findEntity('DeskPRO:Department', $department_id);
		}

		$convos = App::getEntityRepository('DeskPRO:ChatConversation')->getOpenForAgentAndDepartment($agent, $department);

		return $this->render('AgentBundle:UserChat:open-list.html.twig', array(
			'agent' => $agent,
			'convos' => $convos
		));
	}

	/**
	 * @param $id
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function getChatAlertAction($id)
	{
		$convo = $this->em->find('DeskPRO:ChatConversation', $id);
		if (!$convo) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$tickets = null;
		if ($convo->person) {
			$tickets = $this->em->getRepository('DeskPRO:Ticket')->getLatestByUser($convo->person, 5, true);
		}

		$waiting_secs = time() - $convo->date_created->getTimestamp();

		$url = null;
		if ($convo->visitor && $convo->visitor->last_page) {
			$url = $convo->visitor->last_page;
		}

		return $this->render('AgentBundle:UserChat:chat-alert.html.twig', array(
			'convo'         => $convo,
			'person'        => $convo->person,
			'tickets'       => $tickets,
			'session'       => $convo->session,
			'visitor'       => $convo->visitor,
			'waiting_secs'  => $waiting_secs,
			'url'           => $url,
		));
	}

	/**
	 * Creates a JSON response but with client messages as well
	 *
	 * @param array $other_data
	 * @return \Application\DeskPRO\HttpKernel\Controller\Response
	 */
	protected function createJsonCmResponse(array $other_data = array())
	{
		$client_messages = false;
		if ($this->in->getUint('client_messages_since')) {
			$client_messages = App::getEntityRepository('DeskPRO:ClientMessage')->getMessageData(
				$this->person,
				$this->session,
				$this->in->getUint('client_messages_since')
			);
		}

		$other_data['client_messages'] = $client_messages;

		return $this->createJsonResponse($other_data);
	}


	/**
	 * Lists previously closed chats
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function filterAction()
	{
		$filter_id = $this->in->getString('filter_id');
		$filters = $this->getFilters();

		if(!$filter_id || !in_array($filter_id, $filters))
			$filter_id = $filters[0];

		$searcher = new ChatConversationSearch();
		$searcher->setPersonContext($this->person);
		$searcher->setColumns('COUNT(*)');
		$searcher->addTerm(ChatConversationSearch::TERM_STATUS, SearcherAbstract::OP_IS, 'ended');
		$this->updateSearcherFilter($searcher, $filter_id);

		$groups = $this->getGroups();
		$group_by = $this->in->getString('group_var');
		$group_id = '';

		if($group_by && in_array($group_by, $groups))
		{
			switch($group_by) {
				case 'agent':
					$group_id = $this->in->getInt('group_val');
					$searcher->addTerm(ChatConversationSearch::TERM_AGENT_ID, SearcherAbstract::OP_IS, $group_id);
					break;
				case 'date_created':
					$group_id = $this->in->getString('group_val');
					$month_year = explode('-',$group_id,2);

					if(count($month_year) != 2)
						break;

					$month = (int)$month_year[0];
					$year = (int)$month_year[1];

					if(!checkdate($month, 1, $year))
						break;

					$beginning = Dates::firstDayInMonth($month, $year);
					$end = Dates::lastDayInMonth($month, $year);
					$searcher->addTerm(ChatConversationSearch::TERM_DATE_CREATED, SearcherAbstract::OP_BETWEEN, array('date1' => $beginning, 'date2' => $end));

					break;
				case 'department':
					$group_id = $this->in->getInt('group_val');
					$searcher->addTerm(ChatConversationSearch::TERM_DEPARTMENT_ID, SearcherAbstract::OP_IS, $group_id);
					break;
			}
		}

		$total = $this->container->getDb()->fetchColumn($searcher->getSql());

		$limit = 50;
		$max_page = ceil($total / $limit);

		$page = $this->in->getUint('p');
		if (!$page || $page > $max_page) $page = 1;

		$start = ($page - 1) * $limit;

		$searcher->setColumns('id');
		$searcher->setLimit('start', $start);
		$searcher->setLimit('limit', $limit);
		$chat_ids = $this->container->getDb()->fetchAllCol($searcher->getSql());

		$chats = $this->container->getEm()->getRepository('DeskPRO:ChatConversation')->getByIds($chat_ids, true);

		return $this->render('AgentBundle:UserChat:list.html.twig', array(
			'chat_ids' => $chat_ids,
			'chats' => $chats,
			'total' => $total,
			'page' => $page,
			'max_page' => $max_page,
			'filter_id' => $filter_id,
			'group_var' => $group_by,
			'group_val' => $group_id
		));
	}

	protected function updateSearcherFilter($searcher, $filter)
	{
		switch($filter) {
			case 'mine':
				$searcher->addTerm(ChatConversationSearch::TERM_AGENT_ID,
					SearcherAbstract::OP_IS,$this->person['id']);
				break;
		}
	}

	protected function getGroups()
	{
		$groups = array();

		foreach($this->groups as $group) {
			if($group == 'agent'
			&& !$this->person->hasPerm('agent_tickets.view_others')
			&& !$this->person->hasPerm('agent_tickets.view_unassigned'))
				continue;

			$groups[] = $group;
		}

		return $groups;
	}

	protected function getDisallowedGroupsForFilter($filter)
	{
		$disallowed = array();

		if($filter == 'mine')
			$disallowed[] = 'agent';

		return $disallowed;
	}

	protected function getFilters()
	{
		$filters = array();

		foreach($this->filters as $filter) {
			if($filter == 'all'
			&& !$this->person->hasPerm('agent_tickets.view_others')
			&& !$this->person->hasPerm('agent_tickets.view_unassigned'))
				continue;

			$filters[] = $filter;
		}

		return $filters;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getChatsPageAction()
	{
		$chat_ids = $this->container->getIn()->getCleanValueArray('ids', 'uint', 'discard');
		$chats = $this->container->getEm()->getRepository('DeskPRO:ChatConversation')->getByIds($chat_ids, true);

		return $this->render('AgentBundle:UserChat:list-page.html.twig', array(
			'chats' => $chats
		));
	}
}
