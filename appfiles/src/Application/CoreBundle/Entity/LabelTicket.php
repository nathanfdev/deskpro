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
 * Labels on tickets
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="labels_tickets")
 */
class LabelTicket extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var string
	 * @Id
	 * @Column(name="label", type="string", length=255)
	 */
	protected $label;

	/**
	 * @var int
	 * @Id
	 * @Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id;

	/**
	 * @var \Application\CoreBundle\Entity\Ticket
	 * @ManyToOne(targetEntity="Ticket")
	 * @JoinColumn(name="ticket_id", referencedColumnName="id")
	 */
	protected $ticket;
}