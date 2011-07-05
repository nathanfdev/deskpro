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
 * Labels on tickets
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="labels_tickets")
 */
class LabelTicket extends LabelAssocAbstract
{
	const LABEL_TYPENAME = 'tickets';
	
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Ticket")
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $ticket;
}