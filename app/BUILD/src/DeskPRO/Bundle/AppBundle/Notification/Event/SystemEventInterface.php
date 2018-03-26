<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event;

/**
 * Interface SystemEventInterface.
 */
interface SystemEventInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function __sleep();
}
