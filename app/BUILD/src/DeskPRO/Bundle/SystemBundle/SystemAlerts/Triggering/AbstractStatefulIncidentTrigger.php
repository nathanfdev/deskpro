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
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\StatefulIncident;

/**
 * Class AbstractStatefulIncidentTrigger.
 *
 * {@inheritdoc}
 */
abstract class AbstractStatefulIncidentTrigger extends AbstractTrigger implements StatefulIncidentTrigger
{
    /**
     * @var StatefulIncident[] Incident subject unique ID => Incident instance
     *
     * @see \DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event::getSubjectUniqueId()
     */
    protected $continuingIncidents = [];

    /**
     * @var callable Executed when the trigger criteria stops matching (i.e. the problem goes away)
     */
    private $resolvedCallback;

    /**
     * @var callable Executed after a continuing incident is updated
     */
    private $continuingCallback;

    /**
     * @var callable Executed when an admin manually dismisses an incident
     */
    private $dismissedCallback;

    /**
     * @var callable Executed when the issue is either dismissed or resolved
     */
    private $closedCallback;

    /**
     * {@inheritdoc}
     */
    public function consume(Event $event)
    {
        if ($this->supports($event)) {
            $subject = $event->getSubjectUniqueId();
            if (!array_key_exists($subject, $this->continuingIncidents)) {
                $class                               = $this->getIncidentClass();
                $this->continuingIncidents[$subject] = new $class();
            }

            $incident = $this->continuingIncidents[$subject];
            $incident->addEvent($event);
            if ($this->isIncidentState($incident)) {
                if (!$incident->isRaised()) {
                    $incident->setRaised(true);
                    if ($this->raisedCallback) {
                        call_user_func_array(
                            $this->raisedCallback, array_merge([$incident], $this->raisedCallbackParams));
                    }
                } else {
                    if ($this->continuingCallback) {
                        call_user_func($this->continuingCallback, $incident);
                    }
                }
            } else {
                if ($incident->isRaised()) {
                    $incident->setResolved(true);
                    if ($this->resolvedCallback) {
                        call_user_func($this->resolvedCallback, $incident);
                    }
                    if ($this->closedCallback) {
                        call_user_func($this->closedCallback, $incident);
                    }
                }
            }

            return $incident;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setContinuingIncidents(array $incidents)
    {
        $this->continuingIncidents = [];
        foreach ($incidents as $incident) {
            $this->continuingIncidents[$incident->getFirstEvent()->getSubjectUniqueId()] = $incident;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setResolvedCallback(callable $resolvedCallback)
    {
        $this->resolvedCallback = $resolvedCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function setContinuingCallback(callable $continuingCallback)
    {
        $this->continuingCallback = $continuingCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function setDismissedCallback(callable $dismissedCallback)
    {
        $this->dismissedCallback = $dismissedCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function dismisses(StatefulIncident $incident)
    {
        return get_class($incident) === $this->getIncidentClass();
    }

    /**
     * {@inheritdoc}
     */
    public function setClosedCallback(callable $closedCallback)
    {
        $this->closedCallback = $closedCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function getDismissedCallback()
    {
        return $this->dismissedCallback;
    }

    /**
     * {@inheritdoc}
     */
    public function getClosedCallback()
    {
        return $this->closedCallback;
    }
}
