<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Search
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Search\Searcher\Mysql;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonContextInterface;

use Application\DeskPRO\Search\Adapter\MysqlAdapter;
use Application\DeskPRO\Search\Searcher\TicketSearcherInterface;

use Application\DeskPRO\Search\SearcherResult\ResultSet;
use Application\DeskPRO\Search\SearcherResult\Result;

/**
 * The content searcher searches: tickets
 */
class TicketSearcher implements TicketSearcherInterface, PersonContextInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @param \Application\DeskPRO\Entity\Person $person
	 */
	public function setPersonContext(Person $person)
	{
		$this->person = $person;
	}

	public function query($query)
	{
		$where = "
			object_type = 'ticket'
			AND MATCH (content) AGAINST (?)
		";

		$count_query = "
			SELECT COUNT(*)
			FROM content_search
			WHERE $where
		";

		$select_query = "
			SELECT object_type, object_id
			FROM content_search
			WHERE $where
		";

		$total        = App::getDb()->fetchColumn($count_query, array($query));
		$results_raw  = App::getDb()->fetchAll($select_query, array($query));
		$results      = array();

		foreach ($results_raw as $result_raw) {
			$result = Result::newFromArray(array(
				'id' => $result_raw['object_id'],
				'content_type' => $result_raw['object_type'],
			));

			$results[] = $result;
		}

		$result_set = new ResultSet($total, $results);

		return $result_set;
	}

	public function similar(Ticket $ticket)
	{
		throw new \BadMethodCallException('Similar ticket matching not supported with the Mysql search adapter');
	}
}