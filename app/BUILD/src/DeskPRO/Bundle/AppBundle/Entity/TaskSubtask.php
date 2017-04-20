<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="task_subtask")
 * @JMS\ExclusionPolicy("all")
 */
class TaskSubtask implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     *
     * @var int
     */
    protected $id = null;

    /**
     * The title of the subtask.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $title;

    /**
     * Is subtask is done?
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_done = false;

    /**
     * Task entity with which this subtask is associated.
     *
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\Task", inversedBy="subtasks")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @Assert\NotNull()
     * @Assert\Valid()
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\Entity\TaskSubtask>")
     *
     * @var Task
     */
    protected $task;

    /**
     * Date when subtask was originally created.
     *
     * @Orm\Column(type="datetime", nullable=true)
     *
     * @Assert\NotNull()
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * The person who created the subtask.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @Assert\NotNull()
     *
     * @var Person
     */
    protected $creator;

    /**
     * Display order of this task in list of subtasks.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    protected $display_order = 0;

    /**
     * Date when this subtask was completed.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $date_completed = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
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
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param Person $creator
     *
     * @return $this
     */
    public function setCreator(Person $creator)
    {
        $this->setModelField('creator', $creator);

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }

    /**
     * @return bool
     */
    public function isDone()
    {
        return $this->is_done;
    }

    /**
     * @param bool $done
     *
     * @return $this
     */
    public function setIsDone($done)
    {
        $this->setModelField('is_done', $done);
        $this->setDateCompleted(new \DateTime());

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
    public function setTask($task)
    {
        $this->setModelField('task', $task);

        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param int $display_order
     *
     * @return $this
     */
    public function setDisplayOrder($display_order)
    {
        $this->setModelField('display_order', $display_order);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCompleted()
    {
        return $this->date_completed;
    }

    /**
     * @param \DateTime $date_completed
     *
     * @return $this
     */
    public function setDateCompleted($date_completed)
    {
        $this->setModelField('date_completed', $date_completed);

        return $this;
    }
}
