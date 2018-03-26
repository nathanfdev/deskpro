<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class TicketFilterRepository extends EntityRepository
{
    public function getFilters()
    {
        return $this->findAll();
    }
}
