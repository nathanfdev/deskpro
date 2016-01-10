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
namespace DeskPRO\Bundle\PortalBundle\Brand;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\PortalBundle\Designer\ThemeSetCopyingService;
use Doctrine\ORM\EntityManager;

/**
 * The BrandStack is a way of managing changes in "active" Brands during runtime. It works similar to a stack to allow
 * pushing into and popping out of brands during runtime. However, it keeps an internal state of the constructed
 * BrandContainer's so that each BrandContainer only need be created once during a single request, even if you pop in
 * and out of different brands multiple times.
 *
 * This allows us to operate in a brand context and revert back to the old brand context without caring about how that
 * is done.
 *
 * Ex. use in a service that depends on this stack
 *    public function mailMarketingPromo(Brand $brand)
 *    {
 *         $this->brandStack->push($brand) // will construct the brand container if it doesnt exist
 *         $bc = $this->brandStack->getActive()
 *
 *         // email using settings and data from the $bc
 *
 *         $this->brandStack->pop() // revert the stack so that our service doesn't interrupt others
 *     }
 *
 * Any service / controller that wants to work with a brand (settings/templating/etc) should simply depend on this
 * BrandStack and use getActive(). Pop in an out of different brands as necessary.
 */
class BrandStack
{
    /**
     * @var BrandContainerFactory
     */
    private $factory;

    /**
     * @var \array
     */
    private $stack;

    /**
     * @var BrandContainer[] an array of constructed containers keyed by brand entity id
     */
    private $brand_containers;

    /**
     * @var \Application\DeskPRO\Entity\Brand
     */
    private $default_brand;

    /**
     * @var ThemeSetCopyingService
     */
    private $theme_set_copying_service;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param BrandContainerFactory  $factory
     * @param Brand                  $default_brand
     * @param ThemeSetCopyingService $theme_set_copying_service
     * @param EntityManager          $em
     */
    public function __construct(
        BrandContainerFactory $factory,
        Brand $default_brand,
        ThemeSetCopyingService $theme_set_copying_service,
        EntityManager $em
    ) {
        $this->factory                   = $factory;
        $this->stack                     = [];
        $this->brand_containers          = [];
        $this->default_brand             = $default_brand;
        $this->theme_set_copying_service = $theme_set_copying_service;
        $this->em                        = $em;
    }

    /**
     * Gives you the active BrandContainer.
     *
     * @return BrandContainer
     */
    public function getActive()
    {
        $brand_id = end($this->stack);

        if (false !== $brand_id) {
            return $this->brand_containers[$brand_id];
        }

        return;
    }

    public function getStack()
    {
        return $this->stack;
    }

    /**
     * Pushes the Brand into the stack, so that the brand's container is now active.
     *
     * @param Brand $brand
     *
     * @return BrandContainer
     */
    public function push(Brand $brand)
    {
        $brand_id = $brand->getId();

        $themeset = $brand->getThemeSet();
        $theme    = $themeset->getThemeId();

        array_push($this->stack, $brand_id);

        if (!array_key_exists($brand_id, $this->brand_containers)) {
            $this->brand_containers[$brand_id] = $this->factory->create($brand);
        }

        return $this->getActive();
    }

    /**
     * Reverts pops the state, making the previous brand container active.
     */
    public function pop()
    {
        array_pop($this->stack);
    }

    public function getDefault()
    {
        return $this->default_brand;
    }

    /**
     * Get ThemeSet of the active Brand.
     *
     * @throws \Exception
     *
     * @return ThemeSet
     */
    public function getCurrentThemeSet()
    {
        if (!$theme_set = $this->getCurrentBrand()->getThemeSet()) {
            throw new \Exception('Unable to resolve ThemeSet');
        }

        return $theme_set;
    }

    /**
     * Get edit ThemeSet of the active Brand.
     *
     * If the active Brand doesn't have edit ThemeSet assigned to it, then method creates a copy of the active ThemeSet
     * and persists it as edit ThemeSet.
     *
     * @throws \Exception
     *
     * @return ThemeSet
     */
    public function getCurrentEditThemeSet()
    {
        if (!$edit_theme_set = $this->getCurrentBrand()->getEditThemeSet()) {
            $theme_set      = $this->getCurrentThemeSet();
            $edit_theme_set = new ThemeSet();
            $this->theme_set_copying_service->copy($theme_set, $edit_theme_set);
            $brand = $this->getCurrentBrand();
            $brand->setEditThemeSet($edit_theme_set);
            $this->em->persist($edit_theme_set);
            $this->em->persist($brand);
            $this->em->flush();
        }

        return $edit_theme_set;
    }

    /**
     * @throws \Exception
     *
     * @return Brand
     */
    private function getCurrentBrand()
    {
        if (!$this->getActive() || !$this->getActive()->getBrand()) {
            throw new \Exception('Unable to resolve current Brand');
        }

        return $this->getActive()->getBrand();
    }
}
