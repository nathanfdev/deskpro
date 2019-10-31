<?php

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Brand as BrandEntity;

class CustomDefCommunityTopic extends CustomDefAbstract
{
    /**
     * @param BrandEntity $brand
     *
     * @return \Application\DeskPRO\Entity\CustomDefCommunityTopic|null
     */
    public function getChannelField(BrandEntity $brand)
    {
        return $this->_em
            ->createQuery("
                SELECT f
                FROM DeskPRO:CustomDefCommunityTopic f
                WHERE f.sys_name = 'cat' AND f.brand = :brand
            ")
            ->setParameter('brand', $brand)
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param \Application\DeskPRO\Entity\CustomDefCommunityTopic $parent_category
     *
     * @return array
     */
    public function getAllFlatData(\Application\DeskPRO\Entity\CustomDefCommunityTopic $parent_category)
    {
        $hierarchy = [];

        if ($parent_category['handler_class'] == 'Application\\DeskPRO\\CustomFields\\Handler\\Choice') {
            $choices = [];

            foreach ($parent_category->children as $child) {
                $choices[$child->getId()] = $child;

                $hierarchy[] = [
                    'id'        => $child->getId(),
                    'title'     => $child->getTitle(),
                    'brand'     => $child->getBrand() ? $child->getBrand()->getId() : null,
                    'parent_id' => $child->getOption('parent_id', 0),
                ];
            }

            usort(
                $hierarchy,
                function ($a, $b) use ($choices) {
                    $f1 = $choices[$a['id']];
                    $f2 = $choices[$b['id']];

                    if ($f1->getDisplayOrder() == $f2->getDisplayOrder()) {
                        return 0;
                    }

                    return $f1->getDisplayOrder() < $f2->getDisplayOrder() ? -1 : 1;
                }
            );
        }

        return $hierarchy;
    }
}
