<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity\Hierarchy;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;

/**
 * Interface Hierarchical.
 */
interface Hierarchical extends EntityInterface
{
    /**
     * @return Hierarchical[]
     */
    public function getChildren();
}
