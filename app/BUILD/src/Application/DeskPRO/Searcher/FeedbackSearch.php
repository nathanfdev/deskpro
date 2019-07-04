<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Searcher;

use Application\DeskPRO\App;
use Orb\Util\Util;

class FeedbackSearch extends SearcherAbstract
{
    const TERM_ID                = 'id';
    const TERM_STATUS            = 'status';
    const TERM_BRAND             = 'brand';
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
        $db = App::getDbRead('search.filter.feedback');

        $topic_ids = $db->fetchAllCol($this->getSql($limit));

        return $topic_ids;
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

        return App::getEntityRepository('DeskPRO:CommunityTopic')->getByResultIds($ids);
    }

    /**
     * @return string
     */
    public function getPermWhere()
    {
        if (!$this->person) {
            return '';
        }

        if (!$this->person->hasPerm('feedback.use')) {
            return '0';
        }

        $where = '(community_topics.status != \'hidden\')';

        $dis_ids = $this->person->PermissionsManager->FeedbackCategories->getDisallowedCategories();
        if (!$dis_ids) {
            return $where;
        }

        $dis_ids = implode(',', $dis_ids);

        return '('.$where.' AND community_topics.channel_id NOT IN('.$dis_ids.'))';
    }

    /**
     * Get the total number of matches.
     *
     * @return int
     */
    public function getCount()
    {
        $sql      = 'SELECT COUNT(*) FROM community_topics ';
        $parts    = $this->getSqlParts();
        $order_by = $this->getOrderByPart();

        //------------------------------
        // Add joins
        //------------------------------

        foreach ($parts['joins'] as $j) {
            if (is_array($j)) {
                $sql .= $j[1].' ';
            } else {
                $sql .= "LEFT JOIN $j ON $j.topic_id = community_topics.id ";
            }
        }

        if (is_array($order_by)) {
            list($order_join, $real_order_by) = $order_by;

            $sql .= " $order_join ";
        }

        //------------------------------
        // Add wheres
        //------------------------------

        if ($this->include_hidden) {
            $sql .= "WHERE (community_topics.hidden_status IS NULL OR community_topics.hidden_status NOT IN ('temp')) AND ";
        } else {
            $sql .= "WHERE (community_topics.hidden_status IS NULL OR community_topics.hidden_status NOT IN ('temp', 'deleted')) AND ";
        }
        $where_perm = $this->getPermWhere();
        if ($where_perm) {
            $sql .= $where_perm.' AND ';
        }
        if ($parts['wheres']) {
            $sql .= '('.implode(') AND (', $parts['wheres']).')';
        } else {
            $sql .= '1';
        }

        $count = App::getDbRead('search.filter.feedback')->fetchColumn($sql);

        return $count;
    }

    /**
     * Get the SQL query that'll fetch the results.
     *
     * @return string
     */
    public function getSql(array $limit = null)
    {
        $sql = 'SELECT community_topics.id FROM community_topics ';

        $parts    = $this->getSqlParts();
        $order_by = $this->getOrderByPart();

        //------------------------------
        // Add joins
        //------------------------------

        foreach ($parts['joins'] as $j) {
            if (is_array($j)) {
                $sql .= $j[1].' ';
            } else {
                $sql .= "LEFT JOIN $j ON $j.topic_id = community_topics.id ";
            }
        }

        if (is_array($order_by)) {
            list($order_join, $real_order_by) = $order_by;

            $sql .= " $order_join ";
            $order_by = $real_order_by;
        }

        //------------------------------
        // Add wheres
        //------------------------------

        if ($this->include_hidden) {
            $sql .= "WHERE (community_topics.hidden_status IS NULL OR community_topics.hidden_status NOT IN ('temp')) AND ";
        } else {
            $sql .= "WHERE (community_topics.hidden_status IS NULL OR community_topics.hidden_status NOT IN ('temp', 'deleted')) AND ";
        }
        $where_perm = $this->getPermWhere();
        if ($where_perm) {
            $sql .= $where_perm.' AND ';
        }
        if ($parts['wheres']) {
            $sql .= '('.implode(') AND (', $parts['wheres']).')';
        } else {
            $sql .= '1';
        }

        $sql .= ' GROUP BY community_topics.id ';
        $sql .= $order_by;

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

        $order_by = '';

        switch ($type) {
            case 'id':
            case 'date_created':
                $order_by = "ORDER BY community_topics.date_published $dir";
                break;

            case 'i-voted':
            case 'i_voted':
                if (!$this->person) {
                    $this->order_by = ['id', 'DESC'];

                    return $this->getOrderBy();
                }

                if ($this->person->id) {
                    $join = "LEFT JOIN ratings ON (ratings.object_id = community_topics.id AND ratings.object_type = 'feedback' AND ratings.person_id = {$this->person->id})";
                } else {
                    $order_by = "ORDER BY community_topics.date_published $dir";

                    return $order_by;
                }

                $order_by = [
                    $join,
                    'ORDER BY ratings.date_created DESC, community_topics.id DESC',
                ];
                break;

            case 'popular':
                $order_by = 'ORDER BY (POW(total_rating+1,2)/DATEDIFF(NOW(),date_created)) DESC, date_created DESC';
                break;

            case 'most-voted':
            case 'num_ratings':
                $order_by = "ORDER BY community_topics.num_ratings $dir";
                break;
        }

        return $order_by;
    }

    /**
     * Get the SQL parts we need in the query.
     *
     * @return array
     */
    public function getSqlParts()
    {
        $db = App::getDbRead('search.filter.feedback');

        $wheres = [];
        $joins  = [];

        foreach ($this->terms as $info) {
            $join_id   = Util::requestUniqueId();
            $join_name = "j_$join_id";

            list($term, $op, $choice) = $info;
            $term_id                  = null;

            switch ($term) {
                case self::TERM_ID:
                    $choice = isset($choice['ids']) ? $choice['ids'] : $choice;
                    $choice = isset($choice['id']) ? $choice['id'] : $choice;

                    if ($op == self::OP_CONTAINS || is_array($choice)) {
                        if (!is_array($choice)) {
                            $choice = [$choice];
                        }
                        $wheres[] = $this->_choiceMatch('community_topics.id', 'is', $choice);
                    } else {
                        $wheres[] = $this->_rangeMatch('community_topics.id', $op, $choice, true);
                    }
                    break;

                case self::TERM_HIDDEN_STATUS:
                    if ($op == 'not') {
                        $wheres[] = '(community_topics.hidden_status IS NULL OR '.$this->_stringMatch('community_topics.hidden_status', $op, $choice).')';
                    } else {
                        $wheres[] = $this->_stringMatch('community_topics.hidden_status', $op, $choice);
                    }
                    break;

                case self::TERM_DELETED:
                    if ($op == self::OP_IS) {
                        $wheres[] = 'community_topics.hidden_status = \'deleted\'';
                    } else {
                        $wheres[] = 'community_topics.hidden_status != \'deleted\' OR community_topics.hidden_status IS NULL';
                    }
                    break;

                case self::TERM_STATUS:

                    $cats         = [];
                    $types        = [];
                    $hidden_types = [];

                    foreach ((array) $choice as $c) {
                        if (strpos($c, '.') !== false) {
                            list($hidden, $c) = explode('.', $c, 2);
                        } else {
                            $hidden = false;
                        }
                        if ($hidden === 'hidden') {
                            $hidden_types[] = $c;
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

                    $part_where = [];
                    if ($cats) {
                        $part_where[] = $this->_choiceMatch('community_topics.status_category_id', $op, $cats);
                    }
                    if ($types) {
                        $part_where[] = $this->_stringMatch('community_topics.status', $op, $types);
                    }
                    if ($hidden_types) {
                        $part_where[] = "(community_topics.status = 'hidden' AND ".$this->_stringMatch('community_topics.hidden_status', $op, $types).')';
                    }

                    if ($hidden_types) {
                        $this->include_hidden = true;
                    }

                    $part_where = '('.implode(' OR ', $part_where).')';

                    $wheres[] = $part_where;

                    break;

                case self::TERM_QUERY:

                    $string = $choice['query'];
                    $type   = !empty($choice['type']) ? $choice['type'] : 'phrase';

                    if (!$string) {
                        break;
                    }

                    $w   = [];
                    $w[] = '('.$this->_stringSearch('community_topics.title', $op, $string, $type).')';
                    $w[] = '('.$this->_stringSearch('community_topics.content', $op, $string, $type).')';

                    $wheres[] = implode(' OR ', $w);
                    break;

                case self::TERM_CATEGORY:
                case self::TERM_CATEGORY_SPECIFIC:
                    $base_ids = (array) ((is_array($choice) && isset($choice['category'])) ? $choice['category'] : $choice);
                    $ids      = [];

                    if ($term == self::TERM_CATEGORY_SPECIFIC) {
                        $ids = $base_ids;
                    } else {
                        foreach ($base_ids as $id) {
                            $ids = array_merge($ids, App::getEntityRepository('DeskPRO:FeedbackCategory')->getIdsInTree($id, true));
                        }
                    }

                    $ids = array_unique($ids);

                    $wheres[] = $this->_choiceMatch('community_topics.category_id', $op, $ids);

                    $this->summary[] = $this->_choiceSummary('Category', $op, $choice, function ($choice) {
                        $titles = App::getEntityRepository('DeskPRO:FeedbackCategory')->getNames((array) $choice);

                        return $titles;
                    });
                    break;

                case self::TERM_BRAND:
                    $ids = (array) $choice;
                    $ids = array_unique($ids);

                    $wheres[] = $this->_choiceMatch('community_topics.brand_id', $op, $ids);

                    $this->summary[] = $this->_choiceSummary('Brand', $op, $choice, function ($choice) {
                        $titles = App::getEntityRepository('DeskPRO:Brand')->getNames((array) $choice);

                        return $titles;
                    });
                    break;

                case self::TERM_STATUS_CATEGORY:
                    $ids = (array) $choice;
                    $ids = array_unique($ids);

                    $wheres[] = $this->_choiceMatch('community_topics.status_category_id', $op, $ids);

                    $this->summary[] = $this->_choiceSummary('Status Category', $op, $choice, function ($choice) {
                        $titles = App::getEntityRepository('DeskPRO:FeedbackStatusCategory')->getNames((array) $choice);

                        return $titles;
                    });
                    break;

                case self::TERM_NUM_RATINGS:
                    $wheres[] = $this->_rangeMatch('community_topics.num_ratings', $op, $choice);
                    break;

                case self::TERM_DATE_CREATED:
                    $wheres[] = $this->_dateMatch('idaes.date_created', $op, $choice);
                    break;

                case self::TERM_LABEL:
                    $this->_normalizeOpAndChoice($op, $choice);

                    $choices_in = [];
                    if (is_array($choice)) {
                        foreach ((array) $choice as $c) {
                            $choices_in[] = $db->quote($c);
                        }
                        $choices_in = implode(',', $choices_in);
                    }

                    switch ($op) {
                        case self::OP_IS:
                            $joins[] = [
                                'labels_community_topics',
                                "LEFT JOIN labels_community_topics AS $join_name ON ($join_name.topic_id = community_topics.id)",
                            ];
                            $wheres[] = "$join_name.label = ".$db->quote($choice);
                            break;
                        case self::OP_NOT:
                            $joins[] = [
                                'labels_community_topics',
                                "LEFT JOIN labels_community_topics AS $join_name ON ($join_name.topic_id = community_topics.id AND $join_name.label = '.$db->quote($choice).')",
                            ];
                            $wheres[] = "$join_name.person_id IS NULL";
                            break;
                        case self::OP_CONTAINS:
                            $joins[] = [
                                'labels_community_topics',
                                "LEFT JOIN labels_community_topics AS $join_name ON ($join_name.topic_id = community_topics.id)",
                            ];
                            $wheres[] = "$join_name.label IN ($choices_in)";
                            break;

                        case self::OP_NOTCONTAINS:
                            $joins[] = [
                                'labels_community_topics',
                                "LEFT JOIN labels_community_topics AS $join_name ON ($join_name.topic_id = community_topics.id AND $join_name.label IN ($choices_in)",
                            ];
                            $wheres[] = "$join_name.person_id IS NULL";
                            break;
                    }
                    break; // end labels
            }
        }

        $joins = array_unique($joins);

        return [
            'joins'  => $joins,
            'wheres' => $wheres,
        ];
    }
}
