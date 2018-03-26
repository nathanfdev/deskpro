<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Themes\Standard;

use DeskPRO\Bundle\PortalBundle\Theme\AbstractTheme;
use DeskPRO\Bundle\PortalBundle\Themes\Base\BaseTheme;

class StandardTheme extends AbstractTheme
{
    const THEME_ID = 'standard';

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
        return 'Standard';
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
