<?php

/**
 * DeskPRO.
 *
 * @category Entities\Interfaces
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;

interface PersonList
{
    /**
     * @return Person[]
     */
    public function getPersonList();
}
