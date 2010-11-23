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
 * Links participants to tickets
 *
 * @Entity
 * @Table(name="tickets_participants")
 */
class TicketParticipant extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @Id @Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Ticket
	 * @OneToOne(targetEntity="Ticket")
	 * @JoinColumn(name="ticket_id", referencedColumnName="id")
	 */
	protected $ticket = null;

	/**
	 * @var int
	 * @Id @Column(name="person_id", type="integer")
	 */
	protected $person_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Person
	 * @OneToOne(targetEntity="Person")
	 * @JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;
}