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
 * Email sources that we need to send
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="sendmail_queue")
 */
class SendmailQueue extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="subject", type="string", length=255)
	 */
	protected $subject;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="to_address", type="string", length=255)
	 */
	protected $to_address;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="attempts", type="integer")
	 */
	protected $attempts = 0;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_next_attempt",type="datetime", nullable=true)
	 */
	protected $date_next_attempt = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_sent",type="datetime", nullable=true)
	 */
	protected $date_sent = null;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="has_sent", type="boolean")
	 */
	protected $has_sent = false;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}
}
