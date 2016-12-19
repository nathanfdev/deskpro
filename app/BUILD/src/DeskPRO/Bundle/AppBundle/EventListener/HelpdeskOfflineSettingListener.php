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
