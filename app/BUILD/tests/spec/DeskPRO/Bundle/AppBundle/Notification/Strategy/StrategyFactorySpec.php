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

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Strategy;

use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Notification\ActionAlertHandler;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler\DbDeliveryHandler;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler\PusherDeliveryHandler;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Persistance\PersistanceAdapterInterface;
use DeskPRO\Bundle\AppBundle\Notification\Strategy\StrategyFactory;
use DeskPRO\Bundle\AppBundle\Notification\UserNotificationHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @mixin StrategyFactory
 */
class StrategyFactorySpec extends ObjectBehavior
{
    public function let(
        SettingsResolver $settings_resolver,
        SettingsBag $settings,
        ContainerInterface $container,
        DbDeliveryHandler $dbHandler,
        PusherDeliveryHandler $pusherHandler,
        PersistanceAdapterInterface $adapterInterface

        ) {
        $this->configureContainer($container, $settings_resolver, $dbHandler, $pusherHandler, $adapterInterface);
        $this->configureSettingsResolver($settings_resolver, $settings);
        $this->configureSettings($settings);
        $this->beConstructedWith($container);
    }

    public function it_can_create_immediate_strategy_with_db_handler(SystemEventInterface $event)
    {
        $event->getName()->willReturn('notification.system.event.with.immediate.strategy');
        $this->create($event)->shouldHaveType('\DeskPRO\Bundle\AppBundle\Notification\Strategy\ImmediateStrategy');
    }

    public function it_can_create_immediate_strategy_with_pusher_handler(SystemEventInterface $event)
    {
        $event->getName()->willReturn('notification.another.system.event.with.immediate.strategy');
        $this->create($event)->shouldHaveType('\DeskPRO\Bundle\AppBundle\Notification\Strategy\ImmediateStrategy');
    }

    public function it_can_create_deferred_strategy_with_db_handler(SystemEventInterface $event, NotifyHandlerInterface $handlerInterface)
    {
        $event->getName()->willReturn('notification.system.event.with.deferred.strategy');
        $strategy = $this->create($event);
        $strategy->shouldHaveType('\DeskPRO\Bundle\AppBundle\Notification\Strategy\DeferredStrategy');
    }

    public function it_can_create_deferred_strategy_with_pusher_handler(SystemEventInterface $event)
    {
        $event->getName()->willReturn('notification.another.system.event.with.deferred.strategy');
        $strategy = $this->create($event);
        $strategy->shouldHaveType('\DeskPRO\Bundle\AppBundle\Notification\Strategy\DeferredStrategy');
    }

    private function configureContainer(
        ContainerInterface $container,
        SettingsResolver $settings_resolver,
        DbDeliveryHandler $dbHandler,
        PusherDeliveryHandler $pusherHandler,
        PersistanceAdapterInterface $adapterInterface)
    {
        $container->get('settings_resolver')->willReturn($settings_resolver);
        $container->getParameter('notification.settings')->willReturn([]);

        $container->has('deskpro.notification.delivery.handler.db')->willReturn(true);
        $container->get('deskpro.notification.delivery.handler.db')->willReturn($dbHandler);

        $container->has('deskpro.notification.delivery.handler.pusher')->willReturn(true);
        $container->get('deskpro.notification.delivery.handler.pusher')->willReturn($pusherHandler);

        $container->has(Argument::containingString('deskpro.notification.peristance.adapter.db'))->willReturn(true);
        $container->get(Argument::containingString('deskpro.notification.peristance.adapter.db'))->willReturn($adapterInterface);

        $container->get('deskpro.notification.notify_handler.user_notify')->willReturn(new UserNotificationHandler());
        $container->get('deskpro.notification.notify_handler.action_alert')->willReturn(new ActionAlertHandler());
    }

    private function configureSettingsResolver(SettingsResolver $settings_resolver, SettingsBag $settings)
    {
        $settings_resolver->getGlobalSettings()->willReturn($settings);
    }

    private function configureSettings(SettingsBag $settings)
    {
        $settings->get('notification.settings.strategies', [])->willReturn($this->strategiesConfig());
        $settings->get('notification.settings.default_strategy')->willReturn($this->defaultStrategyConfig());
    }

    private function strategiesConfig()
    {
        return [
            'notification.system.event.with.deferred.strategy' => [
                'strategy' => 'deferred',
                'delivery' => [
                    'db',
                ],
                'persistance' => 'db',
            ],
            'notification.another.system.event.with.deferred.strategy' => [
                'strategy' => 'deferred',
                'delivery' => [
                    'pusher',
                ],
                'persistance' => 'db',
            ],
            'notification.system.event.with.immediate.strategy' => [
                'strategy' => 'immediate',
                'delivery' => [
                    'db',
                ],
            ],
            'notification.another.system.event.with.immediate.strategy' => [
                'strategy' => 'immediate',
                'delivery' => [
                    'pusher',
                ],
            ],
        ];
    }

    private function defaultStrategyConfig()
    {
        return [
            'strategy' => 'immediate',
            'delivery' => [
                'db',
            ],
        ];
    }
}
