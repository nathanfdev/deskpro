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

namespace DeskPRO\Bundle\PortalBundle\Twig;

use Application\DeskPRO\EntityRepository\Template;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use Twig_Error_Loader;

class PortalLoader implements \Twig_LoaderInterface
{
    /**
     * @var \DeskPRO\Bundle\PortalBundle\Brand\BrandStack
     */
    private $brand_stack;

    /**
     * @var \Application\DeskPRO\EntityRepository\Template
     */
    private $template_repo;

    /**
     * @var PortalBrandThemeLoader
     */
    private $brand_theme_loader;

    /**
     * @var array a list of templates that crashed, so we can fallback on filesystem if needed
     */
    private $crashed_templates;

    public function __construct(BrandStack $brand_stack, Template $template_repo, PortalBrandThemeLoader $brand_theme_loader)
    {
        $this->brand_stack        = $brand_stack;
        $this->template_repo      = $template_repo;
        $this->brand_theme_loader = $brand_theme_loader;
        $this->crashed_templates  = [];
    }

    /**
     * Check if we have the source code of a template, given its name.
     *
     * @param string $name The name of the template to check if we can load
     *
     * @return bool If the template source code is handled by this loader or not
     */
    public function exists($name)
    {
        // we only support Theme: type template names in this loader. All others will be loaded by the normal Twig process.
        // see also TemplateNameParser
        return strpos($name, 'Theme:') === 0 || strpos($name, 'ThemeParent:') === 0 || strpos($name, 'ThemeTagTemplate:') === 0;
    }

    /**
     * Gets the source code of a template, given its name.
     *
     * @param string $name The name of the template to load
     *
     * @throws Twig_Error_Loader When $name is not found
     *
     * @return string The template source code
     */
    public function getSource($name)
    {
        if ($path = $this->getBrandTheme()->resolveTemplatePath((string) $name)) {
            return file_get_contents($path);
        }

        throw new Twig_Error_Loader('could not find theme template "'.$name.'"');
    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param string $name The name of the template to load
     *
     * @throws Twig_Error_Loader When $name is not found
     *
     * @return string The cache key
     */
    public function getCacheKey($name)
    {
        // NOT the theme_set id. The actual filesystem theme id.

        $persisted = $this->getDbTemplate($name) ? '1' : '';

        return $this->getBrandTheme()->getActiveThemeSet()->getThemeId()
               .$this->brand_theme_loader->getPortalModeStorage()->getMode()
               .$persisted
               .$name;
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param string $name The template name
     * @param int    $time The last modification time of the cached template
     *
     * @throws Twig_Error_Loader When $name is not found
     *
     * @return bool true if the template is fresh, false otherwise
     */
    public function isFresh($name, $time)
    {
        // If a DB template exists, it should not be fresh
        if ($template = $this->getDbTemplate($name)) {
            false;
        }

        return filemtime($this->getBrandTheme()->resolveTemplatePath((string) $name)) <= $time;
    }

    /**
     * @throws \RuntimeException
     *
     * @return \DeskPRO\Bundle\PortalBundle\Brand\BrandContainer
     */
    protected function getBrandContainer()
    {
        if (!$brand_container = $this->brand_stack->getActive()) {
            $this->brand_stack->push($this->brand_stack->getDefaultBrand());
        }

        if (!$brand_container && !$brand_container = $this->brand_stack->getActive()) {
            throw new \RuntimeException('no brand is active in the brand stack. cannot fetch a theme template.');
        }

        return $brand_container;
    }

    public function markCustomTemplateAsCrashed($name)
    {
        $this->crashed_templates[] = $name;
    }

    /**
     * @param $name
     *
     * @return \Application\DeskPRO\Entity\Template|null
     */
    public function getDbTemplate($name)
    {
        if (in_array($name, $this->crashed_templates)) {
            return; // this db template crashed, so tell the twig env to look into the filesystem as a fallback
        }

        try {
            return $this->getBrandTheme()->getBrandTemplateFromDb((string) $name);
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandTheme
     */
    private function getBrandTheme()
    {
        return $this->brand_theme_loader->getPortalBrandTheme($this->getBrandContainer()->getBrand());
    }
}
