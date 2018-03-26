<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Make access decisions about content entities. IE Can the user download a Download? Can user view an Article?
 */
class ContentAccessVoter extends AbstractVoter
{
    const VIEW_DOWNLOAD          = 'VIEW_DOWNLOAD';
    const VIEW_DOWNLOAD_CATEGORY = 'VIEW_DOWNLOAD_CATEGORY';
    const DOWNLOAD_DOWNLOAD      = 'DOWNLOAD_DOWNLOAD';

    const VIEW_ARTICLE          = 'VIEW_ARTICLE';
    const VIEW_ARTICLE_CATEGORY = 'VIEW_ARTICLE_CATEGORY';

    const VIEW_NEWS          = 'VIEW_NEWS';
    const VIEW_NEWS_CATEGORY = 'VIEW_NEWS_CATEGORY';

    const VIEW_FEEDBACK = 'VIEW_FEEDBACK';

    const VIEW_GUIDE = 'VIEW_GUIDE';
    const VIEW_TOPIC = 'VIEW_TOPIC';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        $supported = [
            self::DOWNLOAD_DOWNLOAD,
            self::VIEW_DOWNLOAD,
            self::VIEW_DOWNLOAD_CATEGORY,
            self::VIEW_ARTICLE,
            self::VIEW_ARTICLE_CATEGORY,
            self::VIEW_NEWS,
            self::VIEW_NEWS_CATEGORY,
            self::VIEW_FEEDBACK,
            self::VIEW_GUIDE,
            self::VIEW_TOPIC,
        ];

        return in_array($attribute, $supported);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        $user = $token->getUser();

        // $object is the content entity here (or content category) ie Article, ArticleCategory, etc.
        $permissionsBag = $this->getPermissionsBag($user);

        switch ($attribute) {
            case static::VIEW_FEEDBACK:
            case static::DOWNLOAD_DOWNLOAD:
            case static::VIEW_DOWNLOAD:
            case static::VIEW_ARTICLE:
            case static::VIEW_NEWS:
            case static::VIEW_TOPIC:
                /* @var \Application\DeskPRO\Entity\ContentAbstract $object */
                if ($permissionsBag->hasContentCategoryAccess($object) && $object->isPublic()) {
                    return true;
                }
                // agents can still see unpublished stuff
                if ($user && $user instanceof Person && $user->is_agent
                    && ($object->getStatusCode() === 'hidden.unpublished' || $object->getStatusCode() === 'hidden.draft')) {
                    return true;
                }

                break;
            case static::VIEW_NEWS_CATEGORY:
            case static::VIEW_DOWNLOAD_CATEGORY:
            case static::VIEW_ARTICLE_CATEGORY:
            case static::VIEW_GUIDE:
                if ($permissionsBag->hasContentCategoryAccess($object)) {
                    return true;
                }
                break;
        }

        return false;
    }
}
