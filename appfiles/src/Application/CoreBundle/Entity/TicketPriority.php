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
 * Ticket priorities
 *
 * @Entity
 * @Table(name="ticket_priorities")
 */
class TicketPriority extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var string
	 * @Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var int
	 * @Column(name="priority", type="integer")
	 */
	protected $priority = 0;
}