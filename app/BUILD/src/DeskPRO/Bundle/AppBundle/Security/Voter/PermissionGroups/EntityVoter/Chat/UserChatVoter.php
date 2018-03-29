<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\Chat;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PermissionChecker\ChatChecker;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\PermissionGroupEntityVoterInterface;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

/**
 * Class UserChatVoter.
 */
class UserChatVoter implements PermissionGroupEntityVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return ChatConversation::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        if (!$user->hasPerm('agent_chat.use')) {
            return false;
        }

        /** @var ChatConversation $chat */
        $chat = $context->getParent();

        switch ($attribute) {
            case PermissionGroupVoter::VIEW:
                return $this->getChatChecker($user)->canView($chat);
            case PermissionGroupVoter::DELETE:
                return $this->getChatChecker($user)->canDelete($chat);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForUser($attribute, PermissionGroupContext $context, Person $user)
    {
        // no access for now
        return false;
    }

    /**
     * @param Person $user
     *
     * @return ChatChecker
     */
    protected function getChatChecker(Person $user)
    {
        return $user->PermissionsManager->ChatChecker;
    }
}
