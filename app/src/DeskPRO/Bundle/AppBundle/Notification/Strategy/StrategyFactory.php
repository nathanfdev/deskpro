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

namespace DeskPRO\Bundle\AppBundle\Notification\Strategy;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StrategyFactory.
 */
class StrategyFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $standalone
     *
     * @return NotificationStrategyInterface
     */
    public function create($standalone = true)
    {
        if ($standalone) {
            $strategy = new StandaloneStrategy();
            $strategy->setDeliveryService($this->container->get('deskpro.notification.delivery.delivery_service'));

            return $strategy;
        } else {
            //TODO just a stub, it should be created depending on settings via factory
            return new CloudStrategy($this->container->get('deskpro.notification.peristance.adapter.db'));
        }
    }
}
