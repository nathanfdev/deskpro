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
 * Ticket attachments
 *
 * @orm:Entity
 * @orm:Table(name="tickets_attachments")
 */
class TicketAttachment extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id;

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="attachment_id", type="integer")
	 */
	protected $attachment_id;

	/**
	 * @var int
	 * @orm:Column(name="message_id", type="integer")
	 */
	protected $message_id;
}