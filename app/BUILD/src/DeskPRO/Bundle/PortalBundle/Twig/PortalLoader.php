<?php

namespace DeskPRO\Bundle\PortalBundle\Twig;

use Application\DeskPRO\EntityRepository\Template;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use Twig_Error_Loader;

/**
 * Class PortalLoader.
 */
class PortalLoader implements \Twig_LoaderInterface
{
    /**
     * @var \DeskPRO\Bundle\PortalBundle\Brand\BrandStack
     */
    private $brandStack;

    /**
     * @var \Application\DeskPRO\EntityRepository\Template
     */
    private $templateRepo;

    /**
     * @var PortalBrandThemeLoader
     */
    private $brandThemeLoader;

    /**
     * @var array a list of templates that crashed, so we can fallback on filesystem if needed
     */
    private $crashedTemplates;

    /**
     * Constructor.
     *
     * @param BrandStack             $brandStack
     * @param Template               $templateRepo
     * @param PortalBrandThemeLoader $brandThemeLoader
     */
    public function __construct(BrandStack $brandStack, Template $templateRepo, PortalBrandThemeLoader $brandThemeLoader)
    {
        $this->brandStack       = $brandStack;
        $this->templateRepo     = $templateRepo;
        $this->brandThemeLoader = $brandThemeLoader;
        $this->crashedTemplates = [];
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
     * {@inheritdoc}
     */
    public function getSource($name)
    {
        if ($path = $this->getBrandTheme()->resolveTemplatePath((string) $name)) {
            return file_get_contents($path);
        }

        throw new Twig_Error_Loader('could not find theme template "'.$name.'"');
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        // NOT the theme_set id. The actual filesystem theme id.

        $persisted = $this->getDbTemplate($name) ? '1' : '';

        if (!$this->getBrandTheme() || !$this->getBrandTheme()->getActiveThemeSet()) {
            throw new Twig_Error_Loader(sprintf('Template "%s" is not defined.', $name));
        }

        return $this->getBrandTheme()->getActiveThemeSet()->getThemeId()
               .$this->brandThemeLoader->getPortalModeStorage()->getMode()
               .$persisted
               .$name;
    }

    /**
     * {@inheritdoc}
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
        if (!$brand_container = $this->brandStack->getActive()) {
            $this->brandStack->push($this->brandStack->getDefaultBrand());
        }

        if (!$brand_container && !$brand_container = $this->brandStack->getActive()) {
            throw new \RuntimeException('no brand is active in the brand stack. cannot fetch a theme template.');
        }

        return $brand_container;
    }

    /**
     * @param string $name
     */
    public function markCustomTemplateAsCrashed($name)
    {
        $this->crashedTemplates[] = $name;
    }

    /**
     * @param string $name
     *
     * @return \Application\DeskPRO\Entity\Template|null
     */
    public function getDbTemplate($name)
    {
        if (in_array($name, $this->crashedTemplates)) {
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
        return $this->brandThemeLoader->getPortalBrandTheme($this->getBrandContainer()->getBrand());
    }
}
