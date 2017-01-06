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

namespace DeskPRO\Bundle\PortalBundle\Designer;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Doctrine\ORM\EntityManager;

/**
 * Class BrandThemeManager.
 */
class BrandThemeManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ThemeSetCopyingService
     */
    private $themeSetCopyingService;

    /**
     * @var ThemeSet
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param EntityManager          $em
     * @param BrandStack             $brandStack
     * @param ThemeSetCopyingService $themeSetCopyingService
     */
    public function __construct(EntityManager $em, BrandStack $brandStack, ThemeSetCopyingService $themeSetCopyingService)
    {
        $this->em                     = $em;
        $this->brandStack             = $brandStack;
        $this->themeSetCopyingService = $themeSetCopyingService;
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
            $this->themeSetCopyingService->copy($theme_set, $edit_theme_set);
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
        if (!$this->brandStack->getActive() || !$this->brandStack->getActive()->getBrand()) {
            throw new \Exception('Unable to resolve current Brand');
        }

        return $this->brandStack->getActive()->getBrand();
    }
}
