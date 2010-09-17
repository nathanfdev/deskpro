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
 * This record defines the relationship between a Person and a Usersource.
 * 
 * The relationship between RemoteResourse:
 * A Usersource uses a special RemoteResource which basically exists just so auth Identities
 * can be transformed into RemoteRecords, so the standard user field mapping system can be used
 * for those auth adapters that provide additional information. And RemoteRecord is used to store
 * the relationship between the identity and a Person.
 *
 * @see DeskPRO\Auth\UserInitializer
 * @see DeskPRO\RemoteResourceListener\Auth
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="usersource")
 */
class Usersource extends \DeskPRO\Domain\DomainObject
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
	 * A note or description about the user source (admin eyes)
	 *
	 * @var string
	 * @Column(name="note", type="text")
	 */
	protected $note = '';


	/**
	 * The title of this usersource. This SHOULD be a phrase ID so the title can change
	 * based on language.
	 *
	 * @var string
	 * @Column(name="title", type="string", length=255)
	 */
	protected $title = '';


	/**
	 * The description of this usersource. This SHOULD be a phrase ID so the title can change
	 * based on language.
	 *
	 * @var string
	 * @Column(name="description", type="string", length=255)
	 */
	protected $description = '';


	/**
	 * The URL/homepage of this service.
	 *
	 * @var string
	 * @Column(name="url", type="string", length=255)
	 */
	protected $url = '';

	
	/**
	 * Usersource's use a special RemoteResource that just helps transform an Identity
	 * into RemoteRecord and then maps userinfo to Person and PersonField's.
	 *
	 * @var Application\CoreBundle\Entity\RemoteResource
	 * @ManyToOne(targetEntity="RemoteResource")
	 * @JoinColumn(name="remote_resource_id", referencedColumnName="id", nullable=true)
	 */
	protected $remote_resource;


	/**
	 * The typename. This is a simple name that the system will use to base classnames off
	 * of (like the setup).
	 * 
	 * @var string
	 * @Column(name="typename", type="string", length=255)
	 */
	protected $typename;


	/**
	 * Options we'll pass to the adapter
	 * 
	 * @var array
	 * @Column(name="adapter_options", type="array")
	 */
	protected $adapter_options = array();


	/**
	 * The adapter to use. This can also be a static method (ie Whatever::MakeAdapter)
	 * that should return an adapter instead.
	 *
	 * @var string
	 * @Column(name="adapter_class", type="string", length=255)
	 */
	protected $adapter_class = null;


	/**
	 * The order in which to display this source
	 * @var int
	 * @Column(name="display_order", type="integer")
	 */
	protected $display_order = 0;

	
	/**
	 * True if this usersource is enabled/usable.
	 *
	 * @var bool
	 * @Column(name="is_enabled", type="boolean")
	 */
	protected $is_enabled = true;

	

	/**
	 * Get the adapter
	 *
	 * @return Orb\Auth\Adapter\AdapterInterface
	 */
	public function getAdapter()
	{
		static $adapter = null;
		if ($adapter === null) {
			$adapter = DeskPRO\Util::simpleObjectFactory($this->adapter_class, $this->adapter_options);
		}

		return $adapter;
	}
}