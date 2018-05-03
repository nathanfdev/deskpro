<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\StatefulIncident;

/**
 * Class StatefulIncidentTrigger.
 *
 * Base class for stateful incidents triggers. Stateful incident have a clear start/stop, unlike simple fire-and-forget
 * incidents, stateful incidents can be continuing and can be updated when new corresponding events occur.
 */
interface StatefulIncidentTrigger extends Trigger
{
    /**
     * @param StatefulIncident[] $incidents
     */
    public function setContinuingIncidents(array $incidents);

    /**
     * @param callable $resolvedCallback
     */
    public function setResolvedCallback(callable $resolvedCallback);

    /**
     * @param callable $continuingCallback
     */
    public function setContinuingCallback(callable $continuingCallback);

    /**
     * @param callable $dismissedCallback
     */
    public function setDismissedCallback(callable $dismissedCallback);

    /**
     * If trigger dismiss and close callbacks are applicable to an incident.
     *
     * As incidents dismissal happens outside of Trigger context by some external service, we need this method
     * for the dismissing process so that it can determine if an incident should be dismissed with callback
     * of this trigger.
     *
     * @param StatefulIncident $incident
     *
     * @return bool
     */
    public function dismisses(StatefulIncident $incident);

    /**
     * @param callable $closedCallback
     */
    public function setClosedCallback(callable $closedCallback);

    /**
     * @return callable|null
     */
    public function getDismissedCallback();

    /**
     * @return callable
     */
    public function getClosedCallback();
}
