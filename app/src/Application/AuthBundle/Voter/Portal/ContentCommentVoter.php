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
class ContentCommentVoter extends AbstractVoter
{
    const COMMENT_ARTICLES = 'COMMENT_ARTICLES';
    const COMMENT_FEEDBACK = 'COMMENT_FEEDBACK';
    const COMMENT_DOWNLOADS = 'COMMENT_DOWNLOADS';
    const COMMENT_NEWS = 'COMMENT_NEWS';

    protected function getSupportedAttributes()
    {
        return array(self::COMMENT_ARTICLES, self::COMMENT_FEEDBACK, self::COMMENT_DOWNLOADS, self::COMMENT_NEWS);
    }

    protected function isGranted($attribute, $object, $user = null)
    {
        // TODO: we do need the permission bag here, but it is not currently used.... this is purely based on settings atm
        if ($this->isLoggedIn($user)) {
            $permission_bag = $this->getPortalPermissionsManager()->getPermissionsBagForPerson($user);
        } else {
            if ($this->getActiveBrandSetting('core.interact_require_login', false)) {
                return false; // if core.interact_require_login and we aren't logged in, then can't comment
            }
            $permission_bag = $this->getPortalPermissionsManager()->getPermissionsBagForGuest();
        }

        switch($attribute) {
            case static::COMMENT_ARTICLES:
                return $permission_bag->get('articles.comment');
            case static::COMMENT_FEEDBACK:
                return $permission_bag->get('feedback.comment');
            case static::COMMENT_DOWNLOADS:
                return $permission_bag->get('downloads.comment');
            case static::COMMENT_NEWS:
                return $permission_bag->get('news.comment');
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
 