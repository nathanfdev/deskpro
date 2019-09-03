<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Approval;

use JMS\Serializer\Annotation as JMS;
use Orb\Types\JsonObjectSerializable;

/**
 * Class ApproverCriteria
 *
 * @JMS\ExclusionPolicy("all")
 */
class ApproverCriteria implements JsonObjectSerializable
{
    /**
     * @var bool
     *
     * @JMS\Expose
     * @JMS\Type("boolean")
     */
    private $canChooseApprovers = true;

    /**
     * @var int[]
     *
     * @JMS\Expose
     * @JMS\Type("array<integer>")
     */
    private $agents = [];

    /**
     * @var bool
     *
     * @JMS\Expose
     * @JMS\Type("boolean")
     */
    private $allAgents = false;

    /**
     * @var int[]
     *
     * @JMS\Expose
     * @JMS\Type("array<integer>")
     */
    private $users = [];

    /**
     * @var bool
     *
     * @JMS\Expose
     * @JMS\Type("boolean")
     */
    private $allUsers = false;

    /**
     * @var bool
     *
     * @JMS\Expose
     * @JMS\Type("boolean")
     */
    private $organizationManagers = false;

    /**
     * @var int[]
     *
     * @JMS\Expose
     * @JMS\Type("array<integer>")
     */
    private $teams = [];

    /**
     * @var int[]
     *
     * @JMS\Expose
     * @JMS\Type("array<integer>")
     */
    private $departments = [];

    /**
     * @var int|null
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    private $requiredNumberOfApprovers;

    /**
     * @return bool
     */
    public function canChooseApprovers()
    {
        return $this->canChooseApprovers;
    }

    /**
     * @param bool $canChooseApprovers
     * @return ApproverCriteria
     */
    public function setCanChooseApprovers($canChooseApprovers)
    {
        $this->canChooseApprovers = (bool) $canChooseApprovers;

        return $this;
    }


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
        $this->agents = $agents ?: [];

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
        $this->allAgents = (bool) $allAgents;

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
        $this->users = $users ?: [];

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
        $this->allUsers = (bool) $allUsers;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOrganizationManagers()
    {
        return $this->organizationManagers;
    }

    /**
     * @param bool $organizationManagers
     * @return ApproverCriteria
     */
    public function setOrganizationManagers($organizationManagers)
    {
        $this->organizationManagers = (bool) $organizationManagers;

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
        $this->teams = $teams ?: [];

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
        $this->departments = $departments ?: [];

        return $this;
    }

    /**
     * Return TRUE if this criteria absolutely defines and agents or user IDs
     *
     * @return bool
     */
    public function hasPeople()
    {
        return (!empty($this->getAgents()) || !empty($this->getUsers()));
    }

    /**
     * @return int|null
     */
    public function getRequiredNumberOfApprovers()
    {
        return $this->requiredNumberOfApprovers;
    }

    /**
     * @param int|null $requiredNumberOfApprovers
     * @return ApproverCriteria
     */
    public function setRequiredNumberOfApprovers($requiredNumberOfApprovers)
    {
        $this->requiredNumberOfApprovers = $requiredNumberOfApprovers;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function serializeJsonArray()
    {
        return get_object_vars($this);
    }

    /**
     * {@inheritDoc}
     */
    public static function unserializeJsonArray(array $data)
    {
        $criteria = new self();

        foreach ($data as $property => $value) {
            $criteria->{$property} = $value;
        }

        return $criteria;
    }
}
