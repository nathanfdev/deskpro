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
class ChatConversationSearcher implements PersonContextInterface
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

	public function query($query, $per_page = 25, $page = 1, $top = false)
	{
		$where = "
			object_type = 'chat_conversation'
			AND MATCH (content) AGAINST (?)
		";

		$count_query = "
			SELECT COUNT(*)
			FROM content_search
			WHERE $where
		";

		$start = ($page - 1) * $per_page;
		$select_query = "
			SELECT object_type, object_id, MATCH (content) AGAINST (?) AS _rel
			FROM content_search
			WHERE $where
			ORDER BY _rel
			LIMIT $start, $per_page
		";

		if ($top) {
			$total = null;
		} else {
			$total = App::getDb()->fetchColumn($count_query, array($query));
		}
		$results_raw  = App::getDb()->fetchAll($select_query, array($query, $query));
		$results      = array();

		foreach ($results_raw as $result_raw) {
			$result = Result::newFromArray(array(
				'id' => $result_raw['object_id'],
				'content_type' => $result_raw['object_type'],
			));

			$results[] = $result;
		}

		if ($total === null) {
			$total = count($results);
		}

		$result_set = new ResultSet($total, $results);

		return $result_set;
	}

	public function similar(Ticket $ticket)
	{
		throw new \BadMethodCallException('Similar ticket matching not supported with the Mysql search adapter');
	}
}
