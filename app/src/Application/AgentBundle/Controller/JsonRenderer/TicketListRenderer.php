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

namespace Application\AgentBundle\Controller\JsonRenderer;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketResultsDisplay;
use Application\DeskPRO\Util;

class TicketListRenderer
{
	public function renderTicketDisplay(TicketResultsDisplay $ticket_display, $as_array = false)
	{
		if (!$ticket_display->getCount()) {
			return '[]';
		}

		$json_array = array();

		foreach ($ticket_display->getTickets() as $ticket) {
			$data = $ticket->toApiData();

			$data['previews'] = array();
			foreach ($ticket_display->getTicketPreview($ticket) as $m) {
				$data['previews'][] = array(
					'message' => array(
						'id'               => $m->id,
						'preview_text'     => $m->getMessagePreviewText(200),
						'date_created'     => $m->date_created->format('Y-m-d H:i:s'),
						'date_created_ts'  => $m->date_created->getTimestamp(),
					),
					'person' => array(
						'id'            => $m->person ? $m->person->id : 0,
						'display_name'  => $m->person ? $m->person->getDisplayName() : 'Anon',
						'is_agent'      => $m->person ? $m->person->is_agent : false,
					),
				);
			}

			$json_array[] = $data;
		}

		if ($as_array) {
			return $json_array;
		}

		return Util::jsonEncode($json_array);
	}

	public function renderTicket(Ticket $ticket)
	{

	}
}
