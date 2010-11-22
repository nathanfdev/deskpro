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
 * Custom ticket data
 * 
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="custom_data_ticket")
 */
abstract class CustomDataTicket extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\CoreBundle\Entity\CustomDefTicket
	 * @ManyToOne(targetEntity="CustomDefTicket")
	 * @JoinColumn(name="field_id", referencedColumnName="id")
	 */
	protected $field = null;

	/**
	 * @var int
	 * @Id @Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id;

	/**
	 * @var \Application\CoreBundle\Entity\Ticket
	 * @ManyToOne(targetEntity="Ticket")
	 * @JoinColumn(name="ticket_id", referencedColumnName="id")
	 */
	protected $ticket;
}