<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

/**
 * Links participants to tickets
 *
 * @orm:Entity
 * @orm:Table(name="tickets_participants")
 */
class TicketParticipant extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Ticket", inversedBy="participants")
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id")
	 */
	protected $ticket = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:Id
	 * @orm:OneToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;
}