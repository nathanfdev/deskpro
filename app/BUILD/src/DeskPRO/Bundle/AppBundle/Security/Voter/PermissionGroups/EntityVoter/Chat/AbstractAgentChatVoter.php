<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\Chat;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\DataService\DepartmentDataService;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\PermissionGroupEntityVoterInterface;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;

/**
 * Class AbstractAgentChatVoter.
 */
abstract class AbstractAgentChatVoter implements PermissionGroupEntityVoterInterface
{
    /**
     * @var DepartmentDataService
     */
    protected $departmentDataService;

    /**
     * Constructor.
     *
     * @param DepartmentDataService $departmentDataService
     */
    public function __construct(DepartmentDataService $departmentDataService)
    {
        $this->departmentDataService = $departmentDataService;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForUser($attribute, PermissionGroupContext $context, Person $user)
    {
        return false;
    }

    /**
     * @param AgentChat $agentChat
     * @param Person    $user
     *
     * @return bool
     */
    protected function isPersonInvolved(AgentChat $agentChat, Person $user)
    {
        switch ($agentChat->getType()) {
            case AgentChat::TYPE_DEPARTMENT:
                $departments = $this->departmentDataService->getDepartmentsForPerson($user);
                if (!array_intersect($departments, $agentChat->getDepartments())) {
                    return false;
                }

                break;
            case AgentChat::TYPE_AGENT:
                if (!in_array($user, $agentChat->getAgents(), true)) {
                    return false;
                }

                break;
            case AgentChat::TYPE_TEAM:
                if (!array_intersect($user->getTeams()->toArray(), $agentChat->getAgentTeams())) {
                    return false;
                }

                break;
        }

        return true;
    }
}
