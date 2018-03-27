<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Delivery;

use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\PusherFactory;
use PhpSpec\ObjectBehavior;

/**
 * @mixin PusherFactory
 */
class PusherFactorySpec extends ObjectBehavior
{
    public function it_uses_factory_method_to_be_constructed(SettingsResolver $resolver, SettingsBag $settings)
    {
        $resolver->getGlobalSettings()->willReturn($settings);
        $settings->get('notification.settings.pusher_client.appKey')->willReturn('testAppKey');
        $settings->get('notification.settings.pusher_client.secret')->willReturn('testAppSecret');
        $settings->get('notification.settings.pusher_client.appId')->willReturn('testAppId');
        $settings->get('notification.settings.pusher_client.channel_prefix')->willReturn('');
        $settings->get('notification.settings.pusher_client.cluster')->willReturn('mt1');
        $this->beConstructedThrough('createPusher', [$resolver]);
    }
}
