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

use Application\DeskPRO\Entity\AgentTeam as Team;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\TaskProject as Project;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="project_members", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="person_unique", columns={"project_id", "person_id"}),
 *      @ORM\UniqueConstraint(name="team_unique", columns={"project_id", "team_id"}),
 *      @ORM\UniqueConstraint(name="department_unique", columns={"project_id", "department_id"})
 *  }
 * )
 * @AppAssert\Task\ProjectMember()
 * @JMS\ExclusionPolicy("all")
 *
 * @UniqueEntity(fields={"project", "person"}, errorPath="person")
 * @UniqueEntity(fields={"project", "team"}, errorPath="team")
 * @UniqueEntity(fields={"project", "department"}, errorPath="department")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class ProjectMember implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

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
    protected $id = null;

    /**
     * Project entity.
     *
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TaskProject", inversedBy="members")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\Project>")
     *
     * @var Project
     */
    protected $project;

    /**
     * A person attached to a project.
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person", inversedBy="project_members")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $person;

    /**
     * An agent team attached to project.
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\AgentTeam", inversedBy="project_members")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\AgentTeam>")
     *
     * @var Team
     */
    protected $team;

    /**
     * A department attached to project.
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Department", inversedBy="project_members")
     * @ORM\JoinColumn(name="department_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\AgentTeam>")
     *
     * @var Department
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
     *
     * @return $this
     */
    public function setProject(Project $project = null)
    {
        $this->setModelField('project', $project);

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
    public function setTeam(Team $team = null)
    {
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
    public function setDepartment(Department $department = null)
    {
        $this->setModelField('department', $department);

        return $this;
    }
}
