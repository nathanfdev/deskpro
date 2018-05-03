<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

/**
 * A piece of data that has a meaningful identity in the system.
 *
 * Doctrine entities implement this interface, but other models could implement the EntityInterface as well.
 */
interface EntityInterface
{
    /**
     * A unique identifier for this entity. Usually an integer, but can also be an array for a composite ID.
     *
     * Empty or bolean values are all considered to be equal, and can safely be interpreted as "null". For instance all
     * of these values would be considered to be a "null" id:
     *  - boolean (true or false)
     *  - empty string
     *  - empty array
     *  - null
     *  - an array of the above values.
     *
     * It is important to note that the integer zero is NOT considered null, and is a valid ID.
     *
     * @return mixed
     */
    public function getId();
}
