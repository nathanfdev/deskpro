<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\DeskPRO\NewSearch\SearchEngine\Mysql;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContextInterface;
use Application\DeskPRO\NewSearch\SearchEngine\UserSearchInterface;
use Application\DeskPRO\Search\Adapter\MysqlAdapter;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\OptionsArray;
use Orb\Util\Strings;

class UserSearch implements UserSearchInterface
{
    const MAX_WORDS = 25;

    const LIMIT = 20;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var MysqlResultsTransformer
     */
    private $transformer;

    /**
     * @param Connection              $db
     * @param MysqlResultsTransformer $transformer
     */
    public function __construct(Connection $db, MysqlResultsTransformer $transformer)
    {
        $this->db          = $db;
        $this->transformer = $transformer;
    }

    /**
     * @param SearchContextInterface $context
     * @param string                 $query
     * @param array                  $options
     *
     * @return ResultSet
     */
    public function search(SearchContextInterface $context, $query, array $options = null)
    {
        $options      = new OptionsArray($options ?: array());
        $per_page     = Numbers::bound($options->get('per_page', self::LIMIT), 1, self::LIMIT);
        $page         = max($options->get('page', 1), 1);
        $ignore_perms = $options->get('ignore_perms');

        $limit_types = isset($options['limit_types']) ? $options['limit_types'] : null;
        if ($limit_types && !is_array($limit_types)) {
            $limit_types = explode(',', $limit_types);
            $limit_types = Arrays::func($limit_types, 'trim');
        }
        if ($limit_types) {
            $limit_types = Arrays::removeFalsey($limit_types);
        }

        $limit_types_array = $limit_types;

        $context_params = $this->buildParams($context, $limit_types);
        $types          = $context_params['types'];

        if (!$types) {
            // the following allows us to do a search for ONLY "ticket" types, because $types would be empty
            // if $limit_types only contained "ticket"
            if (in_array('ticket', $limit_types)) {
                $query2 = Strings::decodeHtmlEntities($query);
                $query2 = Strings::decodeUnicodeEntities($query2);
                $query2 = Strings::utf8_accents_to_ascii($query2);

                $query_words           = Arrays::removeEmptyString(explode(' ', trim($query.' '.$query2)));
                list($total, $results) = $this->findTicketResults($context, $limit_types_array, $page, $query_words, 0, array());

                $objects = $this->transformer->transform($results);

                return new ResultSet($objects, $total);
            }

            return new ResultSet(array());
        }

        $limit_types = "'".implode('\',\'', $types)."'";

        $query2 = Strings::decodeHtmlEntities($query);
        $query2 = Strings::decodeUnicodeEntities($query2);
        $query2 = Strings::utf8_accents_to_ascii($query2);

        $query_words = Arrays::removeEmptyString(explode(' ', trim($query.' '.$query2)));

        if (!$query_words) {
            return new ResultSet();
        }

        $query_words = array_unique($query_words);

        $params = array();
        $likes  = array();
        foreach ($query_words as $w) {
            if (strlen($w) <= 2) {
                continue;
            }

            $likes[]  = 'content_search.content LIKE ?';
            $params[] = '%'.str_replace(array('%', '_', '\\'), array('\\%', '\\_', '\\\\'), $w).'%';

            if (count($likes) >= self::MAX_WORDS) {
                break;
            }
        }

        if (count($likes) < self::MAX_WORDS) {
            $exist_labels = $this->db->fetchAllCol('
                SELECT DISTINCT label
                FROM label_defs
                WHERE label IN (?)
            ', array($query_words), array(Connection::PARAM_STR_ARRAY));
            foreach ($exist_labels as $l) {
                $l = MysqlAdapter::encodeLabel($l);
                if ($l) {
                    $likes[]  = 'content_search.content LIKE ?';
                    $params[] = '%'.$l.'%';
                }

                if (count($likes) >= self::MAX_WORDS) {
                    break;
                }
            }
        }

        if ($likes) {
            $where = "
                content_search.object_type IN ($limit_types)
                AND (".implode(' OR ', $likes).')
            ';

            if (!$ignore_perms) {
                $perm_join  = $context_params['join'];
                $perm_where = $context_params['where'];
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

            $total   = $this->db->fetchColumn($count_query, $params);
            $results = $this->db->fetchAll($select_query, $params);
        } else {
            $total   = 0;
            $results = array();
        }

        if ($total === null) {
            $total = count($results);
        }

        list($total, $results) = $this->findTicketResults($context, $limit_types_array, $page, $query_words, $total, $results);

        $objects = $this->transformer->transform($results);

        return new ResultSet($objects, $total);
    }

    /**
     * @param SearchContextInterface $context
     * @param string                 $content
     * @param array                  $options
     *
     * @return ResultSet
     */
    public function similarTo(SearchContextInterface $context, $content, array $options = null)
    {
        $content = $content ?: '';
        $content = Strings::utf8_accents_to_ascii($content);
        $content = strtolower($content);
//        todo?
//        $content = preg_replace('#[^a-zA-Z0-9]#', ' ', $content);
        $content = preg_replace('#\s+#', ' ', $content);
        $content = explode(' ', $content);
        $content = array_filter($content, function ($s) { return isset($s[2]); });
        $content = array_unique($content);
        $content = implode(' ', $content);

        if (!$content) {
            return new ResultSet();
        }

        return $this->search($context, $content, $options);
    }

    private function getTicketResults(SearchContextInterface $context, array $query_words)
    {
        $limit = 5;

        $search_places = array();
        $search_params = array();

        foreach ($query_words as $w) {
            $search_places[] = 'tickets.id = '.(int) $w;

            $search_places[] = 'tickets.ref = ?';
            $search_params[] = $w;

            if (strlen($w) <= 2) {
                continue;
            }

            $search_places[] = 'tickets_messages.message LIKE ?';
            $search_params[] = '%'.str_replace(array('%', '_', '\\'), array('\\%', '\\_', '\\\\'), $w).'%';

            if (count($search_params) >= self::MAX_WORDS) {
                break;
            }
        }

        if (!$search_params) {
            return array();
        }

        $search_places = implode(' OR ', $search_places);

        if ($context->getPerson()->organization && $context->getPerson()->organization_manager) {
            $params = array(
                $context->getPerson()->getId(),
                $context->getPerson()->getId(),
                $context->getPerson()->getId(),
                $context->getPerson()->organization->getId(),
            );

            $params = array_merge($params, $search_params);

            $ticket_ids = $this->db->fetchAllCol("
                SELECT DISTINCT(tickets.id)
                FROM tickets
                LEFT JOIN tickets_participants ON (tickets_participants.ticket_id = tickets.id)
                LEFT JOIN tickets_messages ON (tickets_messages.ticket_id = tickets.id AND tickets_messages.is_agent_note = 0)
                WHERE
                    (tickets.person_id = ? OR tickets_participants.person_id = ? OR tickets.agent_id = ? OR tickets.organization_id = ?)
                    AND (tickets.date_last_agent_reply IS NOT NULL OR tickets.date_last_user_reply IS NOT NULL)
                    AND ($search_places)
                ORDER BY tickets.date_status DESC, tickets.date_created DESC
                LIMIT $limit
            ", $params);
        } else {
            $params = array(
                $context->getPerson()->getId(),
                $context->getPerson()->getId(),
                $context->getPerson()->getId(),
            );

            $params = array_merge($params, $search_params);

            $ticket_ids = $this->db->fetchAllCol("
                SELECT DISTINCT(tickets.id)
                FROM tickets
                LEFT JOIN tickets_participants ON (tickets_participants.ticket_id = tickets.id)
                LEFT JOIN tickets_messages ON (tickets_messages.ticket_id = tickets.id AND tickets_messages.is_agent_note = 0)
                WHERE
                    (tickets.person_id = ? OR tickets_participants.person_id = ? OR tickets.agent_id = ?)
                    AND (tickets.date_last_agent_reply IS NOT NULL OR tickets.date_last_user_reply IS NOT NULL)
                    AND ($search_places)
                ORDER BY tickets.date_status DESC, tickets.date_created DESC
                LIMIT $limit
            ", $params);
        }

        if (!$ticket_ids) {
            return array();
        }

        $hits = array_map(function ($tid) {
            return array(
                'object_type' => 'ticket',
                'object_id'   => $tid,
            );
        }, $ticket_ids);

        return $hits;
    }

    /**
     * @param SearchContextInterface $context
     * @param array                  $limit_types
     *
     * @return ResultSet
     */
    private function buildParams(SearchContextInterface $context, array $limit_types = null)
    {
        $types  = array();
        $joins  = array();
        $wheres = array();

        $x = 0;
        if ($context->getArticleCategoryIds() && ($limit_types === null || in_array('article', $limit_types))) {
            $jn      = '_cs'.$x++;
            $cat_ids = implode(',', $context->getArticleCategoryIds());

            $types[]  = 'article';
            $joins[]  = "LEFT JOIN content_search_attribute AS $jn ON ($jn.object_type = 'article' AND $jn.object_type = content_search.object_type AND $jn.object_id = content_search.object_id AND $jn.attribute_id LIKE 'category_id%' AND $jn.content IN ($cat_ids))";
            $wheres[] = "($jn.object_type = 'article' AND $jn.object_id IS NOT NULL)";
        }
        if ($context->getNewsCategoryIds() && ($limit_types === null || in_array('news', $limit_types))) {
            $jn      = '_cs'.$x++;
            $cat_ids = implode(',', $context->getNewsCategoryIds());

            $types[]  = 'news';
            $joins[]  = "LEFT JOIN content_search_attribute AS $jn ON ($jn.object_type = 'news' AND $jn.object_type = content_search.object_type AND $jn.object_id = content_search.object_id AND $jn.attribute_id = 'category_id' AND $jn.content IN ($cat_ids))";
            $wheres[] = "($jn.object_type = 'news' AND $jn.object_id IS NOT NULL)";
        }
        if ($context->getFeedbackCategoryIds() && ($limit_types === null || in_array('feedback', $limit_types))) {
            $jn      = '_cs'.$x++;
            $cat_ids = implode(',', $context->getFeedbackCategoryIds());

            $types[]  = 'feedback';
            $joins[]  = "LEFT JOIN content_search_attribute AS $jn ON ($jn.object_type = 'feedback' AND $jn.object_type = content_search.object_type AND $jn.object_id = content_search.object_id AND $jn.attribute_id = 'category_id' AND $jn.content IN ($cat_ids))";
            $wheres[] = "($jn.object_type = 'feedback' AND $jn.object_id IS NOT NULL)";
        }
        if ($context->getDownloadCategoryIds() && ($limit_types === null || in_array('download', $limit_types))) {
            $jn      = '_cs'.$x++;
            $cat_ids = implode(',', $context->getDownloadCategoryIds());

            $types[]  = 'download';
            $joins[]  = "LEFT JOIN content_search_attribute AS $jn ON ($jn.object_type = 'download' AND $jn.object_type = content_search.object_type AND $jn.object_id = content_search.object_id AND $jn.attribute_id = 'category_id' AND $jn.content IN ($cat_ids))";
            $wheres[] = "($jn.object_type = 'download' AND $jn.object_id IS NOT NULL)";
        }

        return array(
            'types' => $types,
            'join'  => implode("\n", $joins),
            'where' => '('.implode(' OR ', $wheres).')',
        );
    }

    /**
     * @param SearchContextInterface $context
     * @param $limit_types_array
     * @param $page
     * @param $query_words
     * @param $total
     * @param $results
     *
     * @return array
     */
    protected function findTicketResults(SearchContextInterface $context, $limit_types_array, $page, $query_words, $total, $results)
    {
        if ($context->getPerson() && ($limit_types_array === null || in_array('ticket', $limit_types_array)) && $page == 1) {
            $ticket_results = $this->getTicketResults($context, $query_words);

            if ($ticket_results) {
                $total += count($ticket_results);
                $results = array_merge($ticket_results, $results);
            }
        }

        return array($total, $results);
    }
}
