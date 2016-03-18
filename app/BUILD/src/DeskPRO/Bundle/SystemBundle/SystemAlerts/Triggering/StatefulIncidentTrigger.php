<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;

/**
 * Class StatefulIncidentTrigger.
 *
 * Base class for stateful incidents triggers. Stateful incident have a clear start/stop, unlike simple fire-and-forget
 * incidents, stateful incidents can be continuing and can be updated when new corresponding events occur.
 */
abstract class StatefulIncidentTrigger extends Trigger
{
    /**
     * @var Incident
     */
    protected $continuing_incident = null;

    /**
     * @var callable Executed when the issue is created
     */
    private $raised_callback;

    /**
     * @var callable Executed when the trigger criteria stops matching (i.e. the problem goes away)
     */
    private $resolved_callback;

    /**
     * @var callable Executed after a continuing incident is updated
     */
    private $continuing_callback;

    /**
     * @var callable Executed when an admin manually dismisses an incident
     */
    private $dismissed_callback;

    /**
     * @var array Array of Incident concrete classes which should be dismissed with the passed callback
     */
    private $dismissed_incident_types = [];

    /**
     * @var callable Executed when the issue is either dismissed or resolved
     */
    private $closed_callback;

    /**
     * @return bool
     */
    public function hasContinuingIncident()
    {
        return $this->continuing_incident !== null;
    }

    /**
     * @return Incident
     */
    public function getContinuingIncident()
    {
        return $this->continuing_incident;
    }

    /**
     * @param Incident $continuing_incident
     */
    public function setContinuingIncident(Incident $continuing_incident)
    {
        $this->continuing_incident = $continuing_incident;
    }

    /**
     * {@inheritdoc}
     */
    public function consume(Event $event)
    {
        if ($this->supports($event)) {
            $this->process($event);

            // If incident criteria are met, then depending on whether there is a continuing
            // incident we either update it or create a new one
            if ($this->isIncidentState()) {
                if ($this->continuing_incident) {
                    $this->updateContinuingIncident($event);
                    if ($this->continuing_callback) {
                        call_user_func($this->continuing_callback, $this->continuing_incident);
                    }
                } else {
                    $this->continuing_incident = $this->createIncident();
                    if ($this->raised_callback) {
                        call_user_func($this->raised_callback, $this->continuing_incident);
                    }

                    return $this->continuing_incident;
                }
            }

            // If incident criteria are no longer valid, then we need to mark the continuing incident as resolved
            elseif ($this->continuing_incident) {
                $resolved_incident = $this->continuing_incident;
                $resolved_incident->setResolved(true);
                if ($this->resolved_callback) {
                    call_user_func($this->resolved_callback, $resolved_incident);
                }
                if ($this->closed_callback) {
                    call_user_func($this->closed_callback, $resolved_incident);
                }

                $this->continuing_incident = null;
                $this->initState();

                return $resolved_incident;
            }
        }
    }

    /**
     * @param callable $resolved_callback
     */
    public function setResolvedCallback(callable $resolved_callback)
    {
        $this->resolved_callback = $resolved_callback;
    }

    /**
     * @param callable $continuing_callback
     */
    public function setContinuingCallback(callable $continuing_callback)
    {
        $this->continuing_callback = $continuing_callback;
    }

    /**
     * @param callable     $dismissed_callback
     * @param string|array $dismissed_incident_types
     *
     * @throws \Exception
     */
    public function setDismissedCallback(callable $dismissed_callback, $dismissed_incident_types)
    {
        if (!is_array($dismissed_incident_types)) {
            $dismissed_incident_types = [$dismissed_incident_types];
        }
        if (empty($dismissed_incident_types)) {
            throw new \Exception(
                'You must provide at least one Incident type which should be dismissed with the passed callback');
        }
        foreach ($dismissed_incident_types as $type) {
            if (!is_subclass_of($type, Incident::class, true)) {
                throw new \Exception("'$type' is not a subclass of Incident");
            }
        }

        $this->dismissed_incident_types = $dismissed_incident_types;
        $this->dismissed_callback       = $dismissed_callback;
    }

    /**
     * If trigger dismiss and close callbacks are applicable to an incident.
     *
     * @param Incident $incident
     *
     * @return bool
     */
    public function dismisses(Incident $incident)
    {
        return in_array(get_class($incident), $this->dismissed_incident_types);
    }

    /**
     * @param callable $closed_callback
     */
    public function setClosedCallback(callable $closed_callback)
    {
        $this->closed_callback = $closed_callback;
    }

    /**
     * @return callable|null
     */
    public function getDismissedCallback()
    {
        return $this->dismissed_callback;
    }

    /**
     * @return callable
     */
    public function getClosedCallback()
    {
        return $this->closed_callback;
    }

    /**
     * Updates a continuing incident with information from new logged events.
     *
     * (!) Note that this method should not persist an incident with entity manager, it should just update and return
     *
     * @return Incident Updated incident
     */
    abstract protected function updateContinuingIncident();
}
