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

namespace Application\DeskPRO\Tickets\Escalations;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\TicketEscalation;
use Application\DeskPRO\Searcher\OrganizationSearch;
use Application\DeskPRO\Searcher\PersonSearch;
use Application\DeskPRO\Searcher\TicketSearch;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Psr\Log\NullLogger;

class EscalationTicketMatcherTest extends EscalationTicketMatcher
{
	private $matches;


	/**
	 * @param \Application\DeskPRO\Entity\Ticket[] $tickets
	 */
	public function setTickets(array $tickets)
	{
		$this->matches = $tickets;
	}

	/**
	 * @param TicketEscalation $esc
	 * @param int              $limit
	 * @return \Application\DeskPRO\Entity\Ticket[]
	 */
	public function getMatches(TicketEscalation $esc, $limit = 100)
	{
		return $this->matches;
	}
}