<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line;

use Application\DeskPRO\Entity\Person;

interface LineInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return \DateTime
     */
    public function getDateTime();

    /**
     * @return Person|null
     */
    public function getPerson();
}
