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

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Elastica\Transformer\ArticleTransformer;

use APplication\DeskPRO\App;

/**
 * Type for the Ticket entity
 */
class TicketType extends AbstractType
{
	/**
	 * Transform a value into a Document
	 *
	 * @param  Ticket $ticket
	 * @return \Elastic_Document
	 */
	public function transformToDocument($ticket)
	{
		$trans = new TicketTransformer();
		$doc = $trans->transform($ticket);

		if ($ticket->getIsArchived()) {
			$doc->setIndex('ticket');
		} else {
			$doc->setIndex('ticket_active');
		}

		$doc->setIndex('ticket');
		$doc->setType('ticket');

		return $doc;
	}


	/**
	 * Get the document type
	 *
	 * @return string
	 */
	public function getType()
	{
		return 'ticket';
	}


	/**
	 * Get a single value from a document
	 *
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	protected function getValueFromResult(\Elastica_Result $doc)
	{
		return App::getEntityRepository('DeskPRO:Ticket')->find($doc->getId());
	}
}