<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;
use Application\DeskPRO\Tickets\TicketActions\ActionsFactory;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketFollowUp.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ticket_follow_ups", indexes={
 *     @ORM\Index(name="status_date_to_run", columns={"status", "date_to_run"})
 * })
 *
 * @JMS\ExclusionPolicy("all")
 */
class TicketFollowUp implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const STATUS_PENDING   = 'pending';
    const STATUS_DONE      = 'done';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Ticket", inversedBy="followUps")
     * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var Ticket
     */
    private $ticket;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    private $person;

    /**
     * @ORM\Column(name="actions", type="json_array")
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @Assert\Count(min="1")
     *
     * @var array
     */
    private $actions = [];

    /**
     * @ORM\Column(name="cancel_if_user_reply", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $cancelIfUserReply;

    /**
     * @ORM\Column(name="status", type="string", length=255)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @ORM\Column(name="date_to_run", type="datetime")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @Assert\NotNull()
     *
     * @var \DateTime
     */
    private $dateToRun;

    /**
     * @ORM\Column(name="date_did_run", type="datetime", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateDidRun;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dateCreated = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function setTicket(Ticket $ticket = null)
    {
        $this->setModelField('ticket', $ticket);

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return ActionsCollection
     */
    public function getActionsCollection()
    {
        $factory    = new ActionsFactory();
        $collection = new ActionsCollection();

        foreach ($this->actions as $action_info) {
            $action = $factory->createFromInfo($action_info);
            if ($action) {
                $collection->add($action);
            }
        }

        return $collection;
    }

    /**
     * @param array $actions
     *
     * @return $this
     */
    public function setActions(array $actions)
    {
        $this->setModelField('actions', $actions);

        return $this;
    }

    /**
     * @return bool
     */
    public function isCancelIfUserReply()
    {
        return $this->cancelIfUserReply;
    }

    /**
     * @param bool $cancelIfUserReply
     *
     * @return $this
     */
    public function setCancelIfUserReply($cancelIfUserReply)
    {
        $this->setModelField('cancelIfUserReply', $cancelIfUserReply);

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setModelField('status', $status);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated = null)
    {
        $this->setModelField('dateCreated', $dateCreated);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateToRun()
    {
        return $this->dateToRun;
    }

    /**
     * @param \DateTime $dateToRun
     *
     * @return $this
     */
    public function setDateToRun(\DateTime $dateToRun = null)
    {
        $this->setModelField('dateToRun', $dateToRun);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateDidRun()
    {
        return $this->dateDidRun;
    }

    /**
     * @param \DateTime $dateDidRun
     *
     * @return $this
     */
    public function setDateDidRun(\DateTime $dateDidRun = null)
    {
        $this->setModelField('dateDidRun', $dateDidRun);

        return $this;
    }
}
