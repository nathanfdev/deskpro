<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class PortalPageDisplay extends AbstractEntityRepository
{
    public function getEnabledBlocks()
    {
        return $this->getEntityManager()->createQuery('
            SELECT p
            FROM DeskPRO:PortalPageDisplay p
            WHERE p.is_enabled = 1
            ORDER BY p.display_order ASC
        ')->execute();
    }

    public function getAllBlocks()
    {
        return $this->getEntityManager()->createQuery('
            SELECT p
            FROM DeskPRO:PortalPageDisplay p
            ORDER BY p.display_order ASC
        ')->execute();
    }
}
