<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Table that implements Many2Many connection between Tickets and Feedback.
 *
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\TicketToFeedbackRepository")
 * @ORM\Table(name="tickets_to_feedback", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ticket_to_feedback_unique", columns={"ticket_id", "feedback_id"})
 * })
 *
 * @JMS\ExclusionPolicy("ALL")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class TicketToFeedback implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * The unique filter ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Ticket")
     * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @var Ticket
     */
    protected $ticket;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Feedback")
     * @ORM\JoinColumn(name="feedback_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @var Feedback
     */
    protected $feedback;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     *
     * @var Person
     */
    protected $person;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
    }

    /**
     * @return int|null
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
     * @return Feedback
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * @param Feedback $feedback
     *
     * @return $this
     */
    public function setFeedback(Feedback $feedback = null)
    {
        $this->setModelField('feedback', $feedback);

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
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param \DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $date_created)
    {
        $this->setModelField('date_created', $date_created);

        return $this;
    }
}
