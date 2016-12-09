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
 *
 * @category Entities
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="task_comments_new")
 * @JMS\ExclusionPolicy("all")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class TaskComment implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     * @JMS\Expose()
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     *
     * @var int
     */
    protected $id = null;

    /**
     * Person created comment.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     * @JMS\Expose()
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull
     * @Assert\Valid()
     *
     * @var Person
     */
    protected $person;

    /**
     * DateTime when comment was created.
     *
     * @JMS\Type("DateTime")
     * @JMS\Expose()
     *
     * @ORM\Column(type="datetime")
     * @Assert\NotNull()
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Comment content.
     *
     * @JMS\Type("string")
     * @JMS\Expose()
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $comment;

    /**
     * Task with which this comment is associated.
     *
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\Task>")
     * @JMS\Expose()
     *
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\Task", inversedBy="comments")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull
     * @Assert\Valid()
     *
     * @var Task
     */
    protected $task;

    /**
     * @JMS\Exclude()
     *
     * @ORM\OneToMany(targetEntity="TaskAttachment", mappedBy="task")
     *
     * @var TaskAttachment[]|ArrayCollection
     */
    protected $attachments;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setModelField('attachments', new ArrayCollection());
        $this->setDateCreated(new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    public function setPerson(Person $person)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return string
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

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
    public function setComment($comment)
    {
        $this->setModelField('comment', $comment);

        return $this;
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @param Task $task
     *
     * @return $this
     */
    public function setTask(Task $task = null)
    {
        $this->setModelField('task', $task);

        return $this;
    }

    /**
     * @return TaskAttachment[]|ArrayCollection
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param TaskAttachment $attachment
     *
     * @return $this
     */
    public function addAttachment(TaskAttachment $attachment)
    {
        $this->attachments->add($attachment);
        $this->setModelField('attachment', $attachment);

        return $this;
    }
}
