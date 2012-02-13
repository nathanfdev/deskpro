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

namespace Application\DeskPRO\Elastica\Transformer;

use Application\DeskPRO\Entity\News;

class TicketMessageTransformer implements TransformerInterface
{
	public function transform($ticket_message)
	{
		$data = array();
		$data['message']       = $ticket_message['message'];
		$data['date_created']  = $ticket_message['date_created']->getTimestamp();
		$data['_parent']       = $ticket_message['ticket_id'];

		$doc = new \Elastica_Document($ticket_message['id'], $data);

		return $doc;
	}
}