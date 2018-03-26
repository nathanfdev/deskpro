<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

class SnippetVoter implements PermissionGroupEntityVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return Snippet::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        /** @var Snippet $snippet */
        $snippet = $context->getParent();

        switch ($attribute) {
            case PermissionGroupVoter::CREATE:
                if ($user->hasPerm('agent_snippets.create_snippet')) {
                    return true;
                }
                if ($snippet->isOwnershipGlobal()) {
                    return $user->hasPerm('agent_snippets.create_global_snippet');
                } elseif (count($snippet->getOwnershipTeams()) > 0 && $user->hasPerm('agent_snippets.create_team_snippet')) {
                    $user->loadHelper('AgentTeam');
                    $agentTeams = $user->getAgentTeamIds();
                    /** @var AgentTeam $team */
                    foreach ($snippet->getOwnershipTeams() as $team) {
                        if (!in_array($team->getId(), $agentTeams)) {
                            return false;
                        }
                    }
                }

                return $user->hasPerm('agent_snippets.create_self_snippet');
                break;
            case PermissionGroupVoter::MODIFY:
                if ($user->hasPerm('agent_snippets.edit_by_others')) {
                    return true;
                }
                if ($snippet->getPerson() && $snippet->getPerson()->getId() === $user->getId()) {
                    return true;
                }
                break;
            case PermissionGroupVoter::DELETE:
                if ($user->hasPerm('agent_snippets.delete_by_others')) {
                    return true;
                }
                if ($snippet->getPerson() && $snippet->getPerson()->getId() === $user->getId()) {
                    return true;
                }
                break;
            case PermissionGroupVoter::VIEW:
                if ($snippet->getPerson() && $snippet->getPerson()->getId() === $user->getId()) {
                    return true;
                }
                if ($snippet->isOwnershipGlobal()) {
                    return true;
                }
                $userTeams = $user->getTeamIds();
                foreach ($snippet->getOwnershipTeams() as $team) {
                    /** @var AgentTeam $team */
                    if (in_array($team->getId(), $userTeams)) {
                        return true;
                    }
                }

                return false;
                break;
            case PermissionGroupVoter::VIEW_LIST:
                return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForUser($attribute, PermissionGroupContext $context, Person $user)
    {
        return false;
    }
}
