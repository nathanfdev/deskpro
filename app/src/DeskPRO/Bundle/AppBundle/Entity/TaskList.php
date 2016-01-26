<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use DeskPRO\Bundle\AppBundle\Entity\TaskProject as Project;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="task_lists")
 */
class TaskList implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id = null;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @var TaskProject
     * @ORM\ManyToOne(targetEntity="TaskProject")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     * @Assert\Valid()
     */
    protected $project;

    /**
     * @var Task[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Task", mappedBy="list")
     */
    protected $tasks;

    /**
     * @var int
     * @Orm\Column(type="integer", nullable=true)
     */
    protected $display_order = 0;

    /**
     * Construct.
     */
    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return Task[]|ArrayCollection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param Project $project
     */
    public function setProject(Project $project)
    {
        $this->project = $project;
        $this->setModelField('project', $project);
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        $this->setModelField('title', $title);
    }

    /**
     * @param Task $task
     */
    public function addTask(Task $task)
    {
        $this->tasks->add($task);
        $this->setModelField('task', $task);
    }

    /**
     * @param Task $task
     */
    public function removeTask(Task $task)
    {
        $this->tasks->remove($task);
    }

    /**
     * @param $displayOrder
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->display_order = $displayOrder;
        $this->setModelField('display_order', $displayOrder);
    }
}
