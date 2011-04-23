<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Elastica
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Elastica\IndexInitializer;

use Application\DeskPRO\Elastica\ElasticaManager;
use \Orb\Log\Logger;

/**
 * An initializer goes through and resets an index, and indexes all existing content.
 *
 * Type's are responsible for doing transformations as well as inserting into the index,
 * so these initializers are basically fetchers that also delete/create the actual ES index as well.
 */
abstract class AbstractInitializer
{
	/**
	 * @var \Application\DeskPRO\Elastica\ElasticaManager
	 */
	protected $manager;

	/**
	 * @var \Orb\Log\Logger
	 */
	protected $logger = null;

	public function __construct(ElasticaManager $manager, Logger $log = null)
	{
		$this->manager = $manager;

		// Empty logger if none provided
		if (!$log) {
			$log = new \Orb\Log\Logger();
		}

		$this->logger = $log;
	}
	

	/**
	 * Index all content
	 */
	abstract public function run();
}