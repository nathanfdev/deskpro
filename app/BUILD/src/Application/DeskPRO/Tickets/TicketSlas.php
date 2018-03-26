<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\Collection\LazyCollection;
use Orb\Util\Arrays;

class TicketSlas extends LazyCollection
{
    /**
     * @return array
     */
    protected function loadRecords()
    {
        $recs = $this->em->getRepository('DeskPRO:Sla')->findAll();
        $recs = Arrays::keyFromData($recs, 'id');

        return $recs;
    }

    //###################################################################################################################
    // implementing these just for better auto-complete in the IDE (due to @return) :-)

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\Sla
     */
    public function getById($id)
    {
        return parent::getById($id);
    }

    /**
     * @param array $ids
     * @param bool  $keyed
     *
     * @return \Application\DeskPRO\Entity\Sla[]
     */
    public function getByIds(array $ids, $keyed = false)
    {
        return parent::getByIds($ids, $keyed);
    }

    /**
     * @return \Application\DeskPRO\Entity\Sla[]
     */
    public function getAll()
    {
        return parent::getAll();
    }
}
