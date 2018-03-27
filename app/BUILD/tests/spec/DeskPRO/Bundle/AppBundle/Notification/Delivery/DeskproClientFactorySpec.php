<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Delivery;

use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeskproClientFactory;
use PhpSpec\ObjectBehavior;

/**
 * @mixin DeskproClientFactory
 */
class DeskproClientFactorySpec extends ObjectBehavior
{
    public function it_uses_factory_method_to_be_constructed(SettingsResolver $resolver, SettingsBag $settings)
    {
        $resolver->getGlobalSettings()->willReturn($settings);
        $settings->get('notification.settings.deskpro_client.port')->willReturn('localhost');
        $settings->get('notification.settings.deskpro_client.host')->willReturn(3000);
        $this->beConstructedThrough('createDeskproClient', [$resolver]);
    }
}
