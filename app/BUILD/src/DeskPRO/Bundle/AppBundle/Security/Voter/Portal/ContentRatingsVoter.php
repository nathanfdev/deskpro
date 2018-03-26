<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

use DeskPRO\Bundle\AppBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Can the user rate content?
 */
class ContentRatingsVoter extends AbstractVoter
{
    const RATE_ARTICLE  = 'RATE_ARTICLE';
    const RATE_FEEDBACK = 'RATE_FEEDBACK';
    const RATE_DOWNLOAD = 'RATE_DOWNLOAD';
    const RATE_NEWS     = 'RATE_NEWS';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        $supported = [self::RATE_ARTICLE, self::RATE_FEEDBACK, self::RATE_DOWNLOAD, self::RATE_NEWS];

        return in_array($attribute, $supported);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        $user = $token->getUser();

        if ($this->isLoggedIn($user)) {
            $permission_bag = $this->getPortalPermissionsManager()->getPermissionsBagForPerson($user);
        } else {
            $permission_bag = $this->getPortalPermissionsManager()->getPermissionsBagForGuest();
        }

        $permitted = $permission_bag->hasContentCategoryAccess($object);

        switch ($attribute) {
            case static::RATE_ARTICLE:
                return $permission_bag->get('articles.rate') && $permitted;
            case static::RATE_FEEDBACK:
                return $permission_bag->get('feedback.rate') && $permitted;
            case static::RATE_DOWNLOAD:
                return $permission_bag->get('downloads.rate') && $permitted;
            case static::RATE_NEWS:
                return $permission_bag->get('news.rate') && $permitted;
        }

        return false;
    }
}
