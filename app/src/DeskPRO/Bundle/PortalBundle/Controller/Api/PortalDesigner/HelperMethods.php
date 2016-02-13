<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use Application\DeskPRO\Entity\Template;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use DeskPRO\Bundle\PortalBundle\Designer\AdvancedEditsManager;
use DeskPRO\Bundle\PortalBundle\Designer\AssetsManager;
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
     * @return \DeskPRO\Bundle\PortalBundle\Brand\BrandContainer
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
     * @param string $template_name
     *
     * @return Template
     */
    private function getEditThemeSetTemplate($template_name)
    {
        $theme          = $this->getTheme();
        $edit_theme_set = $this->getEditThemeSet();

        if (!array_key_exists($template_name, $theme->getTemplateMap())) {
            throw $this->createNotFoundException('Unable to find requested template');
        }

        $template = $this->getThemeResolver()->getThemeSetTemplateFromDb($edit_theme_set, $template_name);

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
}
