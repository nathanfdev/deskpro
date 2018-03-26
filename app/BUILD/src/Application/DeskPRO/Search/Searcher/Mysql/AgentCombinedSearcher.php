<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search\Searcher\Mysql;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Search\Adapter\MysqlAdapter;
use Application\DeskPRO\Search\SearcherResult\Result;
use Application\DeskPRO\Search\SearcherResult\ResultSet;

/**
 * The combined searcher searches everything: articles, news, downloads, feedback.
 */
class AgentCombinedSearcher
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

    public function query($query_text, $per_page = 25, $page = 1, array $limit_types = null, $top = true)
    {
        $limit_types = \Orb\Util\Arrays::removeFalsey((array) $limit_types);

        // Incase they are label matches, try encoding those as labels
        $words = explode(' ', $query_text);
        foreach ($words as $w) {
            $query_text .= ' '.MysqlAdapter::encodeLabel(strtolower($w));
        }

        if ($limit_types) {
            $limit_types = "'".implode('\',\'', $limit_types)."'";
            $where       = "
                object_type IN ($limit_types)
                AND MATCH (content) AGAINST (? IN BOOLEAN MODE)
            ";
        } else {
            $where = '
                MATCH (content) AGAINST (? IN BOOLEAN MODE)
            ';
        }

        $total = null;
        if (!$top) {
            $count_query = "
                SELECT COUNT(*)
                FROM content_search
                WHERE $where
            ";
            $total = App::getDbRead('search.searcher.combined')->fetchColumn($count_query, [$query_text]);
        }

        $start        = ($page - 1) * $per_page;
        $select_query = "
            SELECT object_type, object_id, MATCH (content) AGAINST (?) AS _relevancy
            FROM content_search
            WHERE $where
            ORDER BY _relevancy DESC
            LIMIT $start, $per_page
        ";

        $results_raw = App::getDbRead('search.searcher.combined')->fetchAll($select_query, [$query_text, $query_text]);
        $results     = [];

        foreach ($results_raw as $result_raw) {
            $result = Result::newFromArray([
                'id'           => $result_raw['object_id'],
                'content_type' => $result_raw['object_type'],
            ]);

            $results[] = $result;
        }

        if ($total === null) {
            $total = count($result);
        }

        $result_set = new ResultSet($total, $results);

        return $result_set;
    }
}
