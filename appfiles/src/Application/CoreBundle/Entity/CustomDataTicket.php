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
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="custom_data_ticket")
 */
abstract class CustomDataTicket extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\CoreBundle\Entity\CustomDefTicket
	 * @orm:ManyToOne(targetEntity="CustomDefTicket")
	 * @orm:JoinColumn(name="field_id", referencedColumnName="id")
	 */
	protected $field = null;

	/**
	 * @var int
	 * @orm:Id @orm:Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id;

	/**
	 * @var \Application\CoreBundle\Entity\Ticket
	 * @orm:ManyToOne(targetEntity="Ticket")
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id")
	 */
	protected $ticket;
}