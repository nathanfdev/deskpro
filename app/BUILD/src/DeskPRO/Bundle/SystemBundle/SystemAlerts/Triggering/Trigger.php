<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;

/**
 * Interface Trigger.
 *
 * Base Trigger class. Trigger is an object consuming logged events of certain types and rising incidents.
 */
interface Trigger
{
    /**
     * @return string Get full Incident concrete class name
     */
    public function getIncidentClass();

    /**
     * Consumes a logged event and returns an Incident if it's created or updated.
     *
     * @param Event $event
     *
     * @return Incident|null
     */
    public function consume(Event $event);

    /**
     * @param callable $raisedCallback
     */
    public function setRaisedCallback(callable $raisedCallback);
}
