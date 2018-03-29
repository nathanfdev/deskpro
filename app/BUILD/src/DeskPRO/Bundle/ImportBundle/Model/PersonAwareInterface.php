<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

/**
 * Entity person related interface.
 *
 * Interface PersonAwareInterface
 */
interface PersonAwareInterface
{
    /**
     * Person email.
     *
     * @return string
     */
    public function getPerson();

    /**
     * Set person email.
     *
     * @param string $person
     *
     * @return $this
     */
    public function setPerson($person);
}
