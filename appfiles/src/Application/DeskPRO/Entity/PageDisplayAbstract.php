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

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\Entity;

/**
 * Standard base for storing display information, such as fields or widgets on a page.
 *
 * @see \Application\DeskPRO\PageDisplay\Zone\BasicZone
 * @orm:MappedSuperclass
 */
abstract class PageDisplayAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The actual section within the page that this description applies (ex 'toptabs')
	 *
	 * @var string
	 * @orm:Column(name="section", type="string", length=50)
	 */
	protected $section = 'default';

	/**
	 * This is a plain data array that is fed into the handler class
	 * to reconstruct the display strcuture.
	 *
	 * @var array
	 * @orm:Column(name="data", type="array")
	 */
	protected $data;
	

	/**
	 * Get a new handler
	 */
	public function createHandler()
	{
		$class = $this->handler_class;
		$obj = new $class($this);

		return $obj;
	}
}