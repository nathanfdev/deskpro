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

class TicketTransformer implements TransformerInterface
{
	public function transform($ticket)
	{
		$data = array();
		$data['subject']       = $ticket['subject'];
		$data['content']       = $ticket->getFirstMessage()->getMessageText();
		$data['department_id'] = $ticket['department_id'];
		$data['is_archived']   = $ticket->getIsArchived();
		$data['date_created']  = $ticket['date_created']->getTimestamp();
		$data['labels']        = $ticket->getLabelManager()->getLabelsArray();

		$doc = new \Elastica_Document($ticket['id'], $data);

		return $doc;
	}
}