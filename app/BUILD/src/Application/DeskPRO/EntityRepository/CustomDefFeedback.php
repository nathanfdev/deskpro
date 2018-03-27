<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class CustomDefFeedback extends CustomDefAbstract
{
    /**
     * @return \Application\DeskPRO\Entity\CustomDefFeedback|null
     */
    public function getCategoryField()
    {
        return $this->_em->createQuery("
            SELECT f
            FROM DeskPRO:CustomDefFeedback f
            WHERE f.sys_name = 'cat'
        ")->setMaxResults(1)->getOneOrNullResult();
    }

    /**
     * @param \Application\DeskPRO\Entity\CustomDefFeedback $parent_category
     *
     * @return array
     */
    public function getAllFlatData(\Application\DeskPRO\Entity\CustomDefFeedback $parent_category)
    {
        $hierarchy = [];

        if ($parent_category['handler_class'] == 'Application\\DeskPRO\\CustomFields\\Handler\\Choice') {
            $choices = [];

            foreach ($parent_category->children as $child) {
                $choices[$child->getId()] = $child;

                $hierarchy[] = [
                    'id'        => $child->getId(),
                    'title'     => $child->getTitle(),
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
