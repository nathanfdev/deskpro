<?php



namespace DeskPRO\Bundle\PortalBundle\Themes\HelpCenter;

use DeskPRO\Bundle\PortalBundle\Theme\AbstractTheme;

class HelpCenterTheme extends AbstractTheme
{
    const THEME_ID = 'helpcenter';

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
        return 'HelpCenter';
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
