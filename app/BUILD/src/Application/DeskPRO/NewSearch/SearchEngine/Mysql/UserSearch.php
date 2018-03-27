<?php

namespace Application\DeskPRO\NewSearch\SearchEngine\Mysql;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContextInterface;
use Application\DeskPRO\NewSearch\SearchEngine\UserSearchInterface;
use Application\DeskPRO\Search\Adapter\MysqlAdapter;
use Doctrine\ORM\EntityManager;
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
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var MysqlResultsTransformer
     */
    private $transformer;

    /**
     * @param Connection              $db
     * @param EntityManager           $em
     * @param MysqlResultsTransformer $transformer
     */
    public function __construct(Connection $db, EntityManager $em, MysqlResultsTransformer $transformer)
    {
        $this->db          = $db;
        $this->em          = $em;
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
        $options     = new OptionsArray($options ?: []);
        $perPage     = Numbers::bound($options->get('per_page', self::LIMIT), 1, self::LIMIT);
        $page        = max($options->get('page', 1), 1);
        $ignorePerms = $options->get('ignore_perms');

        $limitTypes = isset($options['limit_types']) ? $options['limit_types'] : null;
        if ($limitTypes && !is_array($limitTypes)) {
            $limitTypes = explode(',', $limitTypes);
            $limitTypes = Arrays::func($limitTypes, 'trim');
        }
        if ($limitTypes) {
            $limitTypes = Arrays::removeFalsey($limitTypes);
        }

        $limitTypesArray = $limitTypes;

        $contextParams = $this->buildParams($context, $limitTypes);
        $types         = $contextParams['types'];

        if (!$types) {
            // the following allows us to do a search for ONLY "ticket" types, because $types would be empty
            // if $limit_types only contained "ticket"
            if (in_array('ticket', $limitTypes)) {
                $query2 = Strings::decodeHtmlEntities($query);
                $query2 = Strings::decodeUnicodeEntities($query2);
                $query2 = Strings::utf8_accents_to_ascii($query2);

                $queryWords            = Arrays::removeEmptyString(explode(' ', trim($query.' '.$query2)));
                list($total, $results) = $this->findTicketResults($context, $limitTypesArray, $page, $queryWords, 0, []);

                $objects = $this->transformer->transform($results);

                return new ResultSet($objects, $total);
            }

            return new ResultSet([]);
        }

        $limitTypes = "'".implode('\',\'', $types)."'";

        $query2 = Strings::decodeHtmlEntities($query);
        $query2 = Strings::decodeUnicodeEntities($query2);
        $query2 = Strings::utf8_accents_to_ascii($query2);

        $queryWords = Arrays::removeEmptyString(explode(' ', trim($query.' '.$query2)));

        if (!$queryWords) {
            return new ResultSet();
        }

        $queryWords = array_unique($queryWords);

        $params = [];
        $likes  = [];
        foreach ($queryWords as $w) {
            if (strlen($w) <= 2) {
                continue;
            }

            $likes[]  = 'content_search.content LIKE ?';
            $params[] = '%'.str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $w).'%';

            if (count($likes) >= self::MAX_WORDS) {
                break;
            }
        }

        if (count($likes) < self::MAX_WORDS) {
            $existLabels = $this->db->fetchAllCol('
                SELECT DISTINCT label
                FROM label_defs
                WHERE label IN (?)
            ', [$queryWords], [Connection::PARAM_STR_ARRAY]);
            foreach ($existLabels as $l) {
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
                content_search.object_type IN ($limitTypes)
                AND (".implode(' OR ', $likes).')
            ';

            if (!$ignorePerms) {
                $permJoin  = $contextParams['join'];
                $permWhere = $contextParams['where'];
                if (!$permWhere) {
                    $permWhere = '1';
                }
            } else {
                $permJoin  = '';
                $permWhere = '1';
            }

            $countQuery = "
                SELECT COUNT(*)
                FROM content_search
                $permJoin
                WHERE $permWhere AND $where
                LIMIT $perPage
            ";

            $start       = ($page - 1) * $perPage;
            $selectQuery = "
                SELECT content_search.object_type, content_search.object_id
                FROM content_search
                $permJoin
                WHERE $permWhere AND $where
                ORDER BY content_search.object_id DESC
                LIMIT $start, $perPage
            ";

            $total   = $this->db->fetchColumn($countQuery, $params);
            $results = $this->db->fetchAll($selectQuery, $params);
        } else {
            $total   = 0;
            $results = [];
        }

        if ($total === null) {
            $total = count($results);
        }

        list($total, $results) = $this->findTicketResults($context, $limitTypesArray, $page, $queryWords, $total, $results);

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
        $content = array_filter($content, function ($s) {
            return isset($s[2]);
        });
        $content = array_unique($content);
        $content = implode(' ', $content);

        if (!$content) {
            return new ResultSet();
        }

        return $this->search($context, $content, $options);
    }

    private function getTicketResults(SearchContextInterface $context, array $queryWords)
    {
        $limit = 5;

        $qb = $this->em->createQueryBuilder();

        $searchParams = 0;
        $paramsIndex  = 0;
        $params       = [];

        if (!count($queryWords)) {
            return [];
        }

        $searchPlaces = $qb->expr()->orX();
        foreach ($queryWords as $w) {
            ++$searchParams;
            $searchPlaces->add($qb->expr()->eq('tickets.id', '?'.$paramsIndex));
            $params[$paramsIndex++] = (int) $w;

            $searchPlaces->add($qb->expr()->eq('tickets.ref', '?'.$paramsIndex));
            $params[$paramsIndex++] = $w;

            if (strlen($w) <= 2) {
                continue;
            }

            $searchPlaces->add($qb->expr()->like('tickets_messages.message', '?'.$paramsIndex));
            $params[$paramsIndex++] = '%'.str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $w).'%';

            if ($searchParams >= self::MAX_WORDS) {
                break;
            }
        }

        $person = $qb->expr()->orX($qb->expr()->eq('tickets.person', '?'.$paramsIndex));

        if (!$context->getPerson()->isAgent()) {
            $person->add($qb->expr()->eq('tickets_participants.person', '?'.$paramsIndex));
        }
        $params[$paramsIndex++] = (int) $context->getPerson()->getId();

        $brand                  = $qb->expr()->eq('tickets.brand', '?'.$paramsIndex);
        $params[$paramsIndex++] = (int) $context->getBrand()->getId();

        if ($context->getPerson()->organization && $context->getPerson()->organization_manager) {
            $person->add($qb->expr()->eq('tickets.organization', '?'.$paramsIndex));
            $params[$paramsIndex] = (int) $context->getPerson()->organization->getId();
        }

        $qb->select('DISTINCT(tickets.id)')
            ->from(Ticket::class, 'tickets')
            ->leftJoin('tickets.participants', 'tickets_participants')
            ->leftJoin('tickets.messages', 'tickets_messages', 'WITH', 'tickets_messages.is_agent_note = 0')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->isNotNull('tickets.date_last_agent_reply'),
                    $qb->expr()->isNotNull('tickets.date_last_user_reply')
                )
            )
            ->andWhere($searchPlaces)
            ->andWhere($person)
            ->andWhere($brand)
            ->orderBy('tickets.date_status', 'DESC')
            ->addOrderBy('tickets.date_created', 'DESC')
            ->setMaxResults($limit)
            ->setParameters($params);

        $ticketIds = $qb->getQuery()->getArrayResult();

        if (!$ticketIds) {
            return [];
        }

        $hits = array_map(
            function ($tid) {
                return [
                    'object_type' => 'ticket',
                    'object_id'   => $tid[1],
                ];
            },
            $ticketIds
        );

        return $hits;
    }

    /**
     * @param SearchContextInterface $context
     * @param array                  $limit_types
     *
     * @return ResultSet
     */
    public function buildParams(SearchContextInterface $context, array $limit_types = null)
    {
        $types  = [];
        $joins  = [];
        $wheres = [];

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
        if ($context->getGuideIds() && ($limit_types === null || in_array('topic', $limit_types))) {
            $jn      = '_cs'.$x++;
            $cat_ids = implode(',', $context->getGuideIds());

            $types[]  = 'topic';
            $joins[]  = "LEFT JOIN content_search_attribute AS $jn ON ($jn.object_type = 'topic' AND $jn.object_type = content_search.object_type AND $jn.object_id = content_search.object_id AND $jn.attribute_id = 'guide_id' AND $jn.content IN ($cat_ids))";
            $wheres[] = "($jn.object_type = 'topic' AND $jn.object_id IS NOT NULL)";
        }

        return [
            'types' => $types,
            'join'  => implode("\n", $joins),
            'where' => '('.implode(' OR ', $wheres).')',
        ];
    }

    /**
     * @param SearchContextInterface $context
     * @param                        $limit_types_array
     * @param                        $page
     * @param                        $query_words
     * @param                        $total
     * @param                        $results
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

        return [$total, $results];
    }
}
