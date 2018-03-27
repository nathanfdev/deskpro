<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\Entity\Person;

/**
 * This interface is meant to signify that the object should run in the context
 * of a person. This usually means some type of permissions apply.
 */
interface PersonContextInterface
{
    public function setPersonContext(Person $person);
}
