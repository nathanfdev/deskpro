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
 * Custom ticket data
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="custom_data_ticket", indexes={
 *     @orm:Index(name="field_id_idx", columns={"field_id","ticket_id"})
 * })
 */
class CustomDataTicket extends CustomDataAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @orm:ManyToOne(targetEntity="Ticket")
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id")
	 * @orm:Id
	 */
	protected $ticket;

	/**
	 * @var \Application\DeskPRO\Entity\CustomDefTicket
	 * @orm:ManyToOne(targetEntity="CustomDefTicket")
	 * @orm:JoinColumn(name="field_id", referencedColumnName="id")
	 * @orm:Id
	 */
	protected $field = null;

	public function getTicketId()
	{
		return $this->ticket['id'];
	}
}