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

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeResolver;
use Doctrine\ORM\EntityManager;

/**
 * The BrandContainerFactory creates BrandContainers for us.
 */
class BrandContainerFactory
{
    /**
     * @var \Application\DeskPRO\NewSettings\SettingsResolver
     */
    private $settings_resolver;

    /**
     * @var \DeskPRO\Bundle\PortalBundle\Theme\ThemeResolver
     */
    private $theme_resolver;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DeskproBlobStorage
     */
    private $bs;

    /**
     * @var PortalModeStorage
     */
    private $mode_storage;

    public function __construct(
        SettingsResolver $settings_resolver,
        ThemeResolver $theme_resolver,
        EntityManager $em,
        DeskproBlobStorage $bs,
        PortalModeStorage $mode_storage
    ) {
        $this->settings_resolver = $settings_resolver;
        $this->theme_resolver    = $theme_resolver;
        $this->em                = $em;
        $this->bs                = $bs;
        $this->mode_storage      = $mode_storage;
    }

    public function create(Brand $brand)
    {
        return new BrandContainer(
            $brand,
            $this->settings_resolver->getBrandSettings($brand),
            $this->theme_resolver,
            new BrandAssetLoader($brand, $this->em, $this->bs),
            $this->mode_storage
        );
    }
}
