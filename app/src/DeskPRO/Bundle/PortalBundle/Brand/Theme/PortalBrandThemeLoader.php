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

namespace DeskPRO\Bundle\PortalBundle\Brand\Theme;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainerFactory;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeResolver;

/**
 * Class PortalBrandThemeLoader.
 */
class PortalBrandThemeLoader
{
    /**
     * @var BrandContainerFactory
     */
    private $brandContainerFactory;

    /**
     * @var ThemeResolver
     */
    private $themeResolver;

    /**
     * @var PortalModeStorage
     */
    private $portalModeStorage;

    /**
     * @var PortalBrandTheme[]
     */
    private $instances = [];

    /**
     * Constructor.
     *
     * @param BrandContainerFactory $brandContainerFactory
     * @param ThemeResolver         $themeResolver
     * @param PortalModeStorage     $portalModeStorage
     */
    public function __construct(BrandContainerFactory $brandContainerFactory, ThemeResolver $themeResolver, PortalModeStorage $portalModeStorage)
    {
        $this->brandContainerFactory = $brandContainerFactory;
        $this->themeResolver         = $themeResolver;
        $this->portalModeStorage     = $portalModeStorage;
    }

    /**
     * @param Brand $brand
     *
     * @return PortalBrandTheme
     */
    public function getPortalBrandTheme(Brand $brand)
    {
        $id = $brand->getId();

        if (!isset($this->instances[$id])) {
            $this->instances[$id] = new PortalBrandTheme(
                $this->brandContainerFactory->create($brand),
                $this->themeResolver,
                $this->portalModeStorage
            );
        }

        return $this->instances[$id];
    }
}
