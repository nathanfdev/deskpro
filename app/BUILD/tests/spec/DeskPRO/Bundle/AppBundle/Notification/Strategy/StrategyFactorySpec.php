<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Strategy;

use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\EventListener\CliStrategyListener;
use DeskPRO\Bundle\AppBundle\EventListener\ImmediateStrategyListener;
use DeskPRO\Bundle\AppBundle\Notification\ActionAlertHandler;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler\DbDeliveryHandler;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler\PusherDeliveryHandler;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Persistance\PersistenceAdapterInterface;
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
        PersistenceAdapterInterface $adapterInterface

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
        PersistenceAdapterInterface $adapterInterface)
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

        $container->has('deskpro.notification.immediate_listener')->willReturn(true);
        $container->get('deskpro.notification.immediate_listener')->willReturn(new ImmediateStrategyListener());

        $container->has('deskpro.notification.cli_listener')->willReturn(true);
        $container->get('deskpro.notification.cli_listener')->willReturn(new CliStrategyListener());
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
