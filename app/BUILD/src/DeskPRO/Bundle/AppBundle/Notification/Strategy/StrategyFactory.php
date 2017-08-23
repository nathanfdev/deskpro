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
use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeskproDeliveryService;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler\DbDeliveryHandler;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Persistance\PersistenceAdapterInterface;
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
    protected $defaultStrategy;

    /** @var NotificationStrategyInterface[] */
    protected $builtStrategies;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container  = $container;
        $this->cliProcess = php_sapi_name() === 'cli';
        $globalSettings   = $this->container->get('settings_resolver')->getGlobalSettings();
        $this->config     = $globalSettings->get(
            'notification.settings.strategies',
            $this->container->getParameter('notification.settings')
        );
        $this->createDefaultStrategy(
            'notification.settings.default_strategy',
            $globalSettings->get('notification.settings.default_strategy')
        );
    }

    /**
     * @param string $eventName
     * @param array  $config
     */
    private function createDefaultStrategy($eventName, $config)
    {
        $this->defaultStrategy = $this->internalCreate($eventName, $config);
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return NotificationStrategyInterface
     */
    public function create(SystemEventInterface $event)
    {
        if (array_key_exists($event->getName(), $this->config)) {
            return $this->internalCreate($event->getName(), $this->config[$event->getName()]);
        } else {
            return $this->defaultStrategy;
        }
    }

    /**
     * @param array  $config
     * @param string $eventName
     *
     * @return NotificationStrategyInterface
     */
    private function internalCreate($eventName, $config)
    {
        //let us gonna check if we already have strategy for this event

        if (!isset($this->builtStrategies[$eventName])) {
            $strategy = $this->getStrategy($config['strategy']);
            $this->setDeliveryService($strategy, $config);
            $this->setNotifyHandlers($strategy, $config);
            $this->setPersistanceAdapter($strategy, $config);
            $this->builtStrategies[$eventName] = $strategy;
        }

        return $this->builtStrategies[$eventName];
    }

    /**
     * @return NotificationStrategyInterface[]
     */
    public function getAllBuiltStrategies()
    {
        return $this->builtStrategies;
    }

    /**
     * @param string $strategyName
     *
     * @return NotificationStrategyInterface
     */
    private function getStrategy($strategyName)
    {
        switch ($strategyName) {
            case 'immediate':
                $immediateStrategy = new ImmediateStrategy();
                $this->container->get('deskpro.notification.immediate_listener')->pushStrategy($immediateStrategy);
                $this->container->get('deskpro.notification.cli_listener')->pushStrategy($immediateStrategy);

                return $immediateStrategy;
            case 'deferred':
                $deferredStrategy = new DeferredStrategy();
                $this->container->get('deskpro.notification.cli_listener')->pushStrategy($deferredStrategy);

                return $deferredStrategy;
            default:
                throw new \RuntimeException(sprintf('Strategy with alias [ %s ] wasn\'t found!', $strategyName));
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
        $deliveryService      = new DeskproDeliveryService($this->cliProcess);
        $handlerAliasTemplate = 'deskpro.notification.delivery.handler.%s';
        $hasDbHandler         = false;

        foreach ($config as $handlerAlias) {
            $handlerId = sprintf($handlerAliasTemplate, $handlerAlias);
            if ($this->container->has($handlerId)) {
                /** @var DeliveryHandlerInterface $deliveryHandler */
                $deliveryHandler = $this->container->get($handlerId);
                $deliveryService->attachHandler($deliveryHandler);

                if ($deliveryHandler->getType() === DbDeliveryHandler::TYPE) {
                    $hasDbHandler = true;
                }
            } else {
                throw new \RuntimeException(sprintf('Delivery handler with alias [ %s ] wasn\'t found!', $handlerAlias));
            }
        }

        if (!$hasDbHandler) {
            $dbHandler = $this->container->get('deskpro.notification.delivery.handler.db');
            $deliveryService->attachTargettedHandler($dbHandler);
        }

        return $deliveryService;
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
            $adapterId = sprintf('deskpro.notification.peristance.adapter.%s', $config['persistance']);
            if ($this->container->has($adapterId)) {
                /** @var PersistenceAdapterInterface $adapter */
                $adapter = $this->container->get($adapterId);
                $strategy->setPersistenceAdapter($adapter);
            } else {
                throw new \RuntimeException(sprintf('Persistence adapter with alias [ %s ] wasn\'t found!', $config['persistance']));
            }
        }
    }
}
