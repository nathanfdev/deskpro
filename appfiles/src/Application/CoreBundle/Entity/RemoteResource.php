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
use Orb\Util\Strings;
use Orb\Util\Arrays;


/**
 * A RemoteResource describes some external resource that DeskPRO fetches data
 * from. Every resource here has a corresponding scraper that does the actual
 * data-fetching.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="remote_resource")
 */
class RemoteResource extends \DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The system name for the resource. This is so system-level
	 * resources can be linked easily with other entities.
	 *
	 * @var string
	 * @Column(name="sysname", type="string", length=255, nullable=true)
	 */
	protected $sysname = null;
	
	/**
	 * A group or type of resource. For example, there might be multiple user info scrapers,
	 * so it's useful to classify them under the same group.
	 *
	 * @var string
	 * @Index
	 * @Column(name="groupname", type="string", length=255, nullable=true)
	 */
	protected $groupname;

	/**
	 * A note or description about the resource
	 *
	 * @var string
	 * @Column(name="note", type="text")
	 */
	protected $note;

	/**
	 * The classname of the corresponding scraper
	 * 
	 * @var string
	 * @Column(name="scraper_class", type="string", length=255)
	 */
	protected $scraper_class;

	/**
	 * Options passed to the scraper
	 *
	 * @var array
	 * @Column(name="scraper_options", type="array")
	 */
	protected $scraper_options = array();

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @OneToMany(targetEntity="RemoteRecordListener", mappedBy="remote_resource")
	 */
	protected $listeners;

	/**
	 * How often to repoll to update this resource. Null means no polling.
	 *
	 * @var int
	 * @Column(name="poll_cycle", type="integer", nullable=true)
	 */
	protected $poll_cycle = null;

	/**
	 * How often to poll to check if a resource exists (i.e., checking to see
	 * if Highrise has contact info for a person). Null means no polling.
	 *
	 * @var int
	 * @Column(name="poll_discovery_cycle", type="integer", nullable=true)
	 */
	protected $poll_discovery_cycle = null;



	/**
	 * Get an instance of the scraper
	 * 
	 * @return Orb\Scraper\AbstractScraper
	 */
	public function getScraper()
	{
		static $scraper = null;
		if ($scraper === null) {
			$scraper = DeskPRO\Util::simpleObjectFactory($this->scraper_class, $this->scraper_options);
		}

		return $scraper;
	}



	/**
	 * Notify all listeners that a record was updated or created.
	 */
	public function notifyListeners(RemoteRecord $record)
	{
		foreach ($this->listeners as $listener) {
			$listener->getListener()->remoteRecordUpdated($this, $record);
		}
	}
}