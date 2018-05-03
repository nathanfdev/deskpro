<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

use DeskPRO\Bundle\AppBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Concerned only with wether or not a person can use a section / module of the portal.
 */
class ShareContentVoter extends AbstractVoter
{
    const SHARE_ARTICLES = 'SHARE_ARTICLES';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [
            self::SHARE_ARTICLES,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        $user = $token->getUser();

        if ($this->isLoggedIn($user)) {
            $permissionBag = $this->getPortalPermissionsManager()->getPermissionsBagForPerson($user);
        } else {
            $permissionBag = $this->getPortalPermissionsManager()->getPermissionsBagForGuest();
        }

        switch ($attribute) {
            case static::SHARE_ARTICLES:
                // TODO provide some actual rights
                return $this->isLoggedIn($user);
        }

        return false;
    }
}
