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
                $departments = $this->departmentDataService->getChatDepartmentsForPerson($user);
                if (!array_intersect($departments, $agentChat->getDepartments())) {
                    return false;
                }

                break;
            case AgentChat::TYPE_AGENT:
                if (!in_array($user, $agentChat->getAgents())) {
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
