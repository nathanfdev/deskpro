<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy;
use Orb\Util\Arrays;

class AbstractCategoryRepository extends AbstractEntityRepository
{
    /** @var CategoryHierarchy|null */
    protected $_cat_helper = null;

    /**
     * @return \Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy
     */
    public function getCategoryHelper()
    {
        if ($this->_cat_helper !== null) {
            return $this->_cat_helper;
        }

        $this->_cat_helper = new CategoryHierarchy(
            $this->getEntityManager(),
            $this,
            $this->getEntityName(),
            $this->getClassMetadata(),
            $this->getPermissionTableName()
        );

        return $this->_cat_helper;
    }

    /**
     * @return string
     */
    public function getPermissionTableName()
    {
        return;
    }

    public function getCategoryField()
    {
        return 'category_id';
    }

    /**
     * Runs through the hierarchy to reset 'depth' and 'root' values,
     * and updates all 'display_order' so that they are stored in
     * real tree order.
     *
     * This isnt just "bad" thing, it should be called for example
     * when a new category is created, or one is deleted.
     */
    public function repair()
    {
        $cats = $this->_em->getConnection()->fetchAllKeyed('
            SELECT id, parent_id
            FROM `'.$this->getTableName().'`
            ORDER BY display_order ASC, id ASC
        ', [], 'id');

        $flat = Arrays::intoHierarchy($cats);
        $flat = Arrays::flattenHierarchy($flat);

        $all = $this->_em->createQuery("
            SELECT c
            FROM {$this->getEntityName()} c INDEX BY c.id
        ")->execute();

        $display_order = 0;

        $current_root = null;

        $this->_em->getConnection()->beginTransaction();

        try {
            foreach ($flat as $cid => $cinfo) {
                $cat = $all[$cid];

                $display_order += 10;
                $cat->display_order = $display_order;
                $cat->depth         = $cinfo['depth'];

                if (!$cat->parent) {
                    $current_root = $cat;
                    $cat->root    = $cat->getId();
                } else {
                    $cat->root = $current_root['id'];
                }

                $this->_em->persist($cat);
            }

            $this->_em->flush();
            $this->_em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->_em->getConnection()->rollback();
            throw $e;
        }
    }

    /**
     * Pass through to helper.
     *
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->getCategoryHelper(), $method], $args);
    }
}
