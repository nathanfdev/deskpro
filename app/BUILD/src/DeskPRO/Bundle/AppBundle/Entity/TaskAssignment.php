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

use Application\DeskPRO\Entity\AgentTeam as Team;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="task_assignments")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class TaskAssignment implements EntityInterface, NotifyPropertyChanged
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
     * @var Task
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\Task", inversedBy="assigned")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     * @Assert\Valid()
     */
    protected $task;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person", inversedBy="assigned_tasks")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $person;

    /**
     * @var Team
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\AgentTeam")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $team;

    /**
     * @var Department
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Department")
     * @ORM\JoinColumn(name="department_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $department;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
        $this->setModelField('team', null);
        $this->setModelField('department', null);
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param Team $team
     *
     * @return $this
     */
    public function setTeam(Team $team)
    {
        $this->setModelField('department', null);
        $this->setModelField('person', null);
        $this->setModelField('team', $team);

        return $this;
    }

    /**
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function setDepartment(Department $department)
    {
        $this->setModelField('team', null);
        $this->setModelField('person', null);
        $this->setModelField('department', $department);

        return $this;
    }
}
