<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Ricardo Rauch <ricardo@gravityonmars.com>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * Task-Ticket association class.
 *
 * @ORM_Mapping\Entity
 */
class TaskAssociatedTicket extends TaskAssociation
{
  
  /**
   * @var Application\DeskPRO\Entity\Ticket
   * @ORM_Mapping\ManyToOne(targetEntity="Ticket", inversedBy="task_associations")
   * @ORM_Mapping\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="cascade")
   */
  protected $ticket;
  
}
