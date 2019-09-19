<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Approval;

use Orb\Types\JsonObjectSerializable;

/**
 * Class SelectedApprovers
 *
 * Selected approvers are approvers that are assigned to the approval automatically  when it's created
 *
 * @package DeskPRO\Bundle\AppBundle\Entity\Approval
 */
class SelectedApprovers implements JsonObjectSerializable
{
    /**
     * @var bool
     */
    private $hasTicketUser = false;

    /**
     * @var bool
     */
    private $hasOrganizationManagers = false;

    /**
     * @var bool
     */
    private $hasAllAgents = false;

    /**
     * @var int[]
     */
    private $people = [];

    /**
     * @return bool
     */
    public function hasTicketUser()
    {
        return $this->hasTicketUser;
    }

    /**
     * @param bool $hasTicketUser
     * @return SelectedApprovers
     */
    public function setHasTicketUser($hasTicketUser)
    {
        $this->hasTicketUser = $hasTicketUser;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasOrganizationManagers()
    {
        return $this->hasOrganizationManagers;
    }

    /**
     * @param bool $hasOrganizationManagers
     * @return SelectedApprovers
     */
    public function setHasOrganizationManagers($hasOrganizationManagers)
    {
        $this->hasOrganizationManagers = $hasOrganizationManagers;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAllAgents()
    {
        return $this->hasAllAgents;
    }

    /**
     * @param bool $hasAllAgents
     * @return SelectedApprovers
     */
    public function setHasAllAgents($hasAllAgents)
    {
        $this->hasAllAgents = $hasAllAgents;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getPeople()
    {
        return $this->people;
    }

    /**
     * @param int[] $people
     * @return SelectedApprovers
     */
    public function setPeople(array $people)
    {
        $this->people = $people;

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
        $selection = new self();

        foreach ($data as $property => $value) {
            $selection->{$property} = $value;
        }

        return $selection;
    }
}
