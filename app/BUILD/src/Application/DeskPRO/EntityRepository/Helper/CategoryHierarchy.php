<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository\Helper;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class CategoryHierarchy
{
    /**
     * @var \Application\DeskPRO\EntityRepository\AbstractCategoryRepository
     */
    protected $repos;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     */
    protected $class;

    /**
     * @var string
     */
    protected $entity_name;

    /**
     * @var string
     */
    protected $table_name;

    /**
     * @var string
     */
    protected $cache_tag = null;

    /**
     * @var null|array
     */
    protected $_cats = null;
    /**
     * @var null|array
     */
    protected $_cat_hierarchy = null;
    /**
     * @var null|array
     */
    protected $_cat_hierarchy_flat = null;
    /**
     * @var null|array
     */
    protected $_cat_names = null;
    /**
     * @var array
     */
    protected $_cat_ids = [];
    /**
     * @var array
     */
    protected $_cat_parent_map = [];

    public function __construct(EntityManager $em, AbstractEntityRepository $repos, $entity_name, ClassMetadata $class, $cache_tag = null)
    {
        $this->repos       = $repos;
        $this->em          = $em;
        $this->class       = $class;
        $this->entity_name = $entity_name;
        $this->table_name  = $class->getTableName();

        if (!$cache_tag) {
            $cache_tag = $this->table_name;
        }

        $this->cache_tag = $cache_tag;
    }

    /**
     * Get all root node ids.
     *
     * @param int $brandId
     *
     * @return array
     */
    public function getRootNodeIds($brandId = 0)
    {
        $this->getInHierarchy();

        $root_ids = [];

        foreach ($this->_cat_hierarchy as $c) {
            if (!$brandId || $brandId == $c['brand_id']) {
                $root_ids[] = $c['id'];
            }
        }

        return $root_ids;
    }

    /**
     * Get all root nodes.
     *
     * @param int $brandId
     *
     * @return array
     */
    public function getRootNodes($brandId = 0)
    {
        $root_ids = $this->getRootNodeIds($brandId);

        if (!$root_ids) {
            return [];
        }

        return $this->repos->getByIds($root_ids, true);
    }

    /**
     * Get all category IDs that exists.
     *
     * @return array
     */
    public function getIds()
    {
        $this->getInHierarchy();

        return $this->_cat_ids;
    }

    /**
     * Get a plain hierarchy array.
     *
     * @param bool $reset
     *
     * @return array|null
     */
    public function getInHierarchy($reset = false)
    {
        if (!$reset && $this->_cat_hierarchy !== null) {
            return $this->_cat_hierarchy;
        }

        if (is_array($reset)) {
            $cats = $reset;
        } else {
            $select = 'id, parent_id, title';
            if ($this->table_name == 'departments') {
                $select = 'id, parent_id, title, user_title';
            }
            if (in_array($this->table_name, ['article_categories', 'download_categories', 'news_categories'])) {
                $select = 'id, parent_id, title, brand_id';
            }

            $cats = $this->em->getConnection()->fetchAllKeyed("
                SELECT $select
                FROM {$this->table_name}
                ORDER BY display_order ASC, id ASC
            ", [], 'id');
        }

        $this->_cat_ids = [];
        foreach ($cats as &$c) {
            foreach (['id', 'parent_id', 'brand_id'] as $k) {
                if (!empty($c[$k])) {
                    $c[$k] = (int) $c[$k];
                }
            }
            $c['url_slug'] = $c['id'].'-'.Strings::slugifyTitle($c['title']);

            if (!isset($c['user_title']) || !$c['user_title']) {
                $c['user_title'] = $c['title'];
            }

            $this->_cat_ids[]      = $c['id'];
            $this->_cats[$c['id']] = $c;
        }
        unset($c);

        foreach ($cats as $c) {
            $this->_cat_parent_map[$c['id']] = $c['parent_id'] ? $c['parent_id'] : 0;
        }

        $this->_cat_names = Arrays::flattenToIndex($cats, 'title');

        $cats                      = Arrays::intoHierarchy($cats, null);
        $this->_cat_hierarchy      = $cats;
        $this->_cat_hierarchy_flat = Arrays::flattenHierarchy($cats);

        return $this->_cat_hierarchy;
    }

    /**
     * Get an array of child=>parent for all categories.
     *
     * @return array
     */
    public function getParentMap()
    {
        return $this->_cat_parent_map;
    }

    /**
     * Gets the names for each cat, indexed by cat ID.
     *
     * @return array
     */
    public function getNames($for_ids = null)
    {
        $this->getInHierarchy();
        if ($for_ids === null) {
            $for_ids = $this->_cat_ids;
        }

        $ret = [];
        foreach ($for_ids as $id) {
            if (isset($this->_cat_names[$id])) {
                $ret[$id] = $this->_cats[$id]['title'];
            }
        }

        return $ret;
    }

    /**
     * Gets a flat array of cat names, indexed by cat ID. Children
     * names are separated by $sep.
     *
     * @return array
     */
    public function getFullNames($sep = ' > ', $include_tops = true)
    {
        if ($sep === null) {
            $sep = ' > ';
        }

        return $this->_getFullNames([], $this->getInHierarchy(), $sep, $include_tops);
    }

    protected function _getFullNames($basenames, $cats, $sep, $include_tops)
    {
        $names = [];

        foreach ($cats as $k => $cat) {
            $name   = $basenames;
            $name[] = $cat['title'];

            if (!$cat['children'] or $include_tops) {
                $names[$k] = implode($sep, $name);
            }
            if ($cat['children']) {
                $names = Arrays::mergeAssoc($names, $this->_getFullNames($name, $cat['children'], $sep, $include_tops));
            }
        }

        return $names;
    }

    /**
     * Get a flat hierarchy, where children are in the main array but have an increasing 'depth'.
     *
     * @return array
     */
    public function getFlatHierarchy()
    {
        $this->getInHierarchy();

        return $this->_cat_hierarchy_flat;
    }

    /**
     * Get IDs of parents in order (left to right).
     *
     * @param $category
     *
     * @return array
     */
    public function getPathIds($category)
    {
        $ids = [];

        $cat_id = is_object($category) ? $category->getId() : $category;

        while (!empty($this->_cat_parent_map[$cat_id])) {
            $cat_id = $this->_cat_parent_map[$cat_id];
            $ids[]  = $cat_id;
        }

        $ids = array_reverse($ids);

        return $ids;
    }

    /**
     * Get category entities for all parents.
     *
     * @param $category
     *
     * @return array
     */
    public function getPath($category)
    {
        $ids = $this->getPathIds($category);

        if (!$ids) {
            return [];
        }

        return $this->repos->getByIds($ids, true);
    }

    /**
     * Get children IDs of a category.
     *
     * @param int|\Application\DeskPRO\Entity\CategoryAbstract $category
     * @param bool                                             $direct   Only get the immediate children?
     *
     * @return int[]
     */
    public function getChildrenIds($category = null, $direct = true)
    {
        $this->getInHierarchy();

        // All ids if null
        if ($category === null) {
            return $this->_cat_ids;
        }

        $cat_id    = is_object($category) ? $category->getId() : $category;
        $child_ids = [];

        if (!isset($this->_cat_hierarchy_flat[$cat_id])) {
            return [];
        }

        $start = false;
        $depth = null;
        foreach ($this->_cat_hierarchy_flat as $c) {
            if ($start) {
                // Once we go under the cat depth, we're no
                // longer traversing this category tree
                if ($c['depth'] <= $depth) {
                    break;
                }

                // Once we get one level deeper, then we're
                // no longer direct children
                if ($direct && $c['depth'] >= $depth + 2) {
                    continue;
                }

                $child_ids[] = $c['id'];
            } elseif ($c['id'] == $cat_id) {
                $start = true;
                $depth = $c['depth'];
            }
        }

        return $child_ids;
    }

    /**
     * Get children IDs of a category.
     *
     * @param int|\Application\DeskPRO\Entity\CategoryAbstract $category
     * @param bool                                             $direct   Only get the immediate children?
     *
     * @return \Application\DeskPRO\Entity\CategoryAbstract[]
     */
    public function getChildren($category = null, $direct = true)
    {
        $ids = $this->getChildrenIds($category, $direct);
        if (!$ids) {
            return [];
        }

        return $this->repos->getByIds($ids, true);
    }

    /**
     * @deprecated
     */
    public function children($category = null, $direct = true)
    {
        return $this->getChildren($category, $direct);
    }

    /**
     * Get an array of all cat IDs in a tree including the parent itself (optionally disabled).
     *
     * @param int $parent_id
     *
     * @return array
     */
    public function getIdsInTree($parent_id, $incude_top = true)
    {
        $parent_id = is_object($parent_id) ? $parent_id->getId() : $parent_id;

        $ids = $this->getChildrenIds($parent_id);

        if ($incude_top) {
            array_unshift($ids, $parent_id);
        }

        return $ids;
    }

    /**
     * Get IDs of all categories that are leafs (dont have children).
     *
     * @retrun array
     */
    public function getLeafIds()
    {
        return App::getDb()->fetchAllCol('
            SELECT DISTINCT c.id
            FROM feedback_categories c
            LEFT JOIN feedback_categories AS c2 ON (c2.parent_id = c.id)
            WHERE c2.id IS NULL
        ');
    }

    public function getTotalCounts(array $counts)
    {
        $counts['0_total'] = 0;

        foreach ($this->getIds() as $c_id) {
            $total = 0;
            if (isset($counts[$c_id])) {
                $total = $counts[$c_id];
            }

            foreach ($this->getChildrenIds($c_id, false) as $child_id) {
                if (isset($counts[$child_id])) {
                    $total += $counts[$child_id];
                }
            }

            $counts["{$c_id}_total"] = $total;
            $counts['0_total'] += $total;
        }

        return $counts;
    }

    public function getCategoriesForUsergroups(array $usergroup_ids)
    {
        $permission_table_name = $this->repos->getPermissionTableName();

        if (!$permission_table_name) {
            throw new \BadMethodCallException('There is no permissions table set');
        }

        // For categories, everyone is always on, even if its disabled,
        // because everyone still means everyone from agent ui perspective
        $usergroup_ids[] = App::$container->getUserGroups()->getEveryoneGroup()->id;

        if (!$usergroup_ids) {
            return [];
        }

        $conn = App::getDb();
        $qb   = $conn->createQueryBuilder();

        $tbl = $conn->quoteIdentifier($permission_table_name);
        $qb->select('t.category_id');
        $qb->from($tbl, 't');
        $qb->andWhere($qb->expr()->in('t.usergroup_id', $usergroup_ids));
        $qb->groupBy('t.category_id');

        $brandRelatedCategories = [
            ArticleCategory::class,
            DownloadCategory::class,
            NewsCategory::class,
        ];

        if (in_array($this->class->name, $brandRelatedCategories)) {
            /** @var BrandStack $brandStack */
            $brandStack = App::get('brand_stack');

            $currentBrand = $brandStack->getActive()->getBrand();
            if ($currentBrand && $currentBrand->getId()) {
                $tableName = $this->repos->getTableName();

                $qb->innerJoin('t', $tableName, 'c', 'c.id = t.category_id');
                $qb->andWhere($qb->expr()->eq('c.brand_id', $currentBrand->getId()));
            }
        }

        $catIds = $conn->fetchAllCol($qb->getSQL());

        return $catIds;
    }
}
