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

/**
 * Raw email sources
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="email_sources",
 *     indexes={@ORM_Mapping\Index(name="object_idx", columns={"object_type", "object_id"})}
 * )
 */
class EmailSource extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Blob
	 * @ORM_Mapping\ManyToOne(targetEntity="Blob", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="blob_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $blob = null;

	/**
	 * @var \Application\DeskPRO\Entity\EmailGateway
	 * @ORM_Mapping\ManyToOne(targetEntity="EmailGateway")
	 * @ORM_Mapping\JoinColumn(name="gateway_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $gateway = null;

	/**
	 * The type of object this is attached to (should be the table name of
	 * the super type, eg: tickets, people, organizations).
	 *
	 * This typically is not set until after the email is processed (ie
	 * the status is 'processed').
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="object_type", type="string", length=50)
	 */
	protected $object_type = '';

	/**
	 * The ID of the object this is attached to.
	 *
	 * @var int
	 * @ORM_Mapping\Column(name="object_id", type="integer")
	 */
	protected $object_id = '';

	/**
	 * Just the headers portion of the email
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="headers", type="text")
	 */
	protected $headers;

	/**
	 * The current status of the message:
	 * - inserted: Only inserted
	 * - complete: Fully processed
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="status", type="string", length=15)
	 */
	protected $status = 'inserted';

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * The raw source, pieced together.
	 *
	 * This is public on purpose. The AbstractFetcher
	 * sets this property for efficiency in cases where an email
	 * may be processed immediately after being read, we dont
	 * re-fetch the data from the db.
	 *
	 * @var string
	 */
	public $_raw = null;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}



	/**
	 * Get the full raw source of the email
	 *
	 * @return string
	 */
	public function getRawSource()
	{
		if ($this->_raw !== null) return $this->_raw;

		$desc = App::getSystemService('filesystem')->getFileDescriptor($this->blob->id);
		$this->_raw = $desc->get();

		return $this->_raw;
	}
}
