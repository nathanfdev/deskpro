<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use Application\DeskPRO\Entity\Template;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use DeskPRO\Bundle\PortalBundle\Designer\AdvancedEditsManager;
use DeskPRO\Bundle\PortalBundle\Designer\AssetsManager;
use DeskPRO\Bundle\PortalBundle\Designer\BrandThemeManager;
use DeskPRO\Bundle\PortalBundle\Designer\PortalStylesCompiler;
use DeskPRO\Bundle\PortalBundle\Designer\SassDocParser;
use DeskPRO\Bundle\PortalBundle\Designer\StylesManager;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeInterface;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeResolver;

/**
 * Class HelperMethods.
 */
trait HelperMethods
{
    /**
     * @param string $id
     *
     * @return mixed
     */
    abstract public function get($id);

    /**
     * @return \DeskPRO\Bundle\BrandBundle\Brand\BrandContainer
     */
    abstract protected function getBrandContainer();

    /**
     * @return StylesManager
     */
    private function getStylesManager()
    {
        return $this->get('dp.portal.designer.styles_manager');
    }

    /**
     * @return AdvancedEditsManager
     */
    private function getAdvancedEditsManager()
    {
        return $this->get('dp.portal.designer.advanced_edits_manager');
    }

    /**
     * @return AssetsManager
     */
    private function getAssetsManager()
    {
        return $this->get('dp.portal.designer.assets_manager');
    }

    /**
     * @return PortalStylesCompiler
     */
    private function getPortalStylesCompiler()
    {
        return $this->get('dp.portal.designer.portal_styles_compiler');
    }

    /**
     * @return SassDocParser
     */
    private function getSassDocParser()
    {
        return $this->get('dp.portal.designer.sass_doc_parser');
    }

    /**
     * @return ThemeResolver
     */
    private function getThemeResolver()
    {
        return $this->get('theme_resolver');
    }

    /**
     * @param string $templateName
     *
     * @return Template
     */
    private function getEditThemeSetTemplate($templateName)
    {
        $theme        = $this->getTheme();
        $editThemeSet = $this->getEditThemeSet();

        if (!$editThemeSet) {
            return;
        }

        if (!array_key_exists($templateName, $theme->getTemplateMap())) {
            throw $this->createNotFoundException('Unable to find requested template');
        }

        $template = $this->getThemeResolver()->getThemeSetTemplateFromDb($editThemeSet, $templateName);

        return $template;
    }

    /**
     * @return ThemeInterface
     */
    private function getTheme()
    {
        $brand = $this->getBrandContainer()->getBrand();

        return $this->getPortalBrandThemeLoader()->getPortalBrandTheme($brand)->getTheme();
    }

    /**
     * @return ThemeSet
     */
    private function getEditThemeSet()
    {
        return $this->getBrandContainer()->getBrand()->getEditThemeSet();
    }

    /**
     * @return PortalBrandThemeLoader
     */
    private function getPortalBrandThemeLoader()
    {
        return $this->get('portal_brand_theme_loader');
    }

    /**
     * @return BrandThemeManager
     */
    private function getBrandThemeManager()
    {
        return $this->get('dp.portal.designer.brand_theme_manager');
    }
}
