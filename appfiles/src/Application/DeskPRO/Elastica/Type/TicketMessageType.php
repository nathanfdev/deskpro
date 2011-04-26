<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Elastica
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Elastica\Type;

use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Elastica\Transformer\ArticleTransformer;

use APplication\DeskPRO\App;

/**
 * Type for the TicketMessage entity
 */
class TicketMessageType extends AbstractType
{
	/**
	 * Transform a value into a Document
	 *
	 * @param  TicketMessage $ticket
	 * @return \Elastic_Document
	 */
	public function transformToDocument($ticket_message)
	{
		$trans = new TicketTransformer();
		$doc = $trans->transform($ticket_message);

		if ($ticket_message->ticket->getIsArchived()) {
			$doc->setIndex('ticket');
		} else {
			$doc->setIndex('ticket_active');
		}

		$doc->setIndex('ticket');
		$doc->setType('ticket_message');

		return $doc;
	}


	/**
	 * Get the document type
	 *
	 * @return string
	 */
	public function getType()
	{
		return 'ticket_message';
	}


	/**
	 * Get a single value from a document
	 *
	 * @return \Application\DeskPRO\Entity\TicketMessage
	 */
	protected function getValueFromResult(\Elastica_Result $doc)
	{
		return App::getEntityRepository('DeskPRO:TicketMessage')->find($doc->getId());
	}
}