<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

use DeskPRO\Bundle\AppBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Can the user make comments?
 */
class ContentCommentVoter extends AbstractVoter
{
    /** everyone is allowed to VIEW comments, but can they submit a comment? */
    const COMMENT_ARTICLE  = 'COMMENT_ARTICLE';
    const COMMENT_FEEDBACK = 'COMMENT_FEEDBACK';
    const COMMENT_DOWNLOAD = 'COMMENT_DOWNLOAD';
    const COMMENT_NEWS     = 'COMMENT_NEWS';
    const COMMENT_TOPIC    = 'COMMENT_TOPIC';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        $supported = [
            self::COMMENT_ARTICLE,
            self::COMMENT_FEEDBACK,
            self::COMMENT_DOWNLOAD,
            self::COMMENT_NEWS,
            self::COMMENT_TOPIC,
        ];

        return in_array($attribute, $supported);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        $user = $token->getUser();

        if ($this->isLoggedIn($user)) {
            $permissionsBag = $this->getPortalPermissionsManager()->getPermissionsBagForPerson($user);
        } else {
            $permissionsBag = $this->getPortalPermissionsManager()->getPermissionsBagForGuest();
        }

        if (!$this->getActiveBrandSetting('user.publish_comments', false)) {
            return false; // if this setting is off, never allow comments
        }

        $permitted = $permissionsBag->hasContentCategoryAccess($object);

        switch ($attribute) {
            case static::COMMENT_ARTICLE:
                return $permissionsBag->get('articles.comment') && $permitted;
            case static::COMMENT_FEEDBACK:
                return $permissionsBag->get('feedback.comment') && $permitted;
            case static::COMMENT_DOWNLOAD:
                return $permissionsBag->get('downloads.comment') && $permitted;
            case static::COMMENT_NEWS:
                return $permissionsBag->get('news.comment') && $permitted;
            case static::COMMENT_TOPIC:
                return $permissionsBag->get('guides.comment') && $permitted;
        }

        return false;
    }
}
