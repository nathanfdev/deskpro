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
 * A scraper is something that fetches data from a remote resource. This is the abstract
 * scraper type, but each different type of scrape defines its own entity and may have
 * its own dramatically different processing/handling.
 *
 * Actual scrapers are also responsible for how to store any scraped data (hence there is no
 * use in an abstract ScraperData class).
 *
 * @ORM_Mapping\MappedSuperclass
 */
abstract class Scraper
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
	 * The handler classname. A handler is created from this usersource, and is responsible for
	 * creating all the resources needed for a scraper to do its job.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="handler_class", type="string", length=255)
	 */
	protected $handler_class;

	/**
	 * Options we'll pass to the handler
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="options", type="array")
	 */
	protected $options = array();

	/**
	 * True if this scraper is enabled
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_enabled", type="boolean")
	 */
	protected $is_enabled = true;

	/**
	 * @var Application\DeskPRO\Scraper\Handler\HandlerAbstract
	 */
	protected $_handler_instance = null;
}
