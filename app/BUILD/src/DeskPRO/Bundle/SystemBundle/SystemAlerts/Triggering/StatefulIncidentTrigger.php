<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
