<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Approval;

use JMS\Serializer\Annotation as JMS;
use Orb\Types\JsonObjectSerializable;

/**
 * Class ApproverCriteria
 *
 * Forms the basis of which users are available to select in the agent UI
 *
 * @JMS\ExclusionPolicy("all")
 */
class ApproverSelectionCriteria implements JsonObjectSerializable
{
    /**
     * @var bool
     *
     * @JMS\Expose
     * @JMS\Type("boolean")
     */
    private $canSelectTicketUser = false;

    /**
     * @var bool
     *
     * @JMS\Expose
     * @JMS\Type("boolean")
     */
    private $canSelectOrganizationManagers = false;

    /**
     * @var bool
     *
     * @JMS\Expose
     * @JMS\Type("boolean")
     */
    private $canSelectFromAllAgents = false;

    /**
     * @var int[]
     *
     * @JMS\Expose
     * @JMS\Type("array<integer>")
     */
    private $selectFromPeople = [];

    /**
     * @var int
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    private $minNumberOfApprovers;

    /**
     * @return bool
     */
    public function isCanSelectTicketUser()
    {
        return $this->canSelectTicketUser;
    }

    /**
     * @param bool $canSelectTicketUser
     * @return ApproverSelectionCriteria
     */
    public function setCanSelectTicketUser($canSelectTicketUser)
    {
        $this->canSelectTicketUser = $canSelectTicketUser;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCanSelectOrganizationManagers()
    {
        return $this->canSelectOrganizationManagers;
    }

    /**
     * @param bool $canSelectOrganizationManagers
     * @return ApproverSelectionCriteria
     */
    public function setCanSelectOrganizationManagers($canSelectOrganizationManagers)
    {
        $this->canSelectOrganizationManagers = $canSelectOrganizationManagers;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCanSelectFromAllAgents()
    {
        return $this->canSelectFromAllAgents;
    }

    /**
     * @param bool $canSelectFromAllAgents
     * @return ApproverSelectionCriteria
     */
    public function setCanSelectFromAllAgents($canSelectFromAllAgents)
    {
        $this->canSelectFromAllAgents = $canSelectFromAllAgents;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getSelectFromPeople()
    {
        return $this->selectFromPeople;
    }

    /**
     * @param int[] $selectFromPeople
     * @return ApproverSelectionCriteria
     */
    public function setSelectFromPeople(array $selectFromPeople)
    {
        $this->selectFromPeople = $selectFromPeople;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinNumberOfApprovers()
    {
        return $this->minNumberOfApprovers;
    }

    /**
     * @param int $minNumberOfApprovers
     * @return ApproverSelectionCriteria
     */
    public function setMinNumberOfApprovers($minNumberOfApprovers)
    {
        $this->minNumberOfApprovers = $minNumberOfApprovers;

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
