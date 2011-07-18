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

namespace Application\DeskPRO\Search\IndexInitializer\Elastic;

use Orb\Log\Logger;
use Application\DeskPRO\App;
use Application\DeskPRO\Search\IndexInitializer\TicketInitializer as BaseTicketInitializer;

class TicketInitializer extends BaseTicketInitializer
{
	public function preRun()
	{
		#------------------------------
		# Recreate index
		#------------------------------

		$index = $this->adapter->getClient()->getIndex('content');
		try {
			$index->delete();
			$this->logger->log('Deleted old index', Logger::INFO);
		} catch (\Elastica_Exception_Response $e) {
			// probably means it didnt exist to begin with
			$this->logger->log('Exception while deleting old index. Probably can be ignored. Message: ' . $e->getMessage(), Logger::NOTICE);
		}

		try {
			$index->create();
			$this->logger->log('Created index', Logger::INFO);
		} catch (\Elastica_Exception_Response $e) {
			$this->logger->log('Could not create index. Aborting. Error: ' . $e->getMessage(), Logger::ERR);
			throw $e;
		}
	}
}