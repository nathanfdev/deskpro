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

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PermissionChecker\ChatChecker;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

/**
 * Class ChatVoter.
 */
class ChatVoter implements PermissionGroupEntityVoterInterface
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
