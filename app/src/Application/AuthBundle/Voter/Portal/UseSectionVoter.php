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
        if ($this->isLoggedIn($user)) {
            $permissionBag = $this->getPortalPermissionsManager()->getPermissionsBagForPerson($user);
        } else {
            $permissionBag = $this->getPortalPermissionsManager()->getPermissionsBagForGuest();
        }

        switch($attribute) {
            case static::USE_ARTICLES:
                return $this->getActiveBrandSetting('core.apps_kb') && $permissionBag->get('articles.use');
            case static::USE_FEEDBACK:
                return $this->getActiveBrandSetting('core.apps_feedback') && $permissionBag->get('feedback.use');
            case static::USE_CHAT:
                return $this->getActiveBrandSetting('core.apps_chat') && $permissionBag->get('chat.use');
            case static::USE_DOWNLOADS:
                return $this->getActiveBrandSetting('core.apps_downloads') && $permissionBag->get('downloads.use');
            case static::USE_NEWS:
                return $this->getActiveBrandSetting('core.apps_news') && $permissionBag->get('news.use');
            case static::USE_TICKETS:
                return $permissionBag->get('tickets.use');
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
 