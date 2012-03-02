<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
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
