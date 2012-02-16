<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Search
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Search\ContentType\Mysql;

use Application\DeskPRO\Search\ContentType\AbstractContentType;
use Application\DeskPRO\Search\SearcherResult\ResultInterface;
use Application\DeskPRO\Search\Indexer\Document;

/**
 * Ticket message is used just to trigger the entity listener to update
 * the ticket.
 */
class TicketMessage extends AbstractContentType
{
	const ENTITY_NAME = 'DeskPRO:Ticket';
	
	public function objectToDocument($ticket_message)
	{
		$ticket = $ticket_message->ticket;

		$data = array();
		$data['id'] = $ticket['id'];
		$data['content_type'] = 'ticket';
		$data['content'] = $ticket['subject'] . "\n" . $ticket->getFirstMessage()->getMessageText();

		$doc = Document::newFromArray($data);

		return $doc;
	}
}