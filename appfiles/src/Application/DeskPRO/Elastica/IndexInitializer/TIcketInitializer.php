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

use Orb\Log\Logger;
use Application\DeskPRO\App;
use Application\DeskPRO\Elastica\Type\AbstractType;

class ContentInitializer extends AbstractInitializer
{
	public function run()
	{
		#------------------------------
		# Recreate index
		#------------------------------

		$tickets_index = $this->manager->getIndex('tickets');
		try {
			$tickets_index->delete();
			$this->logger->log('Deleted old `tickets` index', Logger::INFO);
		} catch (\Elastica_Exception_Response $e) {
			// probably means it didnt exist to begin with
			$this->logger->log('Exception while deleting old `tickets` index. Probably can be ignored. Message: ' . $e->getMessage(), Logger::NOTICE);
		}

		$tickets_active_index = $this->manager->getIndex('tickets_active');
		try {
			$tickets_active_index->delete();
			$this->logger->log('Deleted old `tickets_active` index', Logger::INFO);
		} catch (\Elastica_Exception_Response $e) {
			// probably means it didnt exist to begin with
			$this->logger->log('Exception while deleting old `tickets_active` index. Probably can be ignored. Message: ' . $e->getMessage(), Logger::NOTICE);
		}

		try {
			$tickets_index->create();
			$this->logger->log('Created `tickets` index', Logger::INFO);
		} catch (\Elastica_Exception_Response $e) {
			$this->logger->log('Could not create `tickets` index. Aborting. Error: ' . $e->getMessage(), Logger::ERR);
			return 0;
		}

		try {
			$tickets_active_index->create();
			$this->logger->log('Created `tickets_active` index', Logger::INFO);
		} catch (\Elastica_Exception_Response $e) {
			$this->logger->log('Could not create `tickets_active` index. Aborting. Error: ' . $e->getMessage(), Logger::ERR);
			return 0;
		}

		#------------------------------
		# Run through each content type
		#------------------------------

		$time_start = microtime(true);
		$total = 0;
		try {
			$total += $this->runForTickets(false);
			$total += $this->runForTicketMessages(false);
			$total += $this->runForTickets(true);
			$total += $this->runForTicketMessages(true);
		} catch (\Exception $e) {
			$this->logger->log('Exception: ' . $e->getMessage(), Logger::ERR);
			throw $e;
		}

		$time = sprintf("%.5f", microtime(true)-$time_start);
		$this->logger->log("Indexed $total items in $time seconds", Logger::INFO);

		return $total;
	}

	public function runForTickets($archive = false)
	{
		if ($archive) {
			$where = "tickets.status IN ('open','pending')";
			$name = "ticket (open)";
		} else {
			$where = "tickets.status NOT IN ('open','pending')";
			$name = "ticket (archived)";
		}

		$count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM tickets WHERE $where");
		if (!$count) {
			$this->logger->log("No $name objects", Logger::INFO);
		}

		$time_start = microtime(true);
		$this->logger->log("START $name ($count objects)", Logger::INFO);

		$per_page = 25;
		$pages = ceil($count / $per_page);

		for ($i = 0; $i < $pages; $i++) {
			$documents = array();
			$offset = $i * $per_page;

			$objects = App::getOrm()->createQuery("
				SELECT o
				FROM DeskPRO:Ticket o
				ORDER BY o.id
			")->setMaxResults($per_page)->setFirstResult($offset)->execute();

			foreach ($objects as $object) {
				$doc = App::get('deskpro.elastica.types.ticket')->transformToDocument($object);
				$documents[] = $doc;
			}

			App::getOrm()->clear();

			$this->manager->getClient()->addDocuments($documents);
			$this->logger->log("--- Inserted batch $i of $pages", Logger::INFO);
		}

		$time = sprintf("%.5f", microtime(true)-$time_start);
		$this->logger->log("END $name (took $time seconds)", Logger::INFO);

		return $count;
	}

	public function runForTicketMessages($archive = false)
	{
		if ($archive) {
			$where = "tickets.status IN ('open','pending')";
			$name = "ticket_messages (open)";
		} else {
			$where = "tickets.status NOT IN ('open','pending')";
			$name = "ticket_messages (archived)";
		}

		$count = App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM ticket_messages
			LEFT JOIN tickets ON (ticket_messages.ticket_id = tickets.id)
			WHERE $where
		");
		if (!$count) {
			$this->logger->log("No $name objects", Logger::INFO);
		}

		$time_start = microtime(true);
		$this->logger->log("START $name ($count objects)", Logger::INFO);

		$per_page = 25;
		$pages = ceil($count / $per_page);

		for ($i = 0; $i < $pages; $i++) {
			$documents = array();
			$offset = $i * $per_page;

			$objects = App::getOrm()->createQuery("
				SELECT o
				FROM DeskPRO:TicketMessage o
				ORDER BY o.id
			")->setMaxResults($per_page)->setFirstResult($offset)->execute();

			foreach ($objects as $object) {
				$doc = App::get('deskpro.elastica.types.ticket_message')->transformToDocument($object);
				$documents[] = $doc;
			}

			App::getOrm()->clear();

			$this->manager->getClient()->addDocuments($documents);
			$this->logger->log("--- Inserted batch $i of $pages", Logger::INFO);
		}

		$time = sprintf("%.5f", microtime(true)-$time_start);
		$this->logger->log("END $name (took $time seconds)", Logger::INFO);

		return $count;
	}
}