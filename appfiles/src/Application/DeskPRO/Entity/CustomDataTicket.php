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

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * Custom ticket data
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="custom_data_ticket", indexes={
 *     @ORM_Mapping\Index(name="field_id_idx", columns={"field_id","ticket_id"})
 * })
 */
class CustomDataTicket extends CustomDataAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @ORM_Mapping\ManyToOne(targetEntity="Ticket")
	 * @ORM_Mapping\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="cascade")
	 * @ORM_Mapping\Id
	 */
	protected $ticket;

	/**
	 * @var \Application\DeskPRO\Entity\CustomDefTicket
	 * @ORM_Mapping\ManyToOne(targetEntity="CustomDefTicket", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="field_id", referencedColumnName="id", onDelete="cascade")
	 * @ORM_Mapping\Id
	 */
	protected $field = null;

	public function getTicketId()
	{
		return $this->ticket['id'];
	}
}