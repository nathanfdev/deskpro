<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Search
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Search\IndexInitializer;

use Application\DeskPRO\Search\Adapter\AbstractAdapter;
use Orb\Log\Logger;

/**
 * An initializer goes through and resets an index, and indexes all existing content.
 */
abstract class AbstractInitializer
{
	/**
	 * @var \Application\DeskPRO\Search\Adapter\AbstractAdapter
	 */
	protected $adapter;

	/**
	 * @var \Orb\Log\Logger
	 */
	protected $logger;

	public function __construct(AbstractAdapter $adapter, Logger $logger = null)
	{
		$this->adapter = $adapter;
		
		if (!$this->logger) {
			$this->logger = new Logger();
		} else {
			$this->logger = $logger;
		}
	}
}