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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\TicketMessage as TicketMessageEntity;

use Orb\Util\Numbers;


class TicketMessageTranslated extends AbstractEntityRepository
{
	/**
	 * Finds all translated messages on a message
	 *
	 * @param TicketMessageEntity $ticket_message
	 * @param string|null         $lang           Optionally only get this one
	 * @return array|TicketMessageEntity|Null
	 */
	public function getForMessage(TicketMessageEntity $ticket_message, $lang_code = null)
	{
		if ($lang_code) {
			return $this->_em->createQuery("
				SELECT m
				FROM DeskPRO:TicketMessageTranslated m
				WHERE m.ticket_message = ?0 AND m.lang_code = ?1
			")->setParameters(array($ticket_message, $lang_code))->getOneOrNullResult();
		} else {
			return $this->_em->createQuery("
				SELECT m
				FROM DeskPRO:TicketMessageTranslated m INDEX BY m.lang_code
				WHERE m.ticket_message = ?0 AND m.lang_code = ?1
			")->setParameters(array($ticket_message))->getOneOrNullResult();
		}
	}
}
