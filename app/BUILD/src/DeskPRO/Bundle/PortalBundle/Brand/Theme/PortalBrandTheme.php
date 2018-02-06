<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
