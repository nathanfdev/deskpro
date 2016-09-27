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

use DeskPRO\Bundle\AppBundle\Entity\NotifyPropertyChangedTrait;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\SuccessEvent;
use DeskPRO\Bundle\SystemBundle\Exception\DenormalizationException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractIncident.
 *
 * @ORM\Entity
 * @ORM\Table(name="system_alerts_incidents")
 * @ORM\ChangeTrackingPolicy("DEFERRED_IMPLICIT")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=30)
 * @ORM\DiscriminatorMap({
 *     "generic_exception"      = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Exception\ExceptionIncident",
 *     "incoming_email_failure" = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email\IncomingEmailFailureIncident",
 *     "outgoing_email_failure" = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email\OutgoingEmailFailureIncident",
 *     "php_critical_error"     = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\PHP\PhpCriticalErrorIncident",
 *     "php_notice"             = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\PHP\PhpNoticeIncident"
 * })
 *
 * @JMS\ExclusionPolicy("all")
 */
abstract class AbstractIncident implements Incident
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="subject_unique_id", type="string", nullable=true)
     */
    protected $subjectUniqueId;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     */
    protected $dateCreated;

    /**
     * @var Event[]|ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent",
     *     inversedBy="incidents",
     *     cascade={"all"},
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\JoinTable(name="system_alerts_incident_events",
     *     joinColumns={@ORM\JoinColumn(name="incident_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $events;

    /**
     * @var Event[] Not persisted field. Stores events passed to the addEvent() method
     */
    protected $newEvents = [];

    /**
     * @var Event|null
     *
     * @ORM\OneToOne(targetEntity="DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent", fetch="EAGER")
     * @ORM\JoinColumn(name="first_failure_event_id", referencedColumnName="id", nullable=true)
     */
    protected $firstFailureEvent;

    /**
     * @var Event|null
     *
     * @ORM\OneToOne(targetEntity="DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent", fetch="EAGER")
     * @ORM\JoinColumn(name="last_failure_event_id", referencedColumnName="id", nullable=true)
     */
    protected $lastFailureEvent;

    /**
     * @var int
     *
     * @ORM\Column(name="failure_events_count", type="integer", nullable=false)
     */
    protected $failureEventsCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="success_events_count", type="integer", nullable=false)
     */
    protected $successEventsCount = 0;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     */
    protected $raised = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     */
    protected $dismissed = false;

    /**
     * {@inheritdoc}
     *
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("title")
     * @JMS\Type("string")
     */
    abstract public function getTitle();

    /**
     * Incident constructor.
     */
    public function __construct()
    {
        $this->dateCreated = new \DateTime();
        $this->events      = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewEvents()
    {
        return $this->newEvents;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsCount()
    {
        return $this->events->count();
    }

    /**
     * {@inheritdoc}
     */
    public function setEvents(array $events)
    {
        foreach ($events as $event) {
            $this->addEvent($event);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addEvent(Event $event)
    {
        $subjectUniqueId = (string) $event->getSubjectUniqueId();

        if ($this->subjectUniqueId && ($this->subjectUniqueId !== $subjectUniqueId)) {
            throw new \Exception(sprintf(
                'Incident must group events with equal subject unique id, expected %s, got %s',
                $this->subjectUniqueId,
                $subjectUniqueId
            ));
        }

        if (!$this->events->contains($event)) {
            $this->events[] = $event;

            // getNewEvents() data -------------------------------------------

            $this->newEvents[] = $event;

            // Denormalized data ---------------------------------------------

            $this->subjectUniqueId = $subjectUniqueId;

            if ($event instanceof SuccessEvent) {
                ++$this->successEventsCount;
            } else {
                ++$this->failureEventsCount;
                $this->firstFailureEvent or $this->firstFailureEvent = $event;
                $this->lastFailureEvent                              = $event;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstEvent()
    {
        if ($this->events->count()) {
            return $this->events->get(0);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLastEvent()
    {
        if ($count = $this->events->count()) {
            return $this->events->get($count - 1);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLastFailureEvent()
    {
        return $this->lastFailureEvent;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateFirstFailure()
    {
        return $this->firstFailureEvent->getDateCreated();
    }

    /**
     * {@inheritdoc}
     */
    public function getDateLastFailure()
    {
        return $this->lastFailureEvent->getDateCreated();
    }

    /**
     * {@inheritdoc}
     */
    public function getFailureEventsCount()
    {
        if ($this->failureEventsCount + $this->successEventsCount !== $this->getEventsCount()) {
            throw new DenormalizationException('Total events count is not equal to sum of failure and success counts');
        }

        return $this->failureEventsCount;
    }

    /**
     * @return bool
     */
    public function isRaised()
    {
        return $this->raised;
    }

    /**
     * @param bool $raised
     */
    public function setRaised($raised)
    {
        $this->raised = $raised;
    }

    /**
     * {@inheritdoc}
     */
    public function isDismissed()
    {
        return $this->dismissed;
    }

    /**
     * {@inheritdoc}
     */
    public function setDismissed($dismissed)
    {
        $this->dismissed = $dismissed;
    }

    /**
     * @return string
     */
    public function getSubjectUniqueId()
    {
        return $this->subjectUniqueId;
    }
}
