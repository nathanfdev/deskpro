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

namespace DeskPRO\Bundle\PortalBundle\Themes\Sidebar;

use DeskPRO\Bundle\PortalBundle\Theme\AbstractTheme;
use DeskPRO\Bundle\PortalBundle\Themes\Base\BaseTheme;

class SidebarTheme extends AbstractTheme
{
    const THEME_ID = 'sidebar';

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return self::THEME_ID;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentId()
    {
        return BaseTheme::THEME_ID;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Sidebar';
    }

    public static function getHardCodedTags()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTemplateDir()
    {
        return __DIR__.'/Resources/views';
    }

    /**
     * @return string absolute path to the root of this theme's controllers
     */
    public function getBaseControllerDir()
    {
        return __DIR__.'/Controller';
    }

    /**
     * @return string|null base namespace of controllers, like: DeskPRO\Bundle\PortalBundle\Themes\Standard
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }
}
