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

use Orb\Util\Strings;
use Orb\Util\Arrays;

use Application\DeskPRO\App;

/**
 * Handles AJAX serving of client messages
 */
class ClientMessagesController extends AbstractController
{
	public function getNewMessagesAction()
	{
		$new_since = $this->in->getUint('since');
		$last_since = $this->person->getPref('agent.ui.last_message_id');
		$activity_time = $this->in->getUint('at');

		#------------------------------
		# Standard client messages
		#------------------------------

		$data = $this->em->getRepository('DeskPRO:ClientMessage')->getMessageData(
			$this->person,
			$this->session,
			$new_since,
			($this->in->getBool('is_initial_poll') ? $last_since : null),
			$this->in->getBool('is_initial_poll')
		);

		// We inject a rendered view for new chats, so loop through the messages to do that
		foreach ($data['messages'] as &$item) {
			$channel = $item[1];
			if ($channel == 'chat.new') {
				$cid = $item[2]['conversation_id'];
				$item[2]['html'] = $this->forward('AgentBundle:UserChat:getChatAlert', array('id' => $cid))->getContent();
			}
		}

		#------------------------------
		# Poll requests
		#------------------------------

		$dos = $this->in->getArrayValue('do');

		foreach ($dos as $do) {
			$do = Strings::dashToCamelCase($do);
			$method = $do . 'Message';

			if (!method_exists($this, $method)) {
				continue;
			}

			$method_data = $this->$method();
			$method_data = Arrays::removeFalsey($method_data);

			if ($method_data) {
				$data['messages'] = array_merge($data['messages'], $method_data);
			}
		}

		// We save the last message we know a user got because we need to know
		// to deliver offline messages (such as chats) the next time the user logs in
		if ($new_since && $new_since > $last_since) {
			$pref = $this->person->setPreference('agent.ui.last_message_id', $new_since);

			$this->container->getDb()->replace('people_prefs', array(
				'name'         => $pref->name,
				'value_str'    => $new_since,
				'value_array'  => null,
				'date_expire'  => null,
				'person_id'    => $this->person->getId()
			));
		}

		// See if we should update last activity time
		if ($activity_time && $activity_time > (time()-330)) {
			// This bit makes sure theres only one record per 5 minute block
            $date_active = new \DateTime('@' . $activity_time);
            list($hour, $minute) = explode(':', $date_active->format('H:i'));
            $minute = intval($minute / 5) * 5;
            $date_active->setTime($hour, $minute, 0);

			App::getDb()->executeQuery('INSERT IGNORE INTO agent_activity(agent_id, date_active) VALUES(?,?)', array($this->person->getId(), $date_active->format('Y-m-d H:i:s')));
		}

		return $this->createJsonResponse($data);
	}

	public function pingSubscriptionsAction()
	{
		$this->person->loadHelper('ClientChannelSubscriptions', array('session' => $this->session));
		$this->person->getClientChannelSubs()->pingSubscriptions();

		$sub_channels = array();
		foreach ($subs as $sub) {
			$sub_channels[] = $sub['channel'];
		}

		return $this->createJsonResponse(array('channels' => $sub_channels));
	}

	public function subscribeChannelsAction()
	{
		$channels = $this->in->getCleanValueArray('channels', 'string', 'discard');

		$this->person->loadHelper('ClientChannelSubscriptions', array('session' => $this->session));
		$subs = $this->person->getClientChannelSubs()->subscribeChannels($channels);

		$names = array();
		foreach ($subs as $sub) {
			$names[] = $sub['channel'];
		}

		return $this->createJsonResponse(array('subscribed_channels' => $names));
	}

	public function unsubscribeChannelsAction()
	{
		$channels = $this->in->getCleanValueArray('channels', 'string', 'discard');

		$this->person->loadHelper('ClientChannelSubscriptions', array('session' => $this->session));
		$subs = $this->person->getClientChannelSubs()->unsubscribeChannels($channels);

		$names = array();
		foreach ($subs as $sub) {
			$names[] = $sub['channel'];
		}

		return $this->createJsonResponse(array('unsubscribed_channels' => $names));
	}

	public function unsubscribeChannelAction()
	{
		$channels = $this->in->getCleanValueArray('channels', 'string', 'discard');

		$this->person->loadHelper('ClientChannelSubscriptions', array('session' => $this->session));
		$subs = $this->person->getClientChannelSubs()->unsubscribeChannels($channels);

		$names = array();
		foreach ($subs as $sub) {
			$names[] = $sub['channel'];
		}

		return $this->createJsonResponse(array('unsubscribed_channels' => $names));
	}

	############################################################################
	# getFilterCounts
	############################################################################

	public function getSysFilterCountsMessage()
	{
		$all_counts = App::getApi('tickets.filters')->getAllCountsSystemFilters($this->person);

		return array(array(null, 'filters.counts', array($all_counts)));
	}

	public function getCustomFilterCountsMessage()
	{
		$all_counts = App::getApi('tickets.filters')->getAllCountsCustomFilters($this->person);

		return array(array(null, 'filters.counts', array($all_counts)));
	}


	############################################################################
	# getFlaggedCounts
	############################################################################

	public function getFlaggedCountsMessage()
	{
		$all_counts = $filters = App::getApi('tickets.filters')->getAllCountsForPersonFlagged($this->person);

		return array(array(null, 'filter-flagged.counts', array($all_counts)));
	}

	############################################################################
	# getCheckTickets
	############################################################################

	public function checkTicketsMessage()
	{
		$ticket_ids = $this->in->getCleanValueArray('check-ticket-ids', 'uint', 'discard');
		$tickets = $this->em->getRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);

		$messages = array();
		foreach ($tickets as $ticket) {
			$msg_id = 'tickets.check.' . $ticket['id'];
			$msg_data = array();

			$msg_data['is_locked'] = $ticket->isLocked();

			$messages[$msg_id] = $msg_data;
		}

		return $messages;
	}

	############################################################################
	# getOnlineAgents
	############################################################################

	public function getOnlineAgentsMessage()
	{
		$active_agents = $this->em->getRepository('DeskPRO:Person')->getActiveAgents();
		$online_agents = array_keys($active_agents);

		return array(array(null, 'agent.online-agents', array('online_agents' => $online_agents)));
	}
}
