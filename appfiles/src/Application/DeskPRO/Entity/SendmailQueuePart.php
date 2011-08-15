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
 * Raw email sources
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="sendmail_queue_part")
 */
class SendmailQueuePart extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\SendmailQueue
	 * @ORM_Mapping\ManyToOne(targetEntity="SendmailQueue")
	 * @ORM_Mapping\JoinColumn(name="sendmail_queue_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $queue = null;

	/**
	 * @!TODO This needs to be a binary type
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="data", type="text")
	 */
	protected $data;
}
