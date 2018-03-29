<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Doctrine\ORM\EntityManager;

interface EntityManagerAwareInterface
{
    public function setEm(EntityManager $em);
}
