<?php

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident;

use DeskPRO\Bundle\AppBundle\Entity\NotifyPropertyChangedTrait;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\SuccessEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

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
 */
abstract class AbstractIncident implements Incident
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
     * @var string
     *
     * @ORM\Column(name="subject_unique_id", type="string", nullable=true)
     */
    protected $subjectUniqueId;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
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
     */
    protected $raised = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $dismissed = false;

    /**
     * {@inheritdoc}
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

            // Denormalized data --------------------------------------------

            $this->subjectUniqueId = $subjectUniqueId;

            if ($event instanceof SuccessEvent) {
                ++$this->successEventsCount;
            } else {
                ++$this->failureEventsCount;

                // Clear the first failure in to make it set to the current event
                if ($this->events->last() instanceof SuccessEvent) {
                    $this->firstFailureEvent = null;
                }

                $this->firstFailureEvent or $this->firstFailureEvent = $event;
                $this->lastFailureEvent                              = $event;
            }

            // Push the new event -------------------------------------------

            $this->events[] = $event;

            // getNewEvents() data ------------------------------------------

            $this->newEvents[] = $event;
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
    public function getFirstFailureEvent()
    {
        return $this->firstFailureEvent;
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
        return $this->firstFailureEvent ? $this->firstFailureEvent->getDateCreated() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateLastFailure()
    {
        return $this->lastFailureEvent ? $this->lastFailureEvent->getDateCreated() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFailureEventsCount()
    {
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
