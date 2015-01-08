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
class ContentSubscriptionsVoter extends AbstractVoter
{
    const SUBSCRIBE_ARTICLES = 'SUBSCRIBE_ARTICLES';

    protected function getSupportedAttributes()
    {
        return array(self::SUBSCRIBE_ARTICLES);
    }

    protected function isGranted($attribute, $object, $user = null)
    {
        if (!$this->isLoggedIn($user)) {
            return false;
        }

        // not sure if there are individual permissions on this, not seeing them in old portal code, but the bag is available...
        $permissionBag = $this->getPortalPermissionsManager()->getPermissionsBagForPerson($user);
        $brand_settings = $this->getActiveBrandContainer()->getSettings();

        switch($attribute) {
            case static::SUBSCRIBE_ARTICLES:
                return $brand_settings->get('user.kb_subscriptions');
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
 