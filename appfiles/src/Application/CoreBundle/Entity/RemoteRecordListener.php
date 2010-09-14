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
 * A RemoteRecordListener describes a class and options that should be notified in the event
 * a RemoteResource is updated. For example, in the contacts system, when we detect that a
 * remote contact entry is updated, we need to perform proper mapping etc.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="remote_record_listener")
 */
class RemoteRecordListener extends \DeskPRO\Domain\DomainObject
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
	 * The remote resource this listener is subscrubed to
	 *
	 * @var Application\CoreBundle\Entity\RemoteResource
	 * @OneToOne(targetEntity="RemoteResource")
	 * @JoinColumn(name="remote_resource_id", referencedColumnName="id")
	 */
	protected $remote_resource;

	/**
	 * The classname of the listener scraper
	 *
	 * @var string
	 * @Column(name="listener_class", type="string", length=255)
	 */
	protected $listener_class;

	/**
	 * Options passed to the scraper
	 *
	 * @var array
	 * @Column(name="listener_options", type="array")
	 */
	protected $listener_options = array();


	/**
	 * Gets the listener object.
	 * 
	 * @return object
	 */
	public function getListener()
	{
		static $listener = null;
		if ($listener === null) {
			$listener = DeskPRO\Util::simpleObjectFactory($this->listener_class, $this->listener_options);
		}

		return $listener;
	}
}