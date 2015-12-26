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

use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeliveryHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeliveryService;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerInterface;
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

    protected $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config    = $this->container->getParameter('notification.settings');
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return NotificationStrategyInterface
     */
    public function create(SystemEventInterface $event)
    {
        if (array_key_exists($event->getName(), $this->config)) {
            return $this->internalCreate($this->config[$event->getName()]);
        } else {
            throw new \RuntimeException(sprintf('No strategy found for event [ %s ]', $event->getName()));
        }
    }

    private function internalCreate($config)
    {
        $strategy = $this->getStrategy($config['strategy']);
        $strategy->setDeliveryService($this->buildDeliveryService($config['delivery']));
        foreach ($this->getNotifyHandlers() as $handler) {
            $strategy->attachEventHandler($handler);
        }

        return $strategy;
    }

    /**
     * @param string $strategy_name
     *
     * @return NotificationStrategyInterface
     */
    private function getStrategy($strategy_name)
    {
        switch ($strategy_name) {
            case 'immediate':
                return new ImmediateStrategy();
            case 'deffered':
                return new DefferedStrategy();
            default:
                throw new \RuntimeException(sprintf('Strategy with alias [ %s ] wasn\'t found!', $strategy_name));
        }
    }

    private function buildDeliveryService($config)
    {
        $delivery_service       = new DeliveryService();
        $handler_alias_template = 'deskpro.notification.delivery.handler.%s';
        foreach ($config as $handler_alias) {
            $handler_id = sprintf($handler_alias_template, $handler_alias);
            if ($this->container->has($handler_id)) {
                /** @var DeliveryHandlerInterface $delivery_handler */
                $delivery_handler = $this->container->get($handler_id);
                $delivery_service->attachHandler($delivery_handler);
            } else {
                throw new \RuntimeException(sprintf('Delivery handler with alias [ %s ] wasn\'t found!', $handler_alias));
            }
        }

        return $delivery_service;
    }

    /**
     * assume that we always have tow notify handlers for system events (just a stub mb)
     * this is UserNotificationHandler and ActionAlertHandler.
     *
     * @return NotifyHandlerInterface[]
     */
    private function getNotifyHandlers()
    {
        return [
            $this->container->get('deskpro.notification.notify_handler.user_notify'),
            $this->container->get('deskpro.notification.notify_handler.action_alert'),
        ];
    }
}
