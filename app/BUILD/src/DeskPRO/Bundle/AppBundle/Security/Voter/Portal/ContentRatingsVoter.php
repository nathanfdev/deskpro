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
