<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use \Orb\Util\Strings;
use \Orb\Util\Arrays;

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

	public function getFilterCountsMessage()
	{
		$all_counts = $filters = App::getApi('tickets.filters')->getAllCountsSystemFilters($this->person);

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
		$tickets = App::getOrm()->getRepository('DeskPRO:Ticket')->getTicketsFromIds($ticket_ids);

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