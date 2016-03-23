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

namespace spec\DeskPRO\Bundle\AppBundle\Notification;

use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Notification\NotificationService;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Notification\NotificationClient;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Notification\NotificationConfiguration;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;

/**
 * @mixin NotificationService
 */
class NotificationServiceSpec extends ObjectBehavior
{
    public function let(
        SettingsResolver $settings_resolver,
        EntityManager $em,
        SettingsBag $settings
    ) {
        $settings_resolver->getGlobalSettings()->willReturn($settings);
        $settings->get('notification.settings.strategies')->willReturn($this->getStrategies());
        $settings->get('notification.settings.default_strategy')->willReturn($this->getDefaultStrategy());
        $settings->get('notification.settings.polling_client.polling_interval', 5000)->willReturn(25000);
        $this->beConstructedWith($em, $settings_resolver);
    }

    public function it_could_construct_configuration_array()
    {
        $this->getClientsSetup()->shouldHaveType(NotificationConfiguration::class);
        $clients = $this->getClientsSetup()->getClients();
        $clients->shouldBeArray();
        $clients->shouldHaveCount(1);
        $client = $clients[0];
        $client->shouldHaveType(NotificationClient::class);
        $client->getType()->shouldBe('polling');
        $client->getOptions()->shouldBe(['last_alert' => $this->lastAlert(), 'polling_interval' => 25000]);
    }

    private function getStrategies()
    {
        return [
            'notification.yet.another.system.event' => [
                'strategy' => 'immediate',
                'delivery' => [
                    'db',
                ],
            ],
        ];
    }

    private function getDefaultStrategy()
    {
        return [
            'strategy' => 'immediate',
            'delivery' => [
                'db',
            ],
        ];
    }
}
