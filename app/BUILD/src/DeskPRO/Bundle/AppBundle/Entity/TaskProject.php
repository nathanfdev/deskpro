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

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="task_projects")
 */
class TaskProject implements EntityInterface, NotifyPropertyChanged
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
     * @var Task[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Task", mappedBy="project")
     */
    protected $tasks;

    /**
     * @var ProjectMember[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="ProjectMember", mappedBy="project", cascade={"persist"}, orphanRemoval=true)
     */
    protected $members;

    /**
     * @var TaskList[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="TaskList", mappedBy="project", cascade={"persist"})
     */
    protected $lists;

    /**
     * Construct.
     */
    public function __construct()
    {
        $this->tasks   = new ArrayCollection();
        $this->members = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return ProjectMember[]|ArrayCollection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @return TaskList[]|ArrayCollection
     */
    public function getLists()
    {
        return $this->lists;
    }

    /**
     * @return Department[]|ArrayCollection
     */
    public function getDepartments()
    {
        $departments = [];
        if (!empty($this->members)) {
            foreach ($this->members as $member) {
                if (!empty($member->getDepartment())) {
                    $departments[] = $member->getDepartment();
                }
            }
        }

        return new ArrayCollection($departments);
    }

    /**
     * @return AgentTeam[]|ArrayCollection
     */
    public function getTeams()
    {
        $teams = [];
        if (!empty($this->members)) {
            foreach ($this->members as $member) {
                if (!empty($member->getTeam())) {
                    $teams[] = $member->getTeam();
                }
            }
        }

        return new ArrayCollection($teams);
    }

    /**
     * @return Person[]|ArrayCollection
     */
    public function getAgents()
    {
        $people = [];
        if (!empty($this->members)) {
            foreach ($this->members as $member) {
                if (!empty($member->getPerson())) {
                    $people[] = $member->getPerson();
                }
            }
        }

        return new ArrayCollection($people);
    }

    /**
     * @param Department $department
     */
    public function addDepartment(Department $department)
    {
        $member = new ProjectMember();
        $member->setDepartment($department);
        $member->setProject($this);
        $this->addMember($member);
    }

    /**
     * @param Department $department
     */
    public function removeDepartment(Department $department)
    {
        if (!empty($this->members)) {
            foreach ($this->members as $member) {
                if ($member->getDepartment() === $department) {
                    $this->members->removeElement($member);
                }
            }
        }
    }

    /**
     * @param AgentTeam $agentTeam
     */
    public function addTeam(AgentTeam $agentTeam)
    {
        $member = new ProjectMember();
        $member->setTeam($agentTeam);
        $member->setProject($this);
        $this->addMember($member);
    }

    /**
     * @param AgentTeam $agentTeam
     */
    public function removeTeam(AgentTeam $agentTeam)
    {
        if (!empty($this->members)) {
            foreach ($this->members as $member) {
                if ($member->getTeam() === $agentTeam) {
                    $this->members->removeElement($member);
                }
            }
        }
    }

    /**
     * @param Person $person
     */
    public function addAgent(Person $person)
    {
        $member = new ProjectMember();
        $member->setPerson($person);
        $member->setProject($this);
        $this->addMember($member);
    }

    /**
     * @param Person $person
     */
    public function removeAgent(Person $person)
    {
        if (!empty($this->members)) {
            foreach ($this->members as $member) {
                if ($member->getPerson() === $person) {
                    $this->members->removeElement($member);
                }
            }
        }
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
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
     * @param ProjectMember $member
     */
    public function addMember(ProjectMember $member)
    {
        $this->members->add($member);
        $this->setModelField('member', $member);
    }

    /**
     * @param ProjectMember $member
     */
    public function removeMember(ProjectMember $member)
    {
        $this->members->removeElement($member);
    }

    /**
     * @param TaskList $list
     */
    public function addList(TaskList $list)
    {
        $this->lists->add($list);
        $this->setModelField('list', $list);
    }

    /**
     * @param TaskList $list
     */
    public function removeList(TaskList $list)
    {
        $this->lists->removeElement($list);
    }
}
