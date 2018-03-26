<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

/**
 * Class FeedbackStatusCategory.
 */
class FeedbackStatusCategory extends AbstractEntityRepository
{
    /** @var array|null */
    protected $active_cats = null;
    /** @var array|null */
    protected $closed_cats = null;

    public function reload()
    {
        $this->active_cats = $this->getEntityManager()->createQuery('
            SELECT c
            FROM DeskPRO:FeedbackStatusCategory c INDEX BY c.id
            WHERE c.status_type = ?1
            ORDER BY c.display_order DESC
        ')->setParameter(1, 'active')->execute();

        $this->closed_cats = $this->getEntityManager()->createQuery('
            SELECT c
            FROM DeskPRO:FeedbackStatusCategory c INDEX BY c.id
            WHERE c.status_type = ?1
            ORDER BY c.display_order DESC
        ')->setParameter(1, 'closed')->execute();
    }

    public function getActiveCategories()
    {
        if ($this->active_cats === null) {
            $this->reload();
        }

        return $this->active_cats;
    }

    public function getClosedCategories()
    {
        if ($this->closed_cats === null) {
            $this->reload();
        }

        return $this->closed_cats;
    }

    public function getNames(array $for_ids = null)
    {
        $categories = $this->findAll();

        $ret = [];
        foreach ($categories as $category) {
            if ($for_ids === null || in_array($category->id, $for_ids)) {
                $ret[$category->id] = $category->title;
            }
        }

        return $ret;
    }
}
