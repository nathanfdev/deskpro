<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ContentSearch\Fetcher;

use Application\DeskPRO\Entity\Person;

abstract class AbstractFetcher
{
    const TYPENAME = '__';

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @param \Application\DeskPRO\Entity\Person $person Person context to run the search from
     */
    public function __construct(Person $person)
    {
        $this->person = $person;
    }

    /**
     * Returns an array of entities identified by $related_ids, that the user is able to see.
     *
     * @param array $related_ids
     *
     * @return array
     */
    abstract public function getEntities(array $related_ids);
}
