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
 * A RemoteRecord is actual data that was fetched from some RemoteResource.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="remote_records")
 */
class RemoteRecord extends \DeskPRO\Domain\DomainObject
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
	 * If this record belongs to a person.
	 *
	 * @var \Application\CoreBundle\Person
	 * @OneToOne(targetEntity="Person")
	 * @JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * The remote records unique ID. This ID should identify the object on the remote
	 * resource. We use this to track updates etc. Example: UserID
	 *
	 * @var string
	 * @Index
	 * @Column(name="identity", type="string", length=255)
	 */
	protected $identity;

	/**
	 * The remote records friendly ID. For example, the $record_id might be a UserID,
	 * this would be the username.
	 *
	 * @var string
	 * @Index
	 * @Column(name="identity_friendly", type="string", length=255, nullable=true)
	 */
	protected $identity_friendly = null;
	
	/**
	 * The remote resource this item belongs to
	 *
	 * @var Application\CoreBundle\Entity\RemoteResource
	 * @OneToOne(targetEntity="RemoteResource")
	 * @JoinColumn(name="remote_resource_id", referencedColumnName="id")
	 */
	protected $remote_resource;

	/**
	 * The object data
	 *
	 * @var array
	 * @Column(name="data", type="array")
	 */
	protected $data = array();

	/**
	 * Whent he object was first created in the system
	 *
	 * @var \DateTime
	 * @Column(name="created_at",type="datetime")
	 */
	protected $created_at;

	/**
	 * When the object was last updated
	 *
	 * @var \DateTime
	 * @Column(name="updated_at",type="datetime")
	 */
	protected $updated_at;


	/**
	 * Assign new values from a scraper Item.
	 * 
	 * @param \Orb\Scraper\Item $item
	 */
	public function fromScraperItem(\Orb\Scraper\Item $item)
	{
		$this['data'] = $item->getData();
		$this['identity'] = $item->getIdentity();
		$this['friendly_identity'] = $item->getFriendlyIdentity();
	}
}