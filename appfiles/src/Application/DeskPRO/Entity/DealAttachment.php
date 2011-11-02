<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Abdullah Kiser <kiser.bd@gmail.com>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * Deal attachments
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\DealAttachment")
 * @ORM_Mapping\Table(name="deal_attachments")
 */
class DealAttachment extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Deal
	 * @ORM_Mapping\ManyToOne(targetEntity="Deal")
	 * @ORM_Mapping\JoinColumn(name="deal_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $deal;

	/**
	 * Who created the attachment
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person;

	/**
	 * @var \Application\DeskPRO\Entity\Blob
	 * @ORM_Mapping\ManyToOne(targetEntity="Blob", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="blob_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $blob;

//	/**
//	 * @var \Application\DeskPRO\Entity\TicketMessage
//	 * @ORM_Mapping\ManyToOne(targetEntity="TicketMessage", fetch="EAGER")
//	 * @ORM_Mapping\JoinColumn(name="message_id", referencedColumnName="id")
//	 */
//	protected $message = null;

        /**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;
        
	public function __construct()
	{
		$this->date_created = new \DateTime();
	}
}