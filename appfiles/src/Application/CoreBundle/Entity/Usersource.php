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
	 * @JoinColumn(name="remote_resource_sysname", referencedColumnName="sysname")
	 */
	protected $remote_resource;


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
	protected $adapter_class;


	/**
	 * The order in which to display this source
	 * @var int
	 * @Column(name="display_order", type="integer")
	 */
	protected $display_order = 0;

	

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