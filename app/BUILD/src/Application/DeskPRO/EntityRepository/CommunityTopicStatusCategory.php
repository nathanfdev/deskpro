<?php

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Brand as BrandEntity;
use Application\DeskPRO\Entity\CommunityForum as CommunityForumEntity;
use Application\DeskPRO\Entity\CommunityTopicStatusCategory as CommunityTopicStatusCategoryEntity;
use Doctrine\ORM\Query\Expr;

/**
 * Class CommunityTopicStatusCategory.
 */
class CommunityTopicStatusCategory extends AbstractEntityRepository
{
    /**
     * @param int|BrandEntity $brand
     *
     * @return CommunityTopicStatusCategoryEntity[]
     */
    public function getActiveCategories($brand = null)
    {
        return $this->getCategoriesForType('active', $brand);
    }

    /**
     * @param int|CommunityForumEntity $forum
     * @param null|mixed $brand
     *
     * @return CommunityTopicStatusCategoryEntity[]
     */
    public function getActiveCategoriesByForum($forum = null, $brand = null)
    {
        return $this->getCategoriesForType('active', $brand, $forum);
    }

    /**
     * @param int|BrandEntity $brand
     *
     * @return CommunityTopicStatusCategoryEntity[]
     */
    public function getClosedCategories($brand = null)
    {
        return $this->getCategoriesForType('closed', $brand);
    }

    /**
     * @param int|CommunityForumEntity $forum
     * @param null|mixed $brand
     *
     * @return CommunityTopicStatusCategoryEntity[]
     */
    public function getClosedCategoriesByForum($forum = null, $brand = null)
    {
        return $this->getCategoriesForType('closed', $brand, $forum);
    }

    public function getNames(array $for_ids = null)
    {
        /** @var CommunityTopicStatusCategoryEntity[] $categories */
        $categories = $this->findAll();

        $ret = [];
        foreach ($categories as $category) {
            if ($for_ids === null || in_array($category->getId(), $for_ids)) {
                $ret[$category->getId()] = $category->getTitle();
            }
        }

        return $ret;
    }

    /**
     * @param string $type
     * @param int|BrandEntity $brand
     * @param int|CommunityForumEntity $forum
     *
     * @return array
     */
    private function getCategoriesForType($type, $brand = null, $forum = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('c')
            ->from(CommunityTopicStatusCategoryEntity::class, 'c', 'c.id')
            ->where('c.status_type = :type')
            ->orderBy('c.display_order', 'DESC')
            ->setParameter('type', $type);

        if ($brand) {
            $qb->andWhere('c.brand = :brand');
            $qb->setParameter('brand', $brand);
        }

        if ($forum) {
            $qb->innerJoin(CommunityForumEntity::class, 'f', Expr\Join::WITH, 'f.id = :forum')
                ->setParameter('forum', $forum);
        }

        return $qb->getQuery()->getResult();
    }
}
