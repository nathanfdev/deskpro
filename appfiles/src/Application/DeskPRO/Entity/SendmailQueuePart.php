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
 * Raw email sources
 *
 * @orm:Entity
 * @orm:Table(name="sendmail_queue_part")
 */
class SendmailQueuePart extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\SendmailQueue
	 * @orm:ManyToOne(targetEntity="SendmailQueue")
	 * @orm:JoinColumn(name="sendmail_queue_id", referencedColumnName="id")
	 */
	protected $queue = null;

	/**
	 * @TODO This needs to be a binary type
	 *
	 * @var string
	 * @orm:Column(name="data", type="text")
	 */
	protected $data;
}
