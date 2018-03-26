<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\Chat;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;

/**
 * Class AgentChatVoter.
 */
class AgentChatVoter extends AbstractAgentChatVoter
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return AgentChat::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        /** @var AgentChat $agentChat */
        $agentChat = $context->getParent();
        if ($agentChat) {
            return $this->isPersonInvolved($agentChat, $user);
        }

        return true;
    }
}
