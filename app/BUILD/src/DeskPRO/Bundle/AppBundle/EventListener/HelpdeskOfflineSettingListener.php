<?php

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Request\InterfaceInfo;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Same as HelpdeskOfflineLowListener except this checks if the helpdesk was turned off
 * intentionally via settings.
 */
class HelpdeskOfflineSettingListener extends HelpdeskOfflineLowListener
{
    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param InterfaceInfo      $interfaceInfo
     * @param ContainerInterface $container
     * @param BrandStack         $brandStack
     * @param $data_dir
     */
    public function __construct(InterfaceInfo $interfaceInfo, ContainerInterface $container, BrandStack $brandStack, $data_dir)
    {
        $this->brandStack = $brandStack;
        parent::__construct($interfaceInfo, $container, $data_dir);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onPreRequest', 500], // runs before everything
        ];
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getBrandSetting($name)
    {
        $brand = $this->brandStack->getActive();

        return $brand->getSetting($name);
    }
}
