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

/**
 * Task-Ticket association class.
 *
 * @orm:Entity
 */
class TaskAssociatedTicket extends TaskAssociation
{
  
  /**
   * @var Application\DeskPRO\Entity\Ticket
   * @orm:ManyToOne(targetEntity="Ticket", inversedBy="task_associations")
   * @orm:JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="cascade")
   */
  protected $ticket;
  
}
