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

use Application\DeskPRO\Entity\AgentTeam as Team;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\TaskProject as Project;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="project_members", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="person_unique", columns={"project_id", "person_id"}),
 *      @ORM\UniqueConstraint(name="team_unique", columns={"project_id", "team_id"}),
 *      @ORM\UniqueConstraint(name="department_unique", columns={"project_id", "department_id"})
 *  }
 * )
 *
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route("api_project_members_get", parameters={"id" = "expr(object.getId())"})
 * )
 */
class ProjectMember implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id = null;

    /**
     * @var Project
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TaskProject")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     * @Assert\Valid()
     */
    protected $project;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $person;

    /**
     * @var Team
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\AgentTeam")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $team;

    /**
     * @var Department
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Department")
     * @ORM\JoinColumn(name="department_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
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
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param Project $project
     */
    public function setProject(Project $project)
    {
        $this->setModelField('project', $project);
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
     */
    public function setPerson(Person $person = null)
    {
        $this->team       = null;
        $this->department = null;
        $this->setModelField('person', $person);
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
     */
    public function setTeam(Team $team = null)
    {
        $this->department = null;
        $this->person     = null;
        $this->setModelField('team', $team);
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
     */
    public function setDepartment(Department $department = null)
    {
        $this->team   = null;
        $this->person = null;
        $this->setModelField('department', $department);
    }
}
