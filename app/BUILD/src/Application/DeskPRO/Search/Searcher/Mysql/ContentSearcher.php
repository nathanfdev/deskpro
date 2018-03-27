<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search\Searcher\Mysql;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContextFactory;
use Application\DeskPRO\NewSearch\SearchEngine\UserSearchProxy;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Search\Adapter\MysqlAdapter;
use Application\DeskPRO\Search\Searcher\ContentSearcherInterface;
use Application\DeskPRO\Search\SearcherResult\Result;
use Application\DeskPRO\Search\SearcherResult\ResultSet;
use Orb\Util\Strings;

/**
 * The content searcher searches: articles, downloads, feedback, news.
 */
class ContentSearcher implements ContentSearcherInterface, PersonContextInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var bool
     */
    protected $ignore_perms = false;

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person = $person;

        // Agents in the agent interface dont apply user usergroup permissions
        if ($person->is_agent && defined('DP_INTERFACE') && DP_INTERFACE == 'agent') {
            $this->ignore_perms = true;
        }
    }

    protected function permFilterTypes($types)
    {
        $limit_types = array_combine($types, $types);

        if ($this->person) {
            if (!$this->person->hasPerm('articles.use')) {
                unset($limit_types['article']);
            }
            if (!$this->person->hasPerm('feedback.use')) {
                unset($limit_types['feedback']);
            }
            if (!$this->person->hasPerm('news.use')) {
                unset($limit_types['news']);
            }
            if (!$this->person->hasPerm('downloads.use')) {
                unset($limit_types['download']);
            }
            if (!$this->person->hasPerm('guides.use')) {
                unset($limit_types['guide']);
            }
        }

        return array_values($limit_types);
    }

    public function query($query_text, $per_page = 25, $page = 1, array $limit_types = null, $top = false)
    {
        $limit_types = \Orb\Util\Arrays::removeFalsey($limit_types);
        if (!$limit_types) {
            $limit_types = ['article', 'download', 'feedback', 'news', 'topic'];
        }

        $limit_types = $this->permFilterTypes($limit_types);

        if (!$limit_types) {
            return new ResultSet(0, []);
        }

        $limit_types = "'".implode('\',\'', $limit_types)."'";

        $query_text_orig = $query_text;

        $query_text = Strings::decodeHtmlEntities($query_text);
        $query_text = Strings::decodeUnicodeEntities($query_text);
        $query_text = Strings::utf8_accents_to_ascii($query_text);

        // Specific labels
        if (preg_match_all('#\[(.*?)\]#', $query_text_orig, $m)) {
            foreach ($m[1] as $w) {
                $query_text .= ' '.MysqlAdapter::encodeLabel(strtolower($w));
            }
        }

        $words = explode(' ', $query_text);
        foreach ($words as $w) {
            $query_text .= ' '.MysqlAdapter::encodeLabel(strtolower($w));
        }

        $where = "
            content_search.object_type IN ($limit_types)
            AND MATCH (content_search.content) AGAINST (? IN BOOLEAN MODE)
        ";

        if (!$this->ignore_perms) {
            $permfilter = new \Application\DeskPRO\Search\Adapter\Mysql\PermissionFilter();
            $permfilter->setPersonContext($this->person);
            $perm_join  = $permfilter->getJoin();
            $perm_where = $permfilter->getWhere();
            if (!$perm_where) {
                $perm_where = '1';
            }
        } else {
            $perm_join  = '';
            $perm_where = '1';
        }

        $count_query = "
            SELECT COUNT(*)
            FROM content_search
            $perm_join
            WHERE $perm_where AND $where
        ";

        $start        = ($page - 1) * $per_page;
        $select_query = "
            SELECT content_search.object_type, content_search.object_id, MATCH (content_search.content) AGAINST (?) AS _rel
            FROM content_search
            $perm_join
            WHERE $perm_where AND $where
            ORDER BY _rel DESC
            LIMIT $start, $per_page
        ";

        if ($top) {
            $total = null;
        } else {
            $total = App::getDbRead('search.searcher.content')->fetchColumn($count_query, [$query_text]);
        }

        $results_raw = App::getDbRead('search.searcher.content')->fetchAll($select_query, [$query_text, $query_text]);
        $results     = [];

        foreach ($results_raw as $result_raw) {
            $result = Result::newFromArray([
                'id'           => $result_raw['object_id'],
                'content_type' => $result_raw['object_type'],
            ]);

            $results[] = $result;
        }

        if ($total === null) {
            $total = count($results);
        }

        $result_set = new ResultSet($total, $results);

        return $result_set;
    }

    public function labelled(array $labels, $per_page = 25, $page = 1, array $limit_types = null)
    {
        $limit_types = \Orb\Util\Arrays::removeFalsey($limit_types);
        if (!$limit_types) {
            $limit_types = ['article', 'download', 'feedback', 'news'];
        }

        $limit_types = $this->permFilterTypes($limit_types);

        if (!$limit_types) {
            return new ResultSet(0, []);
        }

        $limit_types = "'".implode('\',\'', $limit_types)."'";

        $label_where = [];

        foreach ($labels as $label) {
            $label_where[] = '+'.MysqlAdapter::encodeLabel($label);
        }

        $label_where = implode(' ', $label_where);

        $where = "
            content_search.object_type IN ($limit_types)
            AND MATCH (content_search.content) AGAINST (? IN BOOLEAN MODE)
        ";

        $contextFactory = new SearchContextFactory(App::$container);
        $context        = $contextFactory->createUserSearchContext($this->person);

        $searchProxy   = new UserSearchProxy(App::$container);
        $dbSearch      = $searchProxy->dbs();
        $contextParams = $dbSearch->buildParams($context);

        $permJoin  = $contextParams['join'];
        $permWhere = $contextParams['where'];
        if (!$permWhere) {
            $permWhere = '1';
        }

        $count_query = "
            SELECT COUNT(*)
            FROM content_search
            $permJoin
            WHERE $permWhere AND $where
        ";

        $start        = ($page - 1) * $per_page;
        $select_query = "
            SELECT content_search.object_type, content_search.object_id, MATCH (content_search.content) AGAINST (?) AS _rel
            FROM content_search
            $permJoin
            WHERE $permWhere AND $where
            ORDER BY _rel DESC
            LIMIT $start, $per_page
        ";

        $total       = App::getDbRead('search.searcher.content')->fetchColumn($count_query, [$label_where]);
        $results_raw = App::getDbRead('search.searcher.content')->fetchAll($select_query, [$label_where, $label_where]);
        $results     = [];

        foreach ($results_raw as $result_raw) {
            $result = Result::newFromArray([
                'id'           => $result_raw['object_id'],
                'content_type' => $result_raw['object_type'],
            ]);

            $results[] = $result;
        }

        $result_set = new ResultSet($total, $results);

        return $result_set;
    }

    /**
     * Find content similar to $content.
     *
     * @param string $content
     * @param array  $in_types Types you want to search in, or null for all
     *
     * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
     */
    public function similarContent($content, array $in_types = [])
    {
        throw new \Application\DeskPRO\Search\Searcher\UnsupportedOperation();
    }

    public function omnisearch($query_text, array $limit_types = null, $per_page = 25, $page = 1)
    {
        $per_page = 25;
        $page     = 1;

        // Fulltext matches
        $r = $this->query($query_text, $per_page, $page, $limit_types, true);
        if ($r->count()) {
            return $r;
        }

        // Otherwise fallback to like
        $limit_types = \Orb\Util\Arrays::removeFalsey($limit_types);
        if (!$limit_types) {
            $limit_types = ['article', 'download', 'feedback', 'news', 'topic'];
        }

        $limit_types = $this->permFilterTypes($limit_types);

        if (!$limit_types) {
            return new ResultSet(0, []);
        }

        $limit_type_names = $limit_types;
        $limit_types      = "'".implode('\',\'', $limit_types)."'";

        $query_text = Strings::decodeHtmlEntities($query_text);
        $query_text = Strings::decodeUnicodeEntities($query_text);
        $query_text = Strings::utf8_accents_to_ascii($query_text);

        $query_words = explode(' ', $query_text);
        if (!$query_words) {
            return $r;
        }

        $params = [];
        $likes  = [];
        foreach ($query_words as $w) {
            if (strlen($w) <= 2) {
                continue;
            }

            $likes[]  = 'content_search.content LIKE ?';
            $params[] = '%'.str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $w).'%';
        }
        if ($likes) {
            $where = "
                content_search.object_type IN ($limit_types)
                AND (".implode(' OR ', $likes).')
            ';

            if (!$this->ignore_perms) {
                $permfilter = new \Application\DeskPRO\Search\Adapter\Mysql\PermissionFilter();
                $permfilter->setPersonContext($this->person);

                if ($limit_type_names) {
                    $permfilter->setTypes($limit_type_names);
                }

                $perm_join  = $permfilter->getJoin();
                $perm_where = $permfilter->getWhere();
                if (!$perm_where) {
                    $perm_where = '1';
                }
            } else {
                $perm_join  = '';
                $perm_where = '1';
            }

            $count_query = "
                SELECT COUNT(*)
                FROM content_search
                $perm_join
                WHERE $perm_where AND $where
                LIMIT $per_page
            ";

            $start        = ($page - 1) * $per_page;
            $select_query = "
                SELECT content_search.object_type, content_search.object_id
                FROM content_search
                $perm_join
                WHERE $perm_where AND $where
                ORDER BY content_search.object_id DESC
                LIMIT $start, $per_page
            ";

            $total = App::getDbRead('search.searcher.content')->fetchColumn($count_query, $params);

            $results_raw = App::getDbRead('search.searcher.content')->fetchAll($select_query, $params);
            $results     = [];

            foreach ($results_raw as $result_raw) {
                $result = Result::newFromArray([
                    'id'           => $result_raw['object_id'],
                    'content_type' => $result_raw['object_type'],
                ]);

                $results[] = $result;
            }
        } else {
            $total   = 0;
            $results = [];
        }

        if ($total === null) {
            $total = count($results);
        }

        $result_set = new ResultSet($total, $results);

        return $result_set;
    }
}
