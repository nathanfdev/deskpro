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
 * An example of a "global" or "app-wide" voter. This votes on USE_{SECTION} attributes.
 *
 * Concerned only with wether or not a person can use a section / module of the desk.
 */
class UseSectionVoter extends AbstractVoter
{
    const USE_ARTICLES = 'USE_ARTICLES';
    const USE_FEEDBACK = 'USE_FEEDBACK';
    const USE_CHAT = 'USE_CHAT';
    const USE_DOWNLOADS = 'USE_DOWNLOADS';
    const USE_NEWS = 'USE_NEWS';
    const USE_TICKETS = 'USE_TICKETS';

    protected function getSupportedAttributes()
    {
        return array(self::USE_ARTICLES, self::USE_FEEDBACK, self::USE_CHAT, self::USE_DOWNLOADS, self::USE_NEWS, self::USE_TICKETS);
    }

    protected function isGranted($attribute, $object, $user = null)
    {
        // TODO: we do need the permission bag here, but it is not currently used.... this is purely based on settings atm
        // if use section does not depend on user permissions, we can remove the section about perm bags below
        if ($this->isLoggedIn($user)) {
            $permissionBag = $this->getPortalPermissionsManager()->getPermissionsBagForPerson($user);
        } else {
            $permissionBag = $this->getPortalPermissionsManager()->getPermissionsBagForGuest();
        }

        $brand_settings = $this->getActiveBrandContainer()->getSettings();

        switch($attribute) {
            case static::USE_ARTICLES:
                return $brand_settings->get('core.apps_kb');
            case static::USE_FEEDBACK:
                return $brand_settings->get('core.apps_feedback');
            case static::USE_CHAT:
                return $brand_settings->get('core.apps_chat');
            case static::USE_DOWNLOADS:
                return $brand_settings->get('core.apps_downloads');
            case static::USE_NEWS:
                return $brand_settings->get('core.apps_news');
            case static::USE_TICKETS:
                return true; // all of these settings seems to have changed names recently, this update reflects those changes as best as I can see.
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
 