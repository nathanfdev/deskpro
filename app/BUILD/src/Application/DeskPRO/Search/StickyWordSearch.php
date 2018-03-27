<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Searcher\ArticleSearch;
use Application\DeskPRO\Searcher\DownloadSearch;
use Application\DeskPRO\Searcher\FeedbackSearch;
use Application\DeskPRO\Searcher\NewsSearch;
use Application\DeskPRO\Searcher\TopicSearch;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;

/**
 * This finds sticky results for a search term.
 */
class StickyWordSearch implements PersonContextInterface
{
    /**
     * Entity manager.
     *
     * @var \Doctrine\ORM\EntityManager
     */
    public $em;

    /**
     * Plain database connection for raw queries.
     *
     * @var \Application\DeskPRO\DBAL\Connection
     */
    public $db;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person_context;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->db = $em->getConnection();
    }

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;
    }

    /**
     * @param string $query
     *
     * @return array|mixed
     */
    public function getWordsFromQuery($query)
    {
        // Split query into words, quoted strings are grouped togehter
        $words = preg_split(
            '/[\\s,]*\\"([^\\"]+)\\"[\\s,]*|[\\s,]+/',
            $query,
            0,
            PREG_SPLIT_DELIM_CAPTURE
        );

        $words = array_filter($words, function ($w) {
            if (strlen($w) >= 2 && strlen($w) <= 50) {
                return true;
            }

            return false;
        });
        $words = Arrays::removeFalsey($words);
        $words = array_values($words);

        return $words;
    }

    /**
     * @param string $type
     * @param int    $id
     * @param int    $limit
     *
     * @return array
     */
    public function getStickyWords($type, $id, $limit = 5)
    {
        $ret = [];
        $res = $this->db->executeQuery(sprintf('
            SELECT word
            FROM search_sticky_result
            WHERE object_type = :type AND object_id = :id
            LIMIT %d
        ', $limit), ['type' => $type, 'id' => $id]);

        while ($word = $res->fetchColumn()) {
            $ret[] = $word;
        }

        return $ret;
    }

    /**
     * @param string $query
     * @param int    $limit
     * @param array  $limit_types Which types to search in
     *
     * @return array
     */
    public function getResults($query, $limit = 10, $limit_types = ['article', 'news', 'download', 'feedback', 'topic'])
    {
        $words = $this->getWordsFromQuery($query);

        if (!$words) {
            return [];
        }

        array_unshift($words, $query);

        if (count($words) > 15) {
            $words = array_slice($words, 0, 15);
        }

        // Convert input $limit_types to the type strings used in the db
        $limit_types = array_map(function ($t) {
            switch ($t) {
                case 'article':  return 'DeskPRO:Article';
                case 'news':     return 'DeskPRO:News';
                case 'download': return 'DeskPRO:Download';
                case 'feedback': return 'DeskPRO:Feedback';
                case 'topic':    return 'DeskPRO:Topic';
                default: return $t;
            }
        }, $limit_types);

        $results_raw = $this->db->fetchAll('
            SELECT object_type, object_id
            FROM search_sticky_result
            WHERE word IN (?) AND object_type IN (?)
            ORDER BY object_id DESC
            LIMIT 1000
        ', [$words, $limit_types], [Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY]);

        if (!$results_raw) {
            return [];
        }

        //------------------------------
        // Need to verify the user can see
        // the results that we matched
        //------------------------------

        $check_ids = [
            'DeskPRO:Article'  => [],
            'DeskPRO:News'     => [],
            'DeskPRO:Download' => [],
            'DeskPRO:Feedback' => [],
            'DeskPRO:Topic'    => [],
        ];

        if (empty($check_ids)) {
            return [];
        }

        $valid_ids = $check_ids; //copy structure

        if ($this->person_context) {
            foreach ($results_raw as $r) {
                $check_ids[$r['object_type']][] = $r['object_id'];
            }

            if ($check_ids['DeskPRO:Article']) {
                $search = new ArticleSearch();
                $search->setPersonContext($this->person_context);
                $search->addTerm(ArticleSearch::TERM_ID, ArticleSearch::OP_CONTAINS, $check_ids['DeskPRO:Article']);
                $valid_ids['DeskPRO:Article'] = $search->getMatches();
            }
            if ($check_ids['DeskPRO:News']) {
                $search = new NewsSearch();
                $search->setPersonContext($this->person_context);
                $search->addTerm(NewsSearch::TERM_ID, NewsSearch::OP_CONTAINS, $check_ids['DeskPRO:News']);
                $valid_ids['DeskPRO:News'] = $search->getMatches();
            }
            if ($check_ids['DeskPRO:Download']) {
                $search = new DownloadSearch();
                $search->setPersonContext($this->person_context);
                $search->addTerm(DownloadSearch::TERM_ID, DownloadSearch::OP_CONTAINS, $check_ids['DeskPRO:Download']);
                $valid_ids['DeskPRO:Download'] = $search->getMatches();
            }
            if ($check_ids['DeskPRO:Feedback']) {
                $search = new FeedbackSearch();
                $search->setPersonContext($this->person_context);
                $search->addTerm(FeedbackSearch::TERM_ID, FeedbackSearch::OP_CONTAINS, $check_ids['DeskPRO:Feedback']);
                $valid_ids['DeskPRO:Feedback'] = $search->getMatches();
            }
            if ($check_ids['DeskPRO:Topic']) {
                $search = new TopicSearch();
                $search->setPersonContext($this->person_context);
                $search->addTerm(TopicSearch::TERM_ID, TopicSearch::OP_CONTAINS, $check_ids['DeskPRO:Topic']);
                $valid_ids['DeskPRO:Topic'] = $search->getMatches();
            }
        } else {
            // No person context means any of the matches are valid
            // (the user interface will always have a context though)
            $valid_ids = $check_ids;
        }

        // Key them for isset() lookups below
        $valid_ids = array_map(function ($v) {
            return $v ? array_combine($v, $v) : $v;
        }, $valid_ids);

        //------------------------------
        // Get and sort the results
        //------------------------------

        // Count matches
        $results_ranked = [];
        foreach ($results_raw as $r) {
            // Make sure the result is within the list of valid
            // ids we got back from our search verify above
            if (!isset($valid_ids[$r['object_type']][$r['object_id']])) {
                continue;
            }
            $k = "{$r['object_type']}-{$r['object_id']}";
            if (!isset($results_ranked[$k])) {
                $results_ranked[$k]          = $r;
                $results_ranked[$k]['count'] = 0;
            }

            ++$results_ranked[$k]['count'];
        }

        // If we have too many results, we have to trim them down
        // to the top $limit results
        if (count($results_ranked) > $limit) {
            Arrays::sortMulti($results_ranked, 'count', \SORT_NUMERIC);
            $results_ranked = array_slice($results_ranked, 0, $limit, true);
        }

        // Get IDs for each type
        $results_typed = [];
        foreach ($results_ranked as $r) {
            if (!isset($results_typed[$r['object_type']])) {
                $results_typed[$r['object_type']] = [];
            }
            $results_typed[$r['object_type']][] = $r['object_id'];
        }

        // Fetech actual objects
        $real_results = [];
        foreach ($results_typed as $entity_name => $ids) {
            $real_results = array_merge(
                $real_results,
                array_values($this->em->getRepository($entity_name)->getByIds($ids))
            );
        }

        // Sort
        usort($real_results, function ($a, $b) use ($results_ranked) {
            $class = get_class($a);
            $entity_name = $class::getEntityName();
            $ka = "$entity_name-{$a['id']}";

            $class = get_class($b);
            $entity_name = $class::getEntityName();
            $kb = "$entity_name-{$b['id']}";

            if (isset($results_ranked[$ka]) && !isset($results_ranked[$kb])) {
                return -1;
            }
            if (!isset($results_ranked[$ka]) && isset($results_ranked[$kb])) {
                return 1;
            }
            if (!isset($results_ranked[$ka]) && !isset($results_ranked[$kb])) {
                return 0;
            }

            if ($results_ranked[$ka]['count'] == $results_ranked[$kb]['count']) {
                return 0;
            }

            return ($results_ranked[$ka]['count'] < $results_ranked[$kb]['count']) ? -1 : 1;
        });

        // Make them a usual array we expect
        // type => typename, object => entity
        $typed_results = [];
        foreach ($real_results as $r) {
            $class       = get_class($r);
            $entity_name = $class::getEntityName();
            $type        = strtolower(str_replace('DeskPRO:', '', $entity_name));

            $key = $type.'.'.$r->getId();

            $typed_results[$key] = [
                'type'   => $type,
                'object' => $r,
            ];
        }

        return $typed_results;
    }
}
