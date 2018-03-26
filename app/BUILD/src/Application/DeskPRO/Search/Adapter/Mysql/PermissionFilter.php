<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search\Adapter\Mysql;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContextInterface;

/**
 * Strips out search results the user cant actually see.
 */
class PermissionFilter
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $context;

    /**
     * The types to generate the where for.
     *
     * @var array
     */
    protected $types = ['article', 'news', 'download', 'feedback', 'topic'];

    /**
     * The 'where' clause.
     *
     * @var string
     */
    protected $perm_where = '';

    /**
     * Any required joins.
     *
     * @var null
     */
    protected $perm_join = '';

    /**
     * @var bool
     */
    protected $has_gen = false;

    /**
     * @param SearchContextInterface $context
     */
    public function setContext(SearchContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * @param array $types
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    protected function _gen()
    {
        if (!$this->person_context) {
            throw new \RuntimeException('PermissionFilter requires you to set a person context');
        }

        if ($this->has_gen) {
            return;
        }
        $this->has_gen = true;

        $join  = [];
        $where = [];
        $x     = 0;

        if (in_array('article', $this->types)) {
            ++$x;
            $jn      = '_cs'.$x;
            $dis_ids = $this->person_context->PermissionsManager->ArticleCategories->getDisallowedCategories();
            if ($dis_ids) {
                $dis_ids = implode(',', $dis_ids);
                $join[]  = "LEFT JOIN content_search_attribute AS $jn ON ($jn.object_type = 'article' AND $jn.object_type = content_search.object_type AND $jn.object_id = content_search.object_id AND $jn.attribute_id LIKE 'category_id%' AND $jn.content IN ($dis_ids))";
                $where[] = "$jn.object_id IS NULL";
            }
        }

        if (in_array('news', $this->types)) {
            ++$x;
            $jn      = '_cs'.$x;
            $dis_ids = $this->person_context->PermissionsManager->NewsCategories->getDisallowedCategories();
            if ($dis_ids) {
                $dis_ids = implode(',', $dis_ids);
                $join[]  = "LEFT JOIN content_search_attribute AS $jn ON ($jn.object_type = 'news' AND $jn.object_type = content_search.object_type AND $jn.object_id = content_search.object_id AND $jn.attribute_id = 'category_id' AND $jn.content IN ($dis_ids))";
                $where[] = "$jn.object_id IS NULL";
            }
        }

        if (in_array('feedback', $this->types)) {
            ++$x;
            $jn      = '_cs'.$x;
            $dis_ids = $this->person_context->PermissionsManager->FeedbackCategories->getDisallowedCategories();
            if ($dis_ids) {
                $dis_ids = implode(',', $dis_ids);
                $join[]  = "LEFT JOIN content_search_attribute AS $jn ON ($jn.object_type = 'feedback' AND $jn.object_type = content_search.object_type AND $jn.object_id = content_search.object_id AND $jn.attribute_id = 'category_id' AND $jn.content IN ($dis_ids))";
                $where[] = "$jn.object_id IS NULL";
            }
        }

        if (in_array('download', $this->types)) {
            ++$x;
            $jn      = '_cs'.$x;
            $dis_ids = $this->person_context->PermissionsManager->DownloadCategories->getDisallowedCategories();
            if ($dis_ids) {
                $dis_ids = implode(',', $dis_ids);
                $join[]  = "LEFT JOIN content_search_attribute AS $jn ON ($jn.object_type = 'download' AND $jn.object_type = content_search.object_type AND $jn.object_id = content_search.object_id AND $jn.attribute_id = 'category_id' AND $jn.content IN ($dis_ids))";
                $where[] = "$jn.object_id IS NULL";
            }
        }

        if (in_array('topic', $this->types)) {
            ++$x;
            $jn      = '_cs'.$x;
            $dis_ids = $this->person_context->PermissionsManager->DownloadCategories->getDisallowedCategories();
            if ($dis_ids) {
                $dis_ids = implode(',', $dis_ids);
                $join[]  = "LEFT JOIN content_search_attribute AS $jn ON ($jn.object_type = 'topic' AND $jn.object_type = content_search.object_type AND $jn.object_id = content_search.object_id AND $jn.attribute_id = 'guide_id' AND $jn.content IN ($dis_ids))";
                $where[] = "$jn.object_id IS NULL";
            }
        }

        if (!$join && !$where) {
            return;
        }

        $this->perm_join  = implode("\n", $join);
        $this->perm_where = '('.implode(' AND ', $where).')';
    }

    /**
     * Get the required joins for the check.
     *
     * @return string|null
     */
    public function getJoin()
    {
        $this->_gen();

        return $this->perm_join;
    }

    /**
     * Get the required wheres for the check.
     *
     * @return string|null
     */
    public function getWhere()
    {
        $this->_gen();

        return $this->perm_where;
    }
}
