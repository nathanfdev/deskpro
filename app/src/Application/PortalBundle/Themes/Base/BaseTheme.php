<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Themes\Base;

use Application\PortalBundle\Theme\AbstractTheme;
use Application\PortalBundle\Theme\Tag;

class BaseTheme extends AbstractTheme
{
    public function getTags()
    {
        return array(
            new Tag('alerts', 'Theme:Portal:alerts'),
            new Tag('top_bar', 'Theme:Portal:topBar'),
            new Tag('top_search', 'Theme:Portal:topSearch'),
            new Tag('top_tabs', 'Theme:Portal:topTabs'),
            new Tag('get_in_touch', 'Theme:Portal:getInTouch'),
            new Tag('sidebar', 'Theme:Portal:sidebar'),
            new Tag('user_sidebar', 'Theme:Portal:userSidebar'),
            new Tag('login_sidebar', 'Theme:Portal:loginSidebar'),

            new Tag('kb_articles_forcat', 'Theme:Articles:list', array('style' => 'forcat')),
            new Tag('kb_articles_small', 'Theme:Articles:list', array('style' => 'small')),
            new Tag('kb_articles_xsmall', 'Theme:Articles:list', array('style' => 'xsmall')),

            new Tag('kb_cats_home', 'Theme:Articles:categories', array('style' => 'home')),
            new Tag('kb_cats_summary', 'Theme:Articles:categories', array('style' => 'summary')),
            new Tag('kb_cats_small', 'Theme:Articles:categories', array('style' => 'small')),
            new Tag('kb_cats_expander', 'Theme:Articles:categories', array('style' => 'expander')),

            new Tag('news_list', 'Theme:News:list', array('style' => 'posts')),
            new Tag('news_list_small', 'Theme:News:list', array('style' => 'small')),
            new Tag('news_cats_small', 'Theme:News:cats', array('style' => 'small')),
            new Tag('news_cats_dropdown', 'Theme:News:cats', array('style' => 'dropdown')),

            new Tag('downloads_list_small', 'Theme:Downloads:list', array('style' => 'small')),
            new Tag('downloads_cats_small', 'Theme:Downloads:cats', array('style' => 'small')),

            new Tag('feedback_list_small', 'Theme:Feedback:list', array('style' => 'small')),
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'base';
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Base';
    }


    /**
     * {@inheritdoc}
     */
    public function getBaseTemplateDir()
    {
        return __DIR__ . '/Resources/views';
    }

    /**
     * @return string|null base namespace of theme, like: Application\PortalBundle\Themes\Standard
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }
}
