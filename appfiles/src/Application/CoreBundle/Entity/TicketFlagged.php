<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;

/**
 * Flagged tickets
 *
 * @orm:Entity
 * @orm:Table(name="tickets_flagged")
 */
class TicketFlagged extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id = null;

	/**
	 * @var int
	 * @orm:Id @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id = null;
}