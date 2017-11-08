<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\DeskPRO\Publish;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\DownloadComment;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\Entity\GlossaryWord;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\NewsComment;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Entity\TopicComment;
use Application\DeskPRO\People\PersonContextInterface;
use Orb\Util\Arrays;

/**
 * Helps fetch info related to structure of Publish.
 */
class AgentHelper implements PersonContextInterface
{
    const ARTICLES  = 'articles';
    const DOWNLOADS = 'downloads';
    const NEWS      = 'news';
    const FEEDBACK  = 'feedback';
    const TOPICS    = 'topics';

    /** @var array */
    protected $enabled_types = [
        self::ARTICLES,
        self::DOWNLOADS,
        self::NEWS,
        self::TOPICS,
    ];

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person_context;

    /**
     * @param array $types
     */
    public function setEnabledTypes(array $types)
    {
        $this->enabled_types = $types;
    }

    /**
     * @return string|null
     */
    public function getSingleSpecificType()
    {
        $t = $this->enabled_types;
        if (count($t) == 1) {
            return array_pop($t);
        }

        return;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;

        return $this;
    }

    /**
     * Get the category structure.
     *
     * @param string $type
     * @param int    $brandId
     *
     * @return array
     */
    public function getCategoryStructure($type, $brandId = 0)
    {
        $entity_name = self::getCatEntityNameFor($type);

        return App::getEntityRepository($entity_name)->getRootNodes($brandId);
    }

    /**
     * @param string $type
     *
     * @return int
     */
    public function getCategoryCounts($type)
    {
        $entity_name = self::getCatEntityNameFor($type);
        $repos       = App::getEntityRepository($entity_name);
        $counts      = $repos->getAllCounts($this->person_context, null);

        return $counts;
    }

    /**
     * Get an array of categories and their perms usergroups.
     *
     * @param string $type
     *
     * @return array
     */
    public function getCategoryUsergroups($type)
    {
        $entity_name = self::getCatEntityNameFor($type);
        $repos       = App::getEntityRepository($entity_name);
        $table       = $repos->getPermissionTableName();

        if (!$table) {
            return [];
        }

        $cats_to_ugs = App::getDb()->fetchAllGrouped(
            "
            SELECT category_id, usergroup_id
            FROM $table
        ",
            [],
            'category_id',
            null,
            'usergroup_id'
        );

        return $cats_to_ugs;
    }

    //###########################################################################
    // Glossary
    //###########################################################################

    /**
     * Get an array of glossary words, sorted into an alphabetically-indexed array.
     *
     * @param int $brandId
     *
     * @return array
     */
    public function getGlossaryWordsIndex($brandId = 0)
    {
        $glossary_words = App::getEntityRepository(GlossaryWord::class)->getWords($brandId);
        $glossary_words = Arrays::sortIntoAlphabeticalIndex($glossary_words, null, true, true);

        return $glossary_words;
    }

    //###########################################################################
    // Validating Comments
    //###########################################################################

    /**
     * @param int    $limit
     * @param string $order_dir
     *
     * @return array
     */
    public function getValidatingComments($limit = 25, $order_dir = 'ASC')
    {
        $sql_parts = [];

        if (!is_array($limit)) {
            $limit = [
                'max'    => $limit,
                'offset' => 0,
            ];
        }
        $offset = (int) $limit['offset'];
        $limit  = (int) $limit['max'];

        $types = $this->getCommentTypeInfo();

        //------------------------------
        // Fetch from each comment table with a union
        //------------------------------

        foreach ($this->enabled_types as $t) {
            $t_info      = $types[$t];
            $sql_parts[] = "(
                SELECT id as comment_id, '{$t_info['content_type']}' as content_type, date_created
                FROM {$t_info['table']}
                WHERE is_reviewed = 0
            )";
        }

        $sql = implode(' UNION ', $sql_parts);
        $sql .= "ORDER BY date_created $order_dir LIMIT {$offset}, {$limit}";

        $db      = App::getDb();
        $results = $db->fetchAll($sql);

        if (!$results) {
            return [];
        }

        //------------------------------
        // Fetch each comment in the result
        //------------------------------

        $result_ids_typed = [];

        foreach ($results as $r) {
            if (!isset($result_ids_typed[$r['content_type']])) {
                $result_ids_typed[$r['content_type']] = [];
            }

            $result_ids_typed[$r['content_type']][] = $r['comment_id'];
        }

        $results_typed = [];

