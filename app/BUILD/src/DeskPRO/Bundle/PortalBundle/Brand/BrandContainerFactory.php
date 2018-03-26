<?php

namespace DeskPRO\Bundle\PortalBundle\Brand;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\NewSettings\SettingsResolver;
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
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param SettingsResolver $settings_resolver
     * @param EntityManager    $em
     */
    public function __construct(
        SettingsResolver $settings_resolver,
        EntityManager $em
    ) {
        $this->settings_resolver = $settings_resolver;
        $this->em                = $em;
    }

    /**
     * @param Brand $brand
     *
     * @return BrandContainer
     */
    public function create(Brand $brand)
    {
        return new BrandContainer(
            $brand,
            $this->settings_resolver->getBrandSettings($brand)
        );
    }
}
