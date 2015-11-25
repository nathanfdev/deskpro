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
namespace DeskPRO\Bundle\PortalBundle\Twig;

use Application\DeskPRO\EntityRepository\Template;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
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
     * @var array a list of templates that crashed, so we can fallback on filesystem if needed
     */
    private $crashed_templates;

    public function __construct(BrandStack $brand_stack, Template $template_repo)
    {
        $this->brand_stack       = $brand_stack;
        $this->template_repo     = $template_repo;
        $this->crashed_templates = [];
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
        // if a db template crashes, we will try to fetch again, so make sure it isn't marked as crashed first
        //if (!in_array($name, $this->crashed_templates) && $template = $this->getDbTemplate($name)) {
        //    return $template->getTemplateCode();
        //}

        if ($path = $this->getBrandContainer()->resolveTemplatePath((string) $name)) {
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
        $brand = $this->getBrandContainer();

        // TODO: factor in the "edit_theme_id" hierarchy here
        return $brand->getBrand()->getThemeSet()->getId().$name;
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param string    $name The template name
     * @param timestamp $time The last modification time of the cached template
     *
     * @throws Twig_Error_Loader When $name is not found
     *
     * @return bool true if the template is fresh, false otherwise
     */
    public function isFresh($name, $time)
    {
        // If a DB template exists, check its update_at value
        if ($template = $this->getDbTemplate($name)) {
            false;
        }

        // TODO: Possible flaw
        // if you have a template in DB and it is deleted, the cached version will still appear due to the below
        // solution is to mark a template as deleted=1 and have the loader ignore deleted=1 templates.
        return filemtime($this->getBrandContainer()->resolveTemplatePath((string) $name)) <= $time;
    }

    /**
     * @throws \RuntimeException
     *
     * @return \DeskPRO\Bundle\PortalBundle\Brand\BrandContainer
     */
    protected function getBrandContainer()
    {
        if (!$brand_container = $this->brand_stack->getActive()) {
            $this->brand_stack->push($this->brand_stack->getDefault());
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
            return $this->getBrandContainer()->getBrandTemplateFromDb($name);
        } catch (\Exception $e) {
            return;
        }
    }
}
