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

namespace Application\DeskPRO\Search\IndexInitializer;

use Orb\Log\Logger;
use Application\DeskPRO\App;
use Application\DeskPRO\Elastica\Type\AbstractType;

abstract class TicketInitializer extends AbstractInitializer
{
	abstract public function preRun();

	public function run()
	{
		$this->preRun();

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
			$where = "tickets.status IN ('awaiting_agent','awaiting_user')";
			$name = "ticket (open)";
		} else {
			$where = "tickets.status NOT IN ('awaiting_agent','awaiting_user')";
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
			$offset = $i * $per_page;

			$objects = App::getOrm()->createQuery("
				SELECT o
				FROM DeskPRO:Ticket o
				ORDER BY o.id
			")->setMaxResults($per_page)->setFirstResult($offset)->execute();

			$this->adapter->updateObjectsInIndex($objects);
			$this->logger->log("--- Inserted batch $i of $pages", Logger::INFO);

			App::getOrm()->clear();
		}

		$time = sprintf("%.5f", microtime(true)-$time_start);
		$this->logger->log("END $name (took $time seconds)", Logger::INFO);

		return $count;
	}

	public function runForTicketMessages($archive = false)
	{
		if ($archive) {
			$where = "tickets.status IN ('awaiting_agent','awaiting_user')";
			$name = "ticket_messages (open)";
		} else {
			$where = "tickets.status NOT IN ('awaiting_agent','awaiting_user')";
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
			$offset = $i * $per_page;

			$objects = App::getOrm()->createQuery("
				SELECT o
				FROM DeskPRO:TicketMessage o
				ORDER BY o.id
			")->setMaxResults($per_page)->setFirstResult($offset)->execute();

			$this->adapter->updateObjectsInIndex($objects);
			$this->logger->log("--- Inserted batch $i of $pages", Logger::INFO);

			App::getOrm()->clear();
		}

		$time = sprintf("%.5f", microtime(true)-$time_start);
		$this->logger->log("END $name (took $time seconds)", Logger::INFO);

		return $count;
	}
}