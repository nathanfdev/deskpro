<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

use DeskPRO\Bundle\AppBundle\Security\Voter\AbstractVoter;

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

    protected function getSupportedAttributes()
    {
        return array(
            self::SUBSCRIBE_ARTICLE,
            self::SUBSCRIBE_ARTICLE_CATEGORY,
            self::SUBSCRIBE_NEWS,
            self::SUBSCRIBE_NEWS_CATEGORY,
            self::SUBSCRIBE_DOWNLOAD,
            self::SUBSCRIBE_DOWNLOAD_CATEGORY,
            self::SUBSCRIBE_FEEDBACK,
        );
    }

    protected function isGranted($attribute, $object, $user = null)
    {
        if (!$this->isLoggedIn($user)) {
            return false;
        }

        $permission_bag = $this->getPortalPermissionsManager()->getPermissionsBagForPerson($user);

        $permitted = $permission_bag->hasContentCategoryAccess($object);

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
