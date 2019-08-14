<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Approval;

use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ApproverCriteria
 *
 * @ORM\Embeddable
 *
 * @JMS\ExclusionPolicy("all")
 */
class ApproverCriteria
{
    /**
     * @var int[]|null
     *
     * @ORM\Column(name="agents", type="json_array", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("array<integer>")
     */
    private $agents;

    /**
     * @var bool
     *
     * @ORM\Column(name="all_agents", type="boolean", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("boolean")
     */
    private $allAgents = false;

    /**
     * @var int[]|null
     *
     * @ORM\Column(name="users", type="json_array", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("array<integer>")
     */
    private $users;

    /**
     * @var bool
     *
     * @ORM\Column(name="all_users", type="boolean", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("boolean")
     */
    private $allUsers = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="org_managers", type="boolean", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("boolean")
     */
    private $organisationManagers = false;

    /**
     * @var int[]|null
     *
     * @ORM\Column(name="teams", type="json_array", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("array<integer>")
     */
    private $teams;

    /**
     * @var int[]|null
     *
     * @ORM\Column(name="departments", type="json_array", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("array<integer>")
     */
    private $departments;

    /**
     * @return int[]|null
     */
    public function getAgents()
    {
        return $this->agents;
    }

    /**
     * @param int[]|null $agents
     * @return ApproverCriteria
     */
    public function setAgents(array $agents = null)
    {
        $this->agents = $agents;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllAgents()
    {
        return $this->allAgents;
    }

    /**
     * @param bool $allAgents
     * @return ApproverCriteria
     */
    public function setAllAgents($allAgents)
    {
        $this->allAgents = $allAgents;

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param int[]|null $users
     * @return ApproverCriteria
     */
    public function setUsers(array $users = null)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllUsers()
    {
        return $this->allUsers;
    }

    /**
     * @param bool $allUsers
     * @return ApproverCriteria
     */
    public function setAllUsers($allUsers)
    {
        $this->allUsers = $allUsers;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOrganisationManagers()
    {
        return $this->organisationManagers;
    }

    /**
     * @param bool $organisationManagers
     * @return ApproverCriteria
     */
    public function setOrganisationManagers($organisationManagers)
    {
        $this->organisationManagers = $organisationManagers;

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getTeams()
    {
        return $this->teams;
    }

    /**
     * @param int[]|null $teams
     * @return ApproverCriteria
     */
    public function setTeams(array $teams = null)
    {
        $this->teams = $teams;

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getDepartments()
    {
        return $this->departments;
    }

    /**
     * @param int[]|null $departments
     * @return ApproverCriteria
     */
    public function setDepartments(array $departments = null)
    {
        $this->departments = $departments;

        return $this;
    }
}
