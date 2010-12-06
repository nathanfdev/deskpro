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

namespace Application\DeskPRO\Entity;

/**
 * A person scraper fetches information about a Person from a remote resource.
 * For example, to keep information up-to-date using a pre-existing contact management product.
 *
 * Some PersonScrapers might be attached to usersources. Usersources only responsibility is to offer
 * authentication, so PersonScrapers take care of bringing over other data about an identity.
 *
 * @orm:Entity
 * @orm:Table(name="person_scraper")
 */
class PersonScraper extends Scraper
{
	/**
	 * The usersource that this scraper is attached to
	 *
	 * @var Usersource
	 * @orm:OneToOne(targetEntity="Usersource")
	 * @orm:JoinColumn(name="usersource_id", referencedColumnName="id")
	 */
	protected $usersource;

	/**
	 * The usersource ID
	 * @var int
	 * @orm:Column(name="usersource_id", type="integer", nullable=true)
	 */
	protected $usersource_id = null;

	/**
	 * How often to automatically check the remote resource for updates. Null
	 * means disabled.
	 *
	 * @var int
	 * @orm:Column(name="poll_interval", type="integer", nullable=true)
	 */
	protected $poll_interval = null;

	/**
	 * Some scrapers can try to automatically search for a remote record
	 * using local data (for example, using an email address).
	 *
	 * This is the time between auto discovery checks. Null means disabled.
	 *
	 * @var int
	 * @orm:Column(name="poll_discovery_interval", type="integer", nullable=true)
	 */
	protected $poll_discovery_interval = null;

	

	/**
	 * @return Application\DeskPRO\Usersource\ScraperHandler\ScraperHandlerInterface
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
