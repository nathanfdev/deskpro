<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Themes\Base;

use DeskPRO\Bundle\PortalBundle\Theme\AbstractTheme;

class BaseTheme extends AbstractTheme
{
    const THEME_ID = 'base';

    public static function getHardCodedTags()
    {
        return [];
    }

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
        return;
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
        return __DIR__.'/Resources/views';
    }

    /**
     * @return string|null base namespace of theme, like: DeskPRO\Bundle\PortalBundle\Themes\Standard
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * @return string absolute path to the root of this theme's controllers
     */
    public function getBaseControllerDir()
    {
        return __DIR__.'/Controller';
    }
}
