<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\ClientDevice;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

class ClientDeviceVoter implements PermissionGroupEntityVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return ClientDevice::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        /** @var ClientDevice $clientDevice */
        $clientDevice = $context->getParent();

        switch ($attribute) {
            case PermissionGroupVoter::CREATE:
            case PermissionGroupVoter::VIEW_LIST:
                return true;
            case PermissionGroupVoter::VIEW:
            case PermissionGroupVoter::MODIFY:
            case PermissionGroupVoter::DELETE:
                return $clientDevice->getPerson() === $user;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForUser($attribute, PermissionGroupContext $context, Person $user)
    {
        // users have no devices
        return false;
    }
}
