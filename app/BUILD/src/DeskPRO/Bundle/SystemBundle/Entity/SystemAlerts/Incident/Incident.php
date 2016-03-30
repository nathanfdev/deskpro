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
use DeskPRO\Bundle\AppBundle\Entity\NotifyPropertyChangedTrait;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Incident.
 *
 * @ORM\Entity
 * @ORM\Table(name="system_alerts_incidents")
 * @ORM\ChangeTrackingPolicy("DEFERRED_IMPLICIT")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=30)
 * @ORM\DiscriminatorMap({
 *     "generic_exception"      = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\ExceptionIncident",
 *     "incoming_email_failure" = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email\IncomingEmailFailureIncident",
 *     "outgoing_email_failure" = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email\OutgoingEmailFailureIncident"
 * })
 */
abstract class Incident implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $date_created;

    /**
     * @var Event[]
     *
     * @ORM\ManyToMany(targetEntity="DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event")
     * @ORM\JoinTable(
     *     name="system_alerts_incident_events",
     *     joinColumns={@ORM\JoinColumn(name="incident_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE", unique=true)}
     * )
     */
    protected $events;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $resolved = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $dismissed = false;

    /**
     * Incident constructor.
     */
    public function __construct()
    {
        $this->date_created = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @return Event[]
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @return array
     */
    public function getEventIds()
    {
        $ids = [];
        foreach ($this->events as $event) {
            $ids[] = $event->getId();
        }

        return $ids;
    }

    /**
     * @param array $events
     */
    public function setEvents(array $events)
    {
        $this->events = new ArrayCollection($events);
    }

    /**
     * @param Event $event
     */
    public function addEvent(Event $event)
    {
        $this->events[] = $event;
    }

    /**
     * @return bool
     */
    public function isResolved()
    {
        return $this->resolved;
    }

    /**
     * @param bool $resolved
     */
    public function setResolved($resolved)
    {
        $this->resolved = $resolved;
    }

    /**
     * @return bool
     */
    public function isDismissed()
    {
        return $this->dismissed;
    }

    /**
     * @param bool $dismissed
     */
    public function setDismissed($dismissed)
    {
        $this->dismissed = $dismissed;
    }

    /**
     * Get user instructions to handle the incident.
     *
     * @return string
     */
    abstract public function getInstructions();
}
