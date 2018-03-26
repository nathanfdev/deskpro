<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use Doctrine\ORM\EntityRepository;

class TicketFilterSetRepository extends EntityRepository
{
    /**
     * Array of sets with filters and shared data loaded.
     *
     * @return TicketFilterSet[]
     */
    public function getSetsWithFiltersAndAgents()
    {
        return $this->findAll();
    }
}
