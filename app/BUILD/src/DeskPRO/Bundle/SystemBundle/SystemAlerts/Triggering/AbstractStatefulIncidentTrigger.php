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
    protected $continuing_incidents = [];

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
     * @var callable Executed when the issue is either dismissed or resolved
     */
    private $closed_callback;

    /**
     * {@inheritdoc}
     */
    public function consume(Event $event)
    {
        if ($this->supports($event)) {
            $subject = $event->getSubjectUniqueId();
            if (!array_key_exists($subject, $this->continuing_incidents)) {
                $class                                = $this->getIncidentClass();
                $this->continuing_incidents[$subject] = new $class();
            }

            $incident = $this->continuing_incidents[$subject];
            $incident->addEvent($event);
            if ($this->isIncidentState($incident)) {
                if (!$incident->isRaised()) {
                    $incident->setRaised(true);
                    if ($this->raised_callback) {
                        call_user_func_array(
                            $this->raised_callback, array_merge([$incident], $this->raised_callback_params));
                    }
                } else {
                    if ($this->continuing_callback) {
                        call_user_func($this->continuing_callback, $incident);
                    }
                }
            } else {
                if ($incident->isRaised()) {
                    $incident->setResolved(true);
                    if ($this->resolved_callback) {
                        call_user_func($this->resolved_callback, $incident);
                    }
                    if ($this->closed_callback) {
                        call_user_func($this->closed_callback, $incident);
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
        $this->continuing_incidents = [];
        foreach ($incidents as $incident) {
            $this->continuing_incidents[$incident->getFirstEvent()->getSubjectUniqueId()] = $incident;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setResolvedCallback(callable $resolved_callback)
    {
        $this->resolved_callback = $resolved_callback;
    }

    /**
     * {@inheritdoc}
     */
    public function setContinuingCallback(callable $continuing_callback)
    {
        $this->continuing_callback = $continuing_callback;
    }

    /**
     * {@inheritdoc}
     */
    public function setDismissedCallback(callable $dismissed_callback)
    {
        $this->dismissed_callback = $dismissed_callback;
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
    public function setClosedCallback(callable $closed_callback)
    {
        $this->closed_callback = $closed_callback;
    }

    /**
     * {@inheritdoc}
     */
    public function getDismissedCallback()
    {
        return $this->dismissed_callback;
    }

    /**
     * {@inheritdoc}
     */
    public function getClosedCallback()
    {
        return $this->closed_callback;
    }
}
