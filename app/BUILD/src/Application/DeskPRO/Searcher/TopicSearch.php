<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Searcher;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Topic;
use Orb\Util\Util;

class TopicSearch extends SearcherAbstract
{
    const TERM_ID                = 'id';
    const TERM_STATUS            = 'status';
    const TERM_DELETED           = 'deleted';
    const TERM_HIDDEN_STATUS     = 'hidden_status';
    const TERM_CATEGORY          = 'category';
    const TERM_CATEGORY_SPECIFIC = 'category_specific';
    const TERM_STATUS_CATEGORY   = 'status_category';
    const TERM_NUM_RATINGS       = 'num_ratings';
    const TERM_DATE_CREATED      = 'date_created';
    const TERM_LABEL             = 'label';
    const TERM_QUERY             = 'query';

    const ORDER_ID          = 'id';
    const ORDER_DATE        = 'id';
    const ORDER_NUM_RATINGS = 'num_ratings';

    /**
     * @var bool
     */
    protected $include_hidden = false;

    /**
     * Run the search and return an array of matching ID's.
     *
     * @param int $limit
     *
     * @return array
     */
    public function getMatches(array $limit = null)
    {
        $db = App::getDbRead('search.filter.topics');

        $topicIds = $db->fetchAllCol($this->getSql($limit));

        return $topicIds;
    }

    /**
     * Get actual model objects for matches.
     *
     * @param array $limit
     *
     * @return array
     */
    public function getMatchingObjects(array $limit = null)
    {
        $ids = $this->getMatches($limit);

        if (!$ids) {
            return [];
        }

        return App::getEntityRepository(Topic::class)->getByResultIds($ids);
    }

    /**
     * @return string
     */
    public function getPermWhere()
    {
        if (!$this->person) {
            return '';
        }

        if (!$this->person->hasPerm('guides.use')) {
            return '0';
        }

        $where = '(topics.status != \'hidden\')';

        $disIds = $this->person->PermissionsManager->Guides->getDisallowedCategories();
        if (!$disIds) {
            return $where;
        }

        $disIds = implode(',', $disIds);

        return '('.$where.' AND topics.guide_id NOT IN('.$disIds.'))';
    }

    /**
     * Get the total number of matches.
     *
     * @return int
     */
    public function getCount()
    {
        $sql     = 'SELECT COUNT(*) FROM topics ';
        $parts   = $this->getSqlParts();
        $orderBy = $this->getOrderByPart();

        //------------------------------
        // Add joins
        //------------------------------

        foreach ($parts['joins'] as $j) {
            if (is_array($j)) {
                $sql .= $j[1].' ';
            } else {
                $sql .= "LEFT JOIN $j ON $j.topic_id = topics.id ";
            }
        }

        if (is_array($orderBy)) {
            list($orderJoin, $realOrderBy) = $orderBy;

            $sql .= " $orderJoin ";
        }

        //------------------------------
        // Add wheres
        //------------------------------

        if ($this->include_hidden) {
            $sql .= "WHERE (topics.hidden_status IS NULL OR topics.hidden_status NOT IN ('temp')) AND ";
        } else {
            $sql .= "WHERE (topics.hidden_status IS NULL OR topics.hidden_status NOT IN ('temp', 'deleted')) AND ";
        }
        $wherePerm = $this->getPermWhere();
        if ($wherePerm) {
            $sql .= $wherePerm.' AND ';
        }
        if ($parts['wheres']) {
            $sql .= '('.implode(') AND (', $parts['wheres']).')';
        } else {
            $sql .= '1';
        }

        $count = App::getDbRead('search.filter.topics')->fetchColumn($sql);

        return $count;
    }

    /**
     * Get the SQL query that'll fetch the results.
     *
     * @return string
     */
    public function getSql(array $limit = null)
    {
        $sql = 'SELECT topics.id FROM topics ';

        $parts   = $this->getSqlParts();
        $orderBy = $this->getOrderByPart();

        //------------------------------
        // Add joins
        //------------------------------

        foreach ($parts['joins'] as $j) {
            if (is_array($j)) {
                $sql .= $j[1].' ';
            } else {
                $sql .= "LEFT JOIN $j ON $j.topic_id = topics.id ";
            }
        }

        if (is_array($orderBy)) {
            list($orderJoin, $realOrderBy) = $orderBy;

            $sql .= " $orderJoin ";
            $orderBy = $realOrderBy;
        }

        //------------------------------
        // Add wheres
        //------------------------------

        if ($this->include_hidden) {
            $sql .= "WHERE (topics.hidden_status IS NULL OR topics.hidden_status NOT IN ('temp')) AND ";
        } else {
            $sql .= "WHERE (topics.hidden_status IS NULL OR topics.hidden_status NOT IN ('temp', 'deleted')) AND ";
        }
        $wherePerm = $this->getPermWhere();
        if ($wherePerm) {
            $sql .= $wherePerm.' AND ';
        }
        if ($parts['wheres']) {
            $sql .= '('.implode(') AND (', $parts['wheres']).')';
        } else {
            $sql .= '1';
        }

        $sql .= ' GROUP BY topics.id ';
        $sql .= $orderBy;

        if ($limit) {
            $sql .= " LIMIT {$limit['offset']},{$limit['max']}";
        } else {
            $sql .= ' LIMIT 1000';
        }

        return $sql;
    }