        foreach ($result_ids_typed as $t => $ids) {
            $t_info            = $types[$t];
            $results_typed[$t] = App::getEntityRepository($t_info['entity'])->getByIds($ids);
        }

        //------------------------------
        // Put back into original sort order
        // as a combined array
        //------------------------------

        $results_ordered = [];

        foreach ($results as $r) {
            if (isset($results_typed[$r['content_type']][$r['comment_id']])) {
                $results_ordered[] = [
                    'info' => $r,
                    'obj'  => $results_typed[$r['content_type']][$r['comment_id']],
                ];
            }
        }

        return $results_ordered;
    }

    /**
     * @return int
     */
    public function getValidatingCommentsCount()
    {
        $sql_parts = [];

        $types = $this->getCommentTypeInfo();
        $db    = App::getDb();

        foreach ($this->enabled_types as $t) {
            $t_info      = $types[$t];
            $alias       = $db->quoteIdentifier('count_'.$t);
            $sql_parts[] = "(
                SELECT COUNT(*)
                FROM {$t_info['table']}
                WHERE is_reviewed = 0
            ) AS $alias";
        }

        $sql     = 'SELECT '.implode(', ', $sql_parts);
        $results = $db->fetchAssoc($sql);

        return array_sum($results);
    }

    //###########################################################################
    // All Comments
    //###########################################################################

    /**
     * @param int|array $limit
     * @param int       $brandId
     * @param string    $order_dir
     *
     * @return array
     */
    public function getComments($limit, $brandId = 0, $order_dir = 'DESC')
    {
        $sql_parts = [];

        if (!is_array($limit)) {
            $limit = [
                'max'    => $limit,
                'offset' => 0,
            ];
        }
        $offset = (int) $limit['offset'];
        $limit  = (int) $limit['max'];

        $types = $this->getCommentTypeInfo();

        //------------------------------
        // Fetch from each comment table with a union
        //------------------------------

        foreach ($this->enabled_types as $t) {
            $t_info = $types[$t];
            if ($brandId && $t == 'articles') {
                $sql_parts[] = "(
                    SELECT {$t_info['table']}.id as comment_id, '{$t_info['content_type']}' as content_type, 
                    {$t_info['table']}.date_created
                    FROM {$t_info['table']} 
                    INNER JOIN {$t_info['content_type']}
                        ON {$t_info['content_type']}.id = {$t_info['table']}.{$t_info['id_field']}
                    INNER JOIN {$t_info['category_link_table']}
                        ON {$t_info['category_link_table']}.{$t_info['id_field']} = {$t_info['table']}.id
                    INNER JOIN {$t_info['category_table']}
                        ON {$t_info['category_table']}.id = {$t_info['category_link_table']}.{$t_info['category_field']}
                    WHERE {$t_info['table']}.status != 'deleted'
                    AND {$t_info['category_table']}.brand_id = ".(int) $brandId.'
                )';
            } elseif ($brandId && $t != 'feedback') {
                $sql_parts[] = "(
                    SELECT {$t_info['table']}.id as comment_id, '{$t_info['content_type']}' as content_type, 
                    {$t_info['table']}.date_created
                    FROM {$t_info['table']} 
                    INNER JOIN {$t_info['content_type']}
                        ON {$t_info['content_type']}.id = {$t_info['table']}.{$t_info['id_field']}
                    INNER JOIN {$t_info['category_table']}
                        ON {$t_info['category_table']}.id = {$t_info['content_type']}.{$t_info['category_field']}
                    WHERE {$t_info['table']}.status != 'deleted'
                    AND {$t_info['category_table']}.brand_id = ".(int) $brandId.'
                )';
            } else {
                $sql_parts[] = "(
                    SELECT id as comment_id, '{$t_info['content_type']}' as content_type, date_created
                    FROM {$t_info['table']}
                    WHERE status != 'deleted'
                )";
            }
        }

        $sql = implode(' UNION ', $sql_parts);
        $sql .= "ORDER BY date_created $order_dir LIMIT {$offset}, {$limit}";

        $db      = App::getDb();
        $results = $db->fetchAll($sql);

        if (!$results) {
            return [];
        }

        //------------------------------
        // Fetch each comment in the result
        //------------------------------

        $result_ids_typed = [];

        foreach ($results as $r) {
            if (!isset($result_ids_typed[$r['content_type']])) {
                $result_ids_typed[$r['content_type']] = [];
            }

            $result_ids_typed[$r['content_type']][] = $r['comment_id'];
        }

        $results_typed = [];

        foreach ($result_ids_typed as $t => $ids) {
            $t_info            = $types[$t];
            $results_typed[$t] = App::getEntityRepository($t_info['entity'])->getByIds($ids);
        }

        //------------------------------
        // Put back into original sort order
        // as a combined array
        //------------------------------

        $results_ordered = [];

        foreach ($results as $r) {
            if (isset($results_typed[$r['content_type']][$r['comment_id']])) {
                $results_ordered[] = [
                    'info' => $r,
                    'obj'  => $results_typed[$r['content_type']][$r['comment_id']],
                ];
            }
        }

        return $results_ordered;
    }

    /**
     * @param int $brandId
     *
     * @return array
     */
    public function getCommentsCountInfo($brandId = 0)
    {
        $sql_parts = [];

        $types = $this->getCommentTypeInfo();
        $db    = App::getDb();

        foreach ($this->enabled_types as $t) {
            $t_info = $types[$t];
            $alias  = $db->quoteIdentifier($t);
            if ($brandId && $t == 'articles') {
                $sql_parts[] = "(
                    SELECT COUNT(DISTINCT {$t_info['table']}.{$t_info['id_field']})
                    FROM {$t_info['table']} 
                    INNER JOIN {$t_info['content_type']}
                        ON {$t_info['content_type']}.id = {$t_info['table']}.{$t_info['id_field']}
                    INNER JOIN {$t_info['category_link_table']}
                        ON {$t_info['category_link_table']}.{$t_info['id_field']} = {$t_info['table']}.id
                    INNER JOIN {$t_info['category_table']}
                        ON {$t_info['category_table']}.id = {$t_info['category_link_table']}.{$t_info['category_field']}
                    WHERE {$t_info['table']}.status != 'deleted'
                    AND {$t_info['category_table']}.brand_id = ".(int) $brandId."
                ) AS $alias";
            } elseif ($brandId && $t != 'feedback') {
                $sql_parts[] = "(
                    SELECT COUNT(DISTINCT {$t_info['table']}.{$t_info['id_field']})
                    FROM {$t_info['table']} 
                    INNER JOIN {$t_info['content_type']}
                        ON {$t_info['content_type']}.id = {$t_info['table']}.{$t_info['id_field']}
                    INNER JOIN {$t_info['category_table']}
                        ON {$t_info['category_table']}.id = {$t_info['content_type']}.{$t_info['category_field']}
                    WHERE {$t_info['table']}.status != 'deleted'
                    AND {$t_info['category_table']}.brand_id = ".(int) $brandId."
                ) AS $alias";
            } else {
                $sql_parts[] = "(
                    SELECT COUNT(*)
                    FROM {$t_info['table']}
                    WHERE status != 'deleted'
                ) AS $alias";
            }
        }

        $sql     = 'SELECT '.implode(', ', $sql_parts);
        $results = $db->fetchAssoc($sql);

        $count_all = array_sum($results);

        $counts        = $results;
        $counts['all'] = $count_all;

        return $counts;
    }

    /**
     * @param string $for_type
     * @param string $prop
     *
     * @return array
     */
    public function getCommentTypeInfo($for_type = null, $prop = null)
    {
        static $types = [
            'articles' => [
                'content_type'        => 'articles',
                'table'               => 'article_comments',
                'entity'              => ArticleComment::class,
                'category_table'      => 'article_categories',
                'category_link_table' => 'article_to_categories',
                'category_field'      => 'category_id',
                'id_field'            => 'article_id',
            ],
            'downloads' => [
                'content_type'   => 'downloads',
                'table'          => 'download_comments',
                'entity'         => DownloadComment::class,
                'category_table' => 'download_categories',
                'category_field' => 'category_id',
                'id_field'       => 'download_id',
            ],
            'news' => [
                'content_type'   => 'news',
                'table'          => 'news_comments',
                'entity'         => NewsComment::class,
                'category_table' => 'news_categories',
                'category_field' => 'category_id',
                'id_field'       => 'news_id',
            ],
            'feedback' => [
                'content_type' => 'feedback',
                'table'        => 'feedback_comments',
                'entity'       => FeedbackComment::class,
                'id_field'     => 'feedback_id',
            ],
            'topics' => [
                'content_type'   => 'topics',
                'table'          => 'topic_comments',
                'entity'         => TopicComment::class,
                'category_table' => 'guides',
                'category_field' => 'guide_id',
                'id_field'       => 'topic_id',
            ],
        ];

        if ($for_type !== null) {
            if (!isset($types[$for_type])) {
                throw new \InvalidArgumentException("$for_type is an invalid comment type");
            }

            if ($prop !== null) {
                if (!isset($types[$for_type][$prop])) {
                    throw new \InvalidArgumentException("$for_type.$prop is an invalid comment type property");
                }

                return $types[$for_type][$prop];
            }

            return $types[$for_type];
        }

        return $types;
    }

    //###########################################################################
    // Drafts
    //###########################################################################

    /**
     * Get an array of all drafts.
     *
     * @param null   $limit
     * @param string $order_dir
     * @param bool   $all
     * @param string $status
     *
     * @return array
     */
    public function getDraftContent($limit = null, $order_dir = 'ASC', $all = false, $status = 'draft')
    {
        $results = $this->getDraftInfo($limit, $order_dir, $all, $status);

        return $this->getContentFromInfo($results);
    }

    /**
     * @param int    $limit
     * @param string $order_dir
     * @param bool   $all
     *
     * @return array
     */
    public function getDraftInfo($limit = null, $order_dir = 'ASC', $all = false, $status = 'draft')
    {
        $sql_parts = [];

        if ($limit !== null && !is_array($limit)) {
            $limit = [
                'max'    => $limit,
                'offset' => 0,
            ];
        }
        if (null !== $limit) {
            $offset = (int) $limit['offset'];
            $limit  = (int) $limit['max'];
        }

        $types = [
            'articles' => [
                'content_type' => 'articles',
                'entity'       => Article::class,
                'id_field'     => 'article_id',
                'rev_table'    => 'article_revisions',
            ],
            'downloads' => [
                'content_type' => 'downloads',
                'entity'       => Download::class,
                'id_field'     => 'download_id',
                'rev_table'    => 'download_revisions',
            ],
            'news' => [
                'content_type' => 'news',
                'entity'       => News::class,
                'id_field'     => 'news_id',
                'rev_table'    => 'news_revisions',
            ],
            'feedback' => [
                'content_type' => 'feedback',
                'entity'       => Feedback::class,
                'id_field'     => 'feedback_id',
                'rev_table'    => 'feedback_revisions',
            ],
            'topics' => [
                'content_type' => 'topic',
                'entity'       => Topic::class,
                'id_field'     => 'topic_id',
                'rev_table'    => 'topic_revisions',
            ],
        ];

        //------------------------------
        // Fetch from each comment table with a union
        //------------------------------

        $db = App::getDb();
        foreach ($this->enabled_types as $t) {
            $t_info     = $types[$t];
            $person_sql = '';
            $table      = $db->quoteIdentifier($t);

            if (!$all) {
                $person_sql = " AND c.person_id = {$this->person_context['id']}";
            }

            $sql_parts[] = "(
                SELECT DISTINCT(c.id) as content_id, '{$t_info['content_type']}' as content_type, r.id AS revision_id, c.date_created
                FROM $table AS c
                LEFT JOIN {$t_info['rev_table']} r ON (c.id = r.{$t_info['id_field']})
                WHERE 
                    (c.status = 'hidden' AND c.hidden_status = '{$status}' $person_sql) 
                 OR (r.status = '{$status}' $person_sql)
                GROUP BY c.id
            )";
        }

        $sql = implode(' UNION ', $sql_parts);
        if ($limit) {
            $sql .= " ORDER BY date_created $order_dir LIMIT {$offset}, {$limit}";
        } else {
            $sql .= " ORDER BY date_created $order_dir";
        }

        $results = $db->fetchAll($sql);

        return $results;
    }

    /**
     * Count how many drafts there are for this user.
     *
     * @param bool   $mine
     * @param string $status
     *
     * @return int
     */
    public function getCountsByHiddenStatus($mine = true, $status = 'draft')
    {
        $types = [
            'articles' => [
                'content_type' => 'articles',
                'entity'       => Article::class,
                'id_field'     => 'article_id',
                'rev_table'    => 'article_revisions',
            ],
            'downloads' => [
                'content_type' => 'downloads',
                'entity'       => Download::class,
                'id_field'     => 'download_id',
                'rev_table'    => 'download_revisions',
            ],
            'news' => [
                'content_type' => 'news',
                'entity'       => News::class,
                'id_field'     => 'news_id',
                'rev_table'    => 'news_revisions',
            ],
            'feedback' => [
                'content_type' => 'feedback',
                'entity'       => Feedback::class,
                'id_field'     => 'feedback_id',
                'rev_table'    => 'feedback_revisions',
            ],
            'topics' => [
                'content_type' => 'topics',
                'entity'       => Topic::class,
                'id_field'     => 'topic_id',
                'rev_table'    => 'topic_revisions',
            ],
        ];

        $db        = App::getDb();
        $sql_parts = [];
        foreach ($this->enabled_types as $t) {
            $t_info     = $types[$t];
            $person_sql = '';
            $table      = $db->quoteIdentifier($t);
            $alias      = $db->quoteIdentifier('count_'.$t);

            if ($mine) {
                $person_sql = " AND c.person_id = {$this->person_context['id']}";
            }

            $sql_parts[] = "(
                SELECT COUNT(DISTINCT c.id)
                FROM $table c
                LEFT JOIN {$t_info['rev_table']} r ON (r.{$t_info['id_field']} = c.id)
                WHERE 
                    (c.status = 'hidden' AND c.hidden_status = '{$status}' $person_sql) 
                    OR (r.status = '{$status}' $person_sql)
            ) AS $alias";
        }

        $sql = 'SELECT '.implode(', ', $sql_parts);

        $results = $db->fetchAssoc($sql);

        return array_sum($results);
    }

    //###########################################################################

    /**
     * @param array $results
     *
     * @return array
     */
    public function getContentFromInfo($results)
    {
        $types = [
            'articles' => [
                'content_type' => 'articles',
                'entity'       => Article::class,
                'id_field'     => 'article_id',
                'rev_table'    => 'article_revisions',
            ],
            'downloads' => [
                'content_type' => 'downloads',
                'entity'       => Download::class,
                'id_field'     => 'download_id',
                'rev_table'    => 'download_revisions',
            ],
            'news' => [
                'content_type' => 'news',
                'entity'       => News::class,
                'id_field'     => 'news_id',
                'rev_table'    => 'news_revisions',
            ],
            'feedback' => [
                'content_type' => 'feedback',
                'entity'       => Feedback::class,
                'id_field'     => 'feedback_id',
                'rev_table'    => 'feedback_revisions',
            ],
            'topic' => [
                'content_type' => 'topics',
                'entity'       => Topic::class,
                'id_field'     => 'topic_id',
                'rev_table'    => 'topic_revisions',
            ],
        ];

        //------------------------------
        // Fetch each comment in the result
        //------------------------------

        $resultIdsTyped = [];

        foreach ($results as $r) {
            if (!isset($resultIdsTyped[$r['content_type']])) {
                $resultIdsTyped[$r['content_type']] = [];
            }

            $resultIdsTyped[$r['content_type']][] = $r['content_id'];
        }

        $resultsTyped = [];

        foreach ($resultIdsTyped as $t => $ids) {
            $tInfo            = $types[$t];
            $resultsTyped[$t] = App::getEntityRepository($tInfo['entity'])->getByIds($ids);
        }

        //------------------------------
        // Put back into original sort order
        // as a combined array
        //------------------------------

        $resultsOrdered = [];

        foreach ($results as $r) {
            if (isset($resultsTyped[$r['content_type']][$r['content_id']])) {
                $resultsOrdered[] = [
                    'info' => $r,
                    'obj'  => $resultsTyped[$r['content_type']][$r['content_id']],
                ];
            }
        }

        return $resultsOrdered;
    }

    /**
     * Get the content entity for a publish type.
     *
     *
     * @param $type
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function getEntityNameFor($type)
    {
        switch ($type) {
            case self::ARTICLES:
                return Article::class;
                break;
            case self::DOWNLOADS:
                return Download::class;
                break;
            case self::NEWS:
                return News::class;
                break;
            case self::FEEDBACK:
                return Feedback::class;
                break;
            case self::TOPICS:
                return Topic::class;
                break;
        }

        throw new \InvalidArgumentException("Unknown type `$type`");
    }

    /**
     * Get the category entity for a publish type.
     *
     *
     * @param string $type
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function getCatEntityNameFor($type)
    {
        switch ($type) {
            case self::ARTICLES:
                return ArticleCategory::class;
                break;
            case self::DOWNLOADS:
                return DownloadCategory::class;
                break;
            case self::NEWS:
                return NewsCategory::class;
                break;
            case self::FEEDBACK:
                return FeedbackCategory::class;
                break;
            case self::TOPICS:
                return Guide::class;
                break;
        }

        throw new \InvalidArgumentException("Unknown type `$type`");
    }
}
