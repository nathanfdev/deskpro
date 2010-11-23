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
 * @Entity
 * @Table(name="tickets_flagged")
 */
class TicketFlagged extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @Id @Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id = null;

	/**
	 * @var int
	 * @Id @Column(name="person_id", type="integer")
	 */
	protected $person_id = null;
}