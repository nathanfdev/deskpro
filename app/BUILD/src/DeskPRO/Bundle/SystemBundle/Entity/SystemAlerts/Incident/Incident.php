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
