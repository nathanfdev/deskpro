<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\AuthBundle\Voter\Portal;

use Application\AuthBundle\Voter\AbstractVoter;

/**
 * Can the user rate content?
 */
class ContentRatingsVoter extends AbstractVoter
{
    const RATE_ARTICLES = 'RATE_ARTICLES';
    const RATE_FEEDBACK = 'RATE_FEEDBACK';
    const RATE_DOWNLOADS = 'RATE_DOWNLOADS';
    const RATE_NEWS = 'RATE_NEWS';

    protected function getSupportedAttributes()
    {
        return array(self::RATE_ARTICLES, self::RATE_FEEDBACK, self::RATE_DOWNLOADS, self::RATE_NEWS);
    }

    protected function isGranted($attribute, $object, $user = null)
    {
        if ($this->isLoggedIn($user)) {
            $permission_bag = $this->getPortalPermissionsManager()->getPermissionsBagForPerson($user);
            if ($this->getActiveBrandSetting('core.interact_require_login', false)) {
                return false; // if core.interact_require_login and we aren't logged in, then can't comment
            }
        } else {
            $permission_bag = $this->getPortalPermissionsManager()->getPermissionsBagForGuest();
        }

        switch($attribute) {
            case static::RATE_ARTICLES:
                return $permission_bag->get('articles.rate');
            case static::RATE_FEEDBACK:
                return $permission_bag->get('feedback.rate');
            case static::RATE_DOWNLOADS:
                return $permission_bag->get('downloads.rate');
            case static::RATE_NEWS:
                return $permission_bag->get('news.rate');
        }

        return false;
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass
     *
     * @return array    an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return true;
    }
}
 