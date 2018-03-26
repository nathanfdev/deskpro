<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

use DeskPRO\Bundle\AppBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * An example of a "global" or "app-wide" voter. This votes on USE_{SECTION} attributes.
 *
 * Concerned only with wether or not a person can use a section / module of the desk.
 */
class ContentSubscriptionsVoter extends AbstractVoter
{
    const SUBSCRIBE_ARTICLE           = 'SUBSCRIBE_ARTICLE';
    const SUBSCRIBE_ARTICLE_CATEGORY  = 'SUBSCRIBE_ARTICLE_CATEGORY';
    const SUBSCRIBE_NEWS              = 'SUBSCRIBE_NEWS';
    const SUBSCRIBE_NEWS_CATEGORY     = 'SUBSCRIBE_NEWS_CATEGORY';
    const SUBSCRIBE_DOWNLOAD          = 'SUBSCRIBE_DOWNLOAD';
    const SUBSCRIBE_DOWNLOAD_CATEGORY = 'SUBSCRIBE_DOWNLOAD_CATEGORY';
    const SUBSCRIBE_FEEDBACK          = 'SUBSCRIBE_FEEDBACK';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [
            self::SUBSCRIBE_ARTICLE,
            self::SUBSCRIBE_ARTICLE_CATEGORY,
            self::SUBSCRIBE_NEWS,
            self::SUBSCRIBE_NEWS_CATEGORY,
            self::SUBSCRIBE_DOWNLOAD,
            self::SUBSCRIBE_DOWNLOAD_CATEGORY,
            self::SUBSCRIBE_FEEDBACK,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$this->isLoggedIn($user)) {
            return false;
        }

        $permissionBag = $this->getPortalPermissionsManager()->getPermissionsBagForPerson($user);

        $permitted = $permissionBag->hasContentCategoryAccess($object);

        switch ($attribute) {
            case static::SUBSCRIBE_ARTICLE:
            case static::SUBSCRIBE_ARTICLE_CATEGORY:
                return $this->getActiveBrandSetting('user.kb_subscriptions') && $permitted;
            case static::SUBSCRIBE_NEWS:
            case static::SUBSCRIBE_NEWS_CATEGORY:
                return $this->getActiveBrandSetting('user.news_subscriptions') && $permitted;
            case static::SUBSCRIBE_DOWNLOAD:
            case static::SUBSCRIBE_DOWNLOAD_CATEGORY:
                return $this->getActiveBrandSetting('user.downloads_subscriptions') && $permitted;
            case static::SUBSCRIBE_FEEDBACK:
                return $this->getActiveBrandSetting('user.feedback_subscriptions') && $permitted;
        }

        return false;
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass.
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return true;
    }
}
