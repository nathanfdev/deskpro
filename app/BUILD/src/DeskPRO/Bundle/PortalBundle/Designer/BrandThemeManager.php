<?php

namespace DeskPRO\Bundle\PortalBundle\Designer;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
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
     * @param EntityManager                                $em
     * @param \DeskPRO\Bundle\BrandBundle\Brand\BrandStack $brandStack
     * @param ThemeSetCopyingService                       $themeSetCopyingService
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
        $brand = $this->getCurrentBrand();

        if (!$editThemeSet = $brand->getEditThemeSet()) {
            $editThemeSet = new ThemeSet();
            $editThemeSet->setBrand($brand);

            $this->themeSetCopyingService->copy($this->getCurrentThemeSet(), $editThemeSet);

            $brand->setEditThemeSet($editThemeSet);
            $this->em->persist($editThemeSet);
            $this->em->persist($brand);
            $this->em->flush();
        }

        return $editThemeSet;
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
