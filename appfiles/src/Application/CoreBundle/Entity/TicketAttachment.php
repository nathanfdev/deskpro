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
 * Ticket attachments
 *
 * @Entity
 * @Table(name="tickets_attachments")
 */
class TicketAttachment extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @Id @Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id;

	/**
	 * @var int
	 * @Id @Column(name="attachment_id", type="integer")
	 */
	protected $attachment_id;

	/**
	 * @var int
	 * @Column(name="message_id", type="integer")
	 */
	protected $message_id;
}