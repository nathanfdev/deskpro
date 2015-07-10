<?php

/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @category Entities
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DeskPRO\Bundle\AppBundle\Doctrine\NotifyPropertyChangeEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Application\DeskPRO\Entity\Person;

/**
 * @ORM\Entity
 * @ORM\Table(name="task_subtask")
 */
class TaskSubtask extends NotifyPropertyChangeEntity
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @Assert\NotNull()
     */
    protected $id = null;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $done = false;

    /**
     * @var Task
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     */
    protected $task;

    /**
     * @var \DateTime
     * @Orm\Column(type="datetime")
     */
    protected $date_created;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     */
    protected $creator;

    /**
     * @var int
     * @Orm\Column(type="integer")
     */
    protected $order = 0;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $date_completed;

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
     * @return Person
     */
    public function getPerson()
    {
        return $this->creator;
    }

    /**
     * @param Person $creator
     */
    public function setPerson(Person $creator)
    {
        $this->creator = $creator;
        $this->setModelField('creator', $creator);
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
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return boolean
     */
    public function isDone()
    {
        return $this->done;
    }

    /**
     * @param boolean $done
     */
    public function setDone($done)
    {
        $this->done = $done;
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
     */
    public function setTask($task)
    {
        $this->task = $task;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
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
     */
    public function setDateCompleted($date_completed)
    {
        $this->date_completed = $date_completed;
    }
}
