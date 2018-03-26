<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;

/**
 * Class AbstractTrigger.
 *
 * {@inheritdoc}
 */
abstract class AbstractTrigger implements Trigger
{
    /**
     * @var Incident[] Incident subject unique ID => Incident instance
     *
     * @see \DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event::getSubjectUniqueId()
     */
    protected $risingIncidents = [];

    /**
     * @var callable Executed when the issue is created
     */
    protected $raisedCallback;

    /**
     * @var array Parameters to pass to the, will be prepended with an Incident instance
     */
    protected $raisedCallbackParams;

    /**
     * {@inheritdoc}
     */
    public function setRaisedCallback(callable $raisedCallback, array $raisedCallbackParams = [])
    {
        $this->raisedCallback       = $raisedCallback;
        $this->raisedCallbackParams = $raisedCallbackParams;
    }

    /**
     * {@inheritdoc}
     */
    public function consume(Event $event)
    {
        if ($this->supports($event)) {
            $subject = $event->getSubjectUniqueId();
            if (!array_key_exists($subject, $this->risingIncidents)) {
                $class                           = $this->getIncidentClass();
                $this->risingIncidents[$subject] = new $class();
            }

            $incident = $this->risingIncidents[$subject];
            $incident->addEvent($event);
            if ($this->isIncidentState($incident)) {
                $incident->setRaised(true);
                if ($this->raisedCallback) {
                    call_user_func($this->raisedCallback, $incident);
                }
                unset($this->risingIncidents[$subject]);
            }

            return $incident;
        }
    }

    /**
     * Whether trigger supports an Event.
     *
     * @param Event $event
     *
     * @return bool
     */
    abstract protected function supports(Event $event);

    /**
     * Decides if incident criteria are met.
     *
     * @param Incident $incident
     *
     * @return bool
     */
    abstract protected function isIncidentState(Incident $incident);
}
