<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Can the user view the edit profile page?
 */
class ProfileVoter extends AbstractVoter
{
    const EDIT_PROFILE = 'EDIT_PROFILE';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Person && in_array($attribute, [
            self::EDIT_PROFILE,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        $user = $token->getUser();

        // any person that is logged in can edit their own profile
        return $this->isLoggedIn($user) && $object->getId() === $user->getId();
    }
}
