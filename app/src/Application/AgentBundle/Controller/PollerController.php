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
 * Handles creating/editing of API keys
 */
class PollerController extends AbstractController
{
	############################################################################
	# /agent/poller                                         agent_interface_poller
	############################################################################

	/**
	 * Handle a poller request
	 */
	public function handlerAction()
	{
		$dos = $this->in->getArrayValue('do');

		$data = array();

		foreach ($dos as $do) {
			$do = Strings::dashToCamelCase($do);
			$method = $do . 'Message';
			$method_data = $this->$method();
			$method_data = Arrays::removeFalsey($method_data);

			if ($method_data) {
				$data = array_merge($data, $method_data);
			}
		}

		return $this->createJsonResponse(json_encode(array(
			'messages' => $data
		)));
	}


	############################################################################
	# getFilterCounts
	############################################################################

	public function getSysFilterCountsMessage()
	{
		$all_counts = App::getApi('tickets.filters')->getAllCountsSystemFilters($this->person);

		return array(array('filters.counts', $all_counts));
	}

	public function getCustomFilterCountsMessage()
	{
		$all_counts = App::getApi('tickets.filters')->getAllCountsCustomFilters($this->person);

		return array(array('filters.counts', $all_counts));
	}


	############################################################################
	# getFlaggedCounts
	############################################################################

	public function getFlaggedCountsMessage()
	{
		$all_counts = $filters = App::getApi('tickets.filters')->getAllCountsForPersonFlagged($this->person);

		return array(array('filter-flagged.counts', $all_counts));
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

			$msg_data['isLocked'] = $ticket->isLocked();

			$messages[$msg_id] = $msg_data;
		}

		return $messages;
	}
}
