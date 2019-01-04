<?php

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\FeedbackStatusCategory as FeedbackStatusCategoryEntity;

/**
 * Class FeedbackStatusCategory.
 */
class FeedbackStatusCategory extends AbstractEntityRepository
{
    /**
     * @param int|\Application\DeskPRO\Entity\Brand $brand
     *
     * @return FeedbackStatusCategory[]
     */
    public function getActiveCategories($brand = null)
    {
        return $this->getCategoriesForType('active', $brand);
    }

    /**
     * @param int|\Application\DeskPRO\Entity\Brand $brand
     *
     * @return FeedbackStatusCategory[]
     */
    public function getClosedCategories($brand = null)
    {
        return $this->getCategoriesForType('closed', $brand);
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

    /**
     * @param string                                $type
     * @param int|\Application\DeskPRO\Entity\Brand $brand
     *
     * @return array
     */
    private function getCategoriesForType($type, $brand = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('c')
            ->from(FeedbackStatusCategoryEntity::class, 'c', 'c.id')
            ->where('c.status_type = :type')
            ->orderBy('c.display_order', 'DESC')
            ->setParameter('type', $type)
        ;

        if ($brand) {
            $qb->andWhere('c.brand = :brand');
            $qb->setParameter('brand', $brand);
        }

        return $qb->getQuery()->getResult();
    }
}
