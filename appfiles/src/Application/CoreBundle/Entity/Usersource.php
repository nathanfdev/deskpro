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

use DeskPRO\Usersource\Handler\AbstractHandler;

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
 * @Table(name="usersources")
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
	 * If this usersource includes a person scraper to fetch contact info, this is it.
	 *
	 * @var Application\CoreBundle\Entity\PersonScraper
	 * @ManyToOne(targetEntity="PersonScraper")
	 * @JoinColumn(name="person_scraper_id", referencedColumnName="id", nullable=true)
	 */
	protected $person_scraper = null;

	/**
	 * The person scraper ID
	 * @var int
	 * @Column(name="person_scraper_id", type="integer", nullable=true)
	 */
	protected $person_scraper_id = null;

	/**
	 * The handler classname. A handler is created from this usersource, and is responsible for
	 * handling things like creating auth adapters etc.
	 *
	 * @var string
	 * @Column(name="handler_class", type="string", length=255)
	 */
	protected $handler_class;

	/**
	 * Options we'll pass to the handler
	 *
	 * @var array
	 * @Column(name="options", type="array")
	 */
	protected $options = array();

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
	 * @var DeskPRO\Usersource\Handler\AbstractHandler
	 */
	protected $_handler_instance = null;



	/**
	 * Get the usersource handler for this usersource.
	 *
	 * @return DeskPRO\Usersource\Handler\AbstractHandler
	 */
	public function getHandler()
	{
		if ($this->_handler_instance !== null) {
			return $this->_handler_instance;
		}

		$classname = $this->handler_class;
		$this->_handler_instance = new $classname($this);

		return $this->_handler_instance;
	}
}