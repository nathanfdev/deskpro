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

use Application\DeskPRO\App;
use Application\DeskPRO\Markdown;

/**
 * A raw ticket message without any charset conversion into UTF-8
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="tickets_messages_raw")
 */
class TicketMessageRaw extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\TicketMessage
	 * @ORM_Mapping\ManyToOne(targetEntity="TicketMessage")
	 * @ORM_Mapping\JoinColumn(name="message_id", referencedColumnName="id", onDelete="cascade")
	 * @ORM_Mapping\Id
	 */
	protected $message = null;

	/**
	 * The raw content
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="raw", type="dpblob", length=4294967295)
	 */
	protected $raw = '';

	/**
	 * The charset provided
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="charset", type="string", length=100)
	 */
	protected $charset = 'UNKNOWN';
}
