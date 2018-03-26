<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use Doctrine\Common\NotifyPropertyChanged;

/**
 * Interface Incident.
 *
 * Incident is a system problem which is based on some set of system events.
 */
interface Incident extends EntityInterface, NotifyPropertyChanged
{
    /**
     * Incident constructor.
     */
    public function __construct();

    /**
     * If incident is raised.
     *
     * As incident is based on set of events, there can be situation when events set is not significant enough to raise
     * an incident so we refer to this set as not raised incident.
     *
     * @return bool
     */
    public function isRaised();

    /**
     * @param bool $raised
     */
    public function setRaised($raised);

    /**
     * Get human readable incident title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * @return \DateTime
     */
    public function getDateCreated();

    /**
     * @return Event[]
     */
    public function getEvents();

    /**
     * @return Event[] Get events which are not yet persisted
     */
    public function getNewEvents();

    /**
     * @return int
     */
    public function getEventsCount();

    /**
     * @param array $events
     */
    public function setEvents(array $events);

    /**
     * @param Event $event
     */
    public function addEvent(Event $event);

    /**
     * @return Event
     */
    public function getFirstEvent();

    /**
     * @return Event
     */
    public function getLastEvent();

    /**
     * @return Event
     */
    public function getFirstFailureEvent();

    /**
     * @return Event
     */
    public function getLastFailureEvent();

    /**
     * @return int
     */
    public function getFailureEventsCount();

    /**
     * @return \DateTime
     */
    public function getDateFirstFailure();

    /**
     * @return \DateTime
     */
    public function getDateLastFailure();

    /**
     * @return bool
     */
    public function isDismissed();

    /**
     * @param bool $dismissed
     */
    public function setDismissed($dismissed);

    /**
     * @return string
     */
    public function getSubjectUniqueId();
}
