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
 * A RemoteObject is actual data that was fetched from some RemoteResource.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="remote_object")
 */
class RemoteObject extends \DeskPRO\Domain\DomainObject
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
	 * The remote object's unique ID. This ID should identify the object on the remote
	 * resource. We use this to track updates etc.
	 *
	 * @var string
	 * @Index
	 * @Column(name="object_id", type="string", length=255)
	 */
	protected $object_id;
	
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
}