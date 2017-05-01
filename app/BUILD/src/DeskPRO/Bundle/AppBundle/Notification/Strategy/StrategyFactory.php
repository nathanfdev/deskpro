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

namespace DeskPRO\Bundle\AppBundle\Notification\Strategy;

use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeliveryHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeliveryService;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Persistance\PersistanceAdapterInterface;
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

    /**
     * @var mixed
     */
    protected $config;

    /** @var NotificationStrategyInterface */
    protected $default_strategy;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $global_settings = $this->container->get('settings_resolver')->getGlobalSettings();
        $this->config    = $global_settings->get(
            'notification.settings.strategies',
            $this->container->getParameter('notification.settings')
        );
        $this->createDefaultStrategy($global_settings->get('notification.settings.default_strategy'));
    }

    private function createDefaultStrategy($config)
    {
        $this->default_strategy = $this->internalCreate($config);
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
            return $this->default_strategy;
        }
    }

    /**
     * @param $config
     *
     * @return NotificationStrategyInterface
     */
    private function internalCreate($config)
    {
        $strategy = $this->getStrategy($config['strategy']);
        $this->setDeliveryService($strategy, $config);
        $this->setNotifyHandlers($strategy, $config);
        $this->setPersistanceAdapter($strategy, $config);

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
                $immediateStrategy = new ImmediateStrategy();
                $this->container->get('deskpro.notification.immediate_listener')->pushStrategy($immediateStrategy);

                return $immediateStrategy;
            case 'deferred':
                return new DeferredStrategy();
            default:
                throw new \RuntimeException(sprintf('Strategy with alias [ %s ] wasn\'t found!', $strategy_name));
        }
    }

    /**
     * @param NotificationStrategyInterface $strategy
     * @param array                         $config
     *
     * @return NotificationStrategyInterface
     */
    private function setDeliveryService(NotificationStrategyInterface $strategy, array $config)
    {
        return $strategy->setDeliveryService($this->buildDeliveryService($config['delivery']));
    }

    /**
     * @param $config
     *
     * @return DeliveryService
     */
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

    private function setNotifyHandlers(NotificationStrategyInterface $strategy, array $config)
    {
        foreach ($this->getNotifyHandlers() as $handler) {
            $strategy->attachEventHandler($handler);
        }
    }

    /**
     * assume that we always have two notify handlers for system events (just a stub mb)
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

    private function setPersistanceAdapter(NotificationStrategyInterface $strategy, $config)
    {
        if (array_key_exists('persistance', $config)) {
            $adapter_id = sprintf('deskpro.notification.peristance.adapter.%s', $config['persistance']);
            if ($this->container->has($adapter_id)) {
                /** @var PersistanceAdapterInterface $adapter */
                $adapter = $this->container->get($adapter_id);
                $strategy->setPersistanceAdapter($adapter);
            } else {
                throw new \RuntimeException(sprintf('Persistance adapter with alias [ %s ] wasn\'t found!', $config['persistance']));
            }
        }
    }
}
