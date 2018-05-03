<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionChecker;

use Application\DeskPRO\Entity\Person;

/**
 * A permission checker knows how to check access to particular things.
 */
abstract class AbstractChecker
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     */
    public function __construct(Person $person = null)
    {
        $this->person = $person;
        $this->init();
    }

    protected function init()
    {
    }
}
