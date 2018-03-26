<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

interface FilterInterface
{
    /**
     * Retrieves the filter's ID.
     *
     * @return int the filter's ID
     */
    public function getId();

    /**
     * Retrieve the filter's title.
     *
     * @return string The filter's title
     */
    public function getTitle();

    /**
     * Get the last filter update's date.
     *
     * @return \DateTime
     */
    public function getDateUpdated();
}
