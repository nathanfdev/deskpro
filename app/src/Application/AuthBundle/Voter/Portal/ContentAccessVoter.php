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
 * Make access decisions about content entities. IE Can the user download a Download? Can user view an Article?
 */
class ContentAccessVoter extends AbstractVoter
{
    const VIEW_DOWNLOAD = 'VIEW_DOWNLOAD';
    const VIEW_DOWNLOAD_CATEGORY = 'VIEW_DOWNLOAD_CATEGORY';
    const DOWNLOAD_DOWNLOAD = 'DOWNLOAD_DOWNLOAD';

    const VIEW_ARTICLE = 'VIEW_ARTICLE';
    const VIEW_ARTICLE_CATEGORY = 'VIEW_ARTICLE_CATEGORY';

    const VIEW_NEWS = 'VIEW_NEWS';
    const VIEW_NEWS_CATEGORY = 'VIEW_NEWS_CATEGORY';

    const VIEW_FEEDBACK = 'VIEW_FEEDBACK';

    protected function getSupportedAttributes()
    {
        return array(
            self::DOWNLOAD_DOWNLOAD,
            self::VIEW_DOWNLOAD,
            self::VIEW_DOWNLOAD_CATEGORY,
            self::VIEW_ARTICLE,
            self::VIEW_ARTICLE_CATEGORY,
            self::VIEW_NEWS,
            self::VIEW_NEWS_CATEGORY,
            self::VIEW_FEEDBACK
        );
    }

    protected function isGranted($attribute, $object, $user = null)
    {
        // $object is the content entity here (or content category) ie Article, ArticleCategory, etc.
        switch($attribute) {
            case static::DOWNLOAD_DOWNLOAD:
            case static::VIEW_DOWNLOAD:
            case static::VIEW_DOWNLOAD_CATEGORY:
            case static::VIEW_ARTICLE:
            case static::VIEW_ARTICLE_CATEGORY:
            case static::VIEW_NEWS:
            case static::VIEW_NEWS_CATEGORY:
            case static::VIEW_FEEDBACK:
                // TODO: each of these need some security logic, $object is the entity bing asserted upon
                return true;
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
 