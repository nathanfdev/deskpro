<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Orb\Util\Arrays;

class TicketActionDef extends AbstractEntityRepository
{
    public function getActions($index_by_type = false)
    {
        $matches = $this->_em->createQuery('
            SELECT a
            FROM DeskPRO:TicketActionDef a
        ')->execute();

        if ($index_by_type) {
            $matches = Arrays::keyFromData($matches, 'event_type');
        }

        return $matches;
    }
}
