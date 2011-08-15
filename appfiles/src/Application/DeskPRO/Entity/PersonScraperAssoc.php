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
 * This tracks associations between a Person and a PersonScraper.
 *
 * If the PersonScraper is tied to a Usersource, then this assoc
 * record may look very much like the PersonUsersourceAssoc. We need both
 * because technically Usersources and PersonScrapers are two distinct subsystems,
 * we just offer a way to tie them together aesthetically because they are often found together.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="person_scraper_assoc")
 */
class PersonScraperAssoc
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 * 
	 */
	protected $id;

	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", inversedBy="emails")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person;

	/**
	 * The scraper the person is connected to
	 *
	 * @var Usersource
	 * @ORM_Mapping\OneToOne(targetEntity="Usersource")
	 * @ORM_Mapping\JoinColumn(name="person_scraper_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $scraper;

	/**
	 * The person scraper ID
	 * @var int
	 * @ORM_Mapping\Column(name="person_scraper_id", type="integer")
	 */
	protected $person_scraper_id;

	/**
	 * The remote users unique ID. This should change, so it's smart if this is a system
	 * ID such as a UserID. This value is used to map our local Person to the remote Person.
	 *
	 * If null, it means this assoc doesn't really exist, we're using it to keep track of
	 * auto-discovery attempts.
	 *
	 * @var string
	 * @ORM_Mapping\Index
	 * @ORM_Mapping\Column(name="identity", type="string", length=255, nullable=true)
	 */
	protected $identity;

	/**
	 * The raw data the scraper gave us. The data is still "raw", but it should be decoded
	 * into a sensible data structure (eg: If we got JSON, we're storing the decoded value,
	 * not the raw JSON string).
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="raw_data", type="array")
	 */
	protected $data = array();

	/**
	 * When the record was first created in the system
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="created_at", type="datetime")
	 */
	protected $created_at;

	/**
	 * When the record was last updated in the system.
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="updated_at", type="datetime")
	 */
	protected $updated_at;

	/**
	 * If the remote resource reports when it was created, this is when
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="remote_created_at", type="datetime", nullable=true)
	 */
	protected $remote_created_at = null;

	/**
	 * If the remote resource reports when it was updated, this is when.
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="remote_updated_at", type="datetime", nullable=true)
	 */
	protected $remote_updated_at = null;


	public function __construct()
	{
		$this->data = new \Doctrine\Common\Collection\ArrayCollection();
	}


	/** @ORM_Mapping\PrePersist */
	public function __incCreatedAt()
	{
		if (!$this->created_at) {
			$this->created_at = new \DateTime();
		}
		$this->updated_at = new \DateTime();
	}

	/** @ORM_Mapping\PreUpdate */
	public function __incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}
}
