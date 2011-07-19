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
use Application\DeskPRO\Search\Searcher\ContentSearcherInterface;

use Application\DeskPRO\Search\SearcherResult\ResultSet;
use Application\DeskPRO\Search\SearcherResult\Result;

/**
 * The content searcher searches: articles, downloads, ideas, news
 */
class ContentSearcher implements ContentSearcherInterface, PersonContextInterface
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
			object_type IN ('article', 'download', 'idea', 'news')
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

	public function labelled(array $labels)
	{
		$label_where = array();

		foreach ($labels as $label) {
			$label_where[] = "+" . MysqlAdapter::encodeLabel($label);
		}

		$label_where = implode(' ', $label_where);

		$where = "
			object_type IN ('article', 'download', 'idea', 'news')
			AND MATCH (content) AGAINST (? IN BOOLEAN MODE)
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

		$total        = App::getDb()->fetchColumn($count_query, array($label_where));
		$results_raw  = App::getDb()->fetchAll($select_query, array($label_where));
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

	/**
	 * Find content similar to $content.
	 *
	 * @param string $content
	 * @param array $in_types Types you want to search in, or null for all
	 * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
	 */
	public function similarContent($content, array $in_types = array())
	{
		throw new \Application\DeskPRO\Search\Searcher\UnsupportedOperation();
	}


	public function omnisearch($query_text)
	{
		return $this->query($query_text);
	}
}