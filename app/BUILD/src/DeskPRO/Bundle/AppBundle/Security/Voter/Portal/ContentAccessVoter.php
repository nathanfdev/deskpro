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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

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

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        $supported = array(
            self::DOWNLOAD_DOWNLOAD,
            self::VIEW_DOWNLOAD,
            self::VIEW_DOWNLOAD_CATEGORY,
            self::VIEW_ARTICLE,
            self::VIEW_ARTICLE_CATEGORY,
            self::VIEW_NEWS,
            self::VIEW_NEWS_CATEGORY,
            self::VIEW_FEEDBACK,
        );

        return in_array($attribute, $supported);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        $user = $token->getUser();

        // $object is the content entity here (or content category) ie Article, ArticleCategory, etc.
        $permissions_bag = $this->getPermissionsBag($user);

        switch ($attribute) {
            case static::VIEW_FEEDBACK:
                /* @var \Application\DeskPRO\Entity\Feedback $object */
                return $permissions_bag->hasContentCategoryAccess($object) && $object->isPublic();
            case static::DOWNLOAD_DOWNLOAD:
                return $permissions_bag->hasContentCategoryAccess($object);
            case static::VIEW_DOWNLOAD:
                /* @var \Application\DeskPRO\Entity\Download $object */
                return $permissions_bag->hasContentCategoryAccess($object) && $object->isPublic();
            case static::VIEW_DOWNLOAD_CATEGORY:
                return $permissions_bag->hasContentCategoryAccess($object);
            case static::VIEW_ARTICLE:
                /* @var \Application\DeskPRO\Entity\Article $object */
                return $permissions_bag->hasContentCategoryAccess($object) && $object->isPublic();
            case static::VIEW_ARTICLE_CATEGORY:
                return $permissions_bag->hasContentCategoryAccess($object);
            case static::VIEW_NEWS:
                /* @var \Application\DeskPRO\Entity\News $object */
                return $permissions_bag->hasContentCategoryAccess($object) && $object->isPublic();
            case static::VIEW_NEWS_CATEGORY:
                return $permissions_bag->hasContentCategoryAccess($object);
        }

        return false;
    }
}