    /**
     * Get the ORDER BY clause based on order info set.
     *
     * @return string
     */
    public function getOrderByPart()
    {
        // Set a default if none
        if (!$this->order_by) {
            $this->order_by = ['id', 'DESC'];
        }

        list($type, $dir) = $this->order_by;

        $dir = strtoupper($dir);
        if ($dir != self::ORDER_ASC and $dir != self::ORDER_DESC) {
            $dir = self::ORDER_DESC;
        }

        $orderBy = '';

        switch ($type) {
            case 'id':
            case 'date':
                $orderBy = "ORDER BY topics.date_published $dir";
                break;

            case 'num_downloads':
                $orderBy = "ORDER BY topics.num_downloads $dir";
                break;

            case 'title':
                $orderBy = "ORDER BY topics.title $dir";
                break;
        }

        return $orderBy;
    }

    /**
     * Get the SQL parts we need in the query.
     *
     * @return array
     */
    public function getSqlParts()
    {
        $db = App::getDbRead('search.filter.topics');

        $wheres = [];
        $joins  = [];

        foreach ($this->terms as $info) {
            $joinId   = Util::requestUniqueId();
            $joinName = "j_$joinId";

            list($term, $op, $choice) = $info;
            $termId                   = null;

            switch ($term) {
                case self::TERM_ID:
                    $choice = isset($choice['ids']) ? $choice['ids'] : $choice;
                    $choice = isset($choice['id']) ? $choice['id'] : $choice;

                    if ($op == self::OP_CONTAINS || is_array($choice)) {
                        if (!is_array($choice)) {
                            $choice = [$choice];
                        }
                        $wheres[] = $this->_choiceMatch('topics.id', 'is', $choice);
                    } else {
                        $wheres[] = $this->_rangeMatch('topics.id', $op, $choice, true);
                    }
                    break;

                case self::TERM_HIDDEN_STATUS:
                    if ($op == 'not') {
                        $wheres[] = '(topics.hidden_status IS NULL OR '.$this->_stringMatch('topics.hidden_status', $op, $choice).')';
                    } else {
                        $wheres[] = $this->_stringMatch('topics.hidden_status', $op, $choice);
                    }
                    break;

                case self::TERM_DELETED:
                    if ($op == self::OP_IS) {
                        $wheres[] = 'topics.hidden_status = \'deleted\'';
                    } else {
                        $wheres[] = 'topics.hidden_status != \'deleted\' OR topics.hidden_status IS NULL';
                    }
                    break;

                case self::TERM_STATUS:

                    $cats        = [];
                    $types       = [];
                    $hiddenTypes = [];

                    foreach ((array) $choice as $c) {
                        if (strpos($c, '.') !== false) {
                            list($hidden, $c) = explode('.', $c, 2);
                        } else {
                            $hidden = false;
                        }
                        if ($hidden === 'hidden') {
                            $hiddenTypes[] = $c;
                        } elseif (ctype_digit($c)) {
                            $cats[] = $c;
                        } else {
                            $types[] = $c;
                            if ($c == 'hidden') {
                                $this->include_hidden = true;
                            }
                        }
                    }

                    // Visible is a special type name
                    if (($k = array_search('visible', $types)) !== false) {
                        unset($types[$k]);
                        $types = array_merge($types, ['new', 'active', 'closed']);
                        $types = array_unique($types);
                    }

                    $partWhere = [];
                    if ($cats) {
                        $partWhere[] = $this->_choiceMatch('topics.status_category_id', $op, $cats);
                    }
                    if ($types) {
                        $partWhere[] = $this->_stringMatch('topics.status', $op, $types);
                    }
                    if ($hiddenTypes) {
                        $partWhere[] = "(topics.status = 'hidden' AND ".$this->_stringMatch('topics.hidden_status', $op, $types).')';
                    }

                    if ($hiddenTypes) {
                        $this->include_hidden = true;
                    }

                    $partWhere = '('.implode(' OR ', $partWhere).')';

                    $wheres[] = $partWhere;

                    break;

                case self::TERM_QUERY:

                    $string = $choice['query'];
                    $type   = !empty($choice['type']) ? $choice['type'] : 'phrase';

                    if (!$string) {
                        break;
                    }

                    $w   = [];
                    $w[] = '('.$this->_stringSearch('topics.title', $op, $string, $type).')';
                    $w[] = '('.$this->_stringSearch('topics.content', $op, $string, $type).')';

                    $wheres[] = implode(' OR ', $w);
                    break;

                case self::TERM_CATEGORY:
                case self::TERM_CATEGORY_SPECIFIC:
                    $baseIds = (array) ((is_array($choice) && isset($choice['category'])) ? $choice['category'] : $choice);
                    $ids     = [];

                    if ($term == self::TERM_CATEGORY_SPECIFIC) {
                        $ids = $baseIds;
                    } else {
                        foreach ($baseIds as $id) {
                            $ids = array_merge($ids, App::getEntityRepository(Guide::class)->getIdsInTree($id, true));
                        }
                    }

                    $ids = array_unique($ids);

                    $wheres[] = $this->_choiceMatch('topics.guide_id', $op, $ids);

                    $this->summary[] = $this->_choiceSummary('Guide', $op, $choice, function ($choice) {
                        $titles = App::getEntityRepository(Guide::class)->getNames((array) $choice);

                        return $titles;
                    });
                    break;

                case self::TERM_DATE_CREATED:
                    $wheres[] = $this->_dateMatch('idaes.date_created', $op, $choice);
                    break;

            }
        }

        $joins = array_unique($joins);

        return [
            'joins'  => $joins,
            'wheres' => $wheres,
        ];
    }
}
