<?php

namespace DeskPRO\Bundle\PortalBundle\Brand\Theme;

use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeResolver;

/**
 * Class PortalBrandTheme.
 */
class PortalBrandTheme
{
    /**
     * @var BrandContainer
     */
    private $brandContainer;

    /**
     * @var ThemeResolver
     */
    private $themeResolver;

    /**
     * @var PortalModeStorage
     */
    private $portalModeStorage;

    /**
     * Constructor.
     *
     * @param BrandContainer    $brandContainer
     * @param ThemeResolver     $themeResolver
     * @param PortalModeStorage $portalModeStorage
     */
    public function __construct(BrandContainer $brandContainer, ThemeResolver $themeResolver, PortalModeStorage $portalModeStorage)
    {
        $this->brandContainer    = $brandContainer;
        $this->themeResolver     = $themeResolver;
        $this->portalModeStorage = $portalModeStorage;
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Theme\ThemeInterface
     */
    public function getTheme()
    {
        return $this->themeResolver->getThemeById($this->brandContainer->getBrand()->getThemeSet());
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Theme\ThemeInterface|null
     */
    public function getEditTheme()
    {
        return $this->themeResolver->getThemeById($this->brandContainer->getBrand()->getEditThemeSet());
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Entity\ThemeSet
     */
    public function getActiveThemeSet()
    {
        // this depends on the mode we are in
        $mode  = $this->portalModeStorage->getMode();
        $brand = $this->brandContainer->getBrand();

        if ($mode && $mode->isAdminPreview()) {
            return $brand->getEditThemeSet() ?: $brand->getThemeSet();
        } else {
            return $brand->getThemeSet();
        }
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Theme\ThemeInterface
     */
    public function getActiveTheme()
    {
        return $this->themeResolver->getThemeById($this->getActiveThemeSet());
    }

    /**
     * @param $controller
     *
     * @return null|string
     */
    public function resolveController($controller)
    {
        return $this->themeResolver->controller($this->getActiveTheme(), $controller);
    }

    /**
     * @param $name
     *
     * @return string|null
     */
    public function resolveTemplatePath($name)
    {
        $activeTheme = $this->getActiveTheme();
        if (!$activeTheme) {
            return;
        }

        return $this->themeResolver->templatePath($activeTheme, $name);
    }

    /**
     * @param string $tag_name
     * @param array  $arguments
     *
     * @return string
     */
    public function renderTag($tag_name, array $arguments)
    {
        return $this->themeResolver->processTag($this->getActiveTheme(), $tag_name, $arguments);
    }

    /**
     * Returns the proper Template entity from storage if it exists.
     *
     * @param $name
     *
     * @return string
     */
    public function getBrandTemplateFromDb($name)
    {
        return $this->themeResolver->getThemeSetTemplateFromDb($this->getActiveThemeSet(), $name);
    }
}
