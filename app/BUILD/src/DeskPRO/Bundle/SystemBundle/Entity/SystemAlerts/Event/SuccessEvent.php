<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event;

/**
 * Interface SuccessEvent.
 */
interface SuccessEvent extends Event
{
    /**
     * @return string Full class name of the corresponding failure event
     */
    public function getFailureType();
}
