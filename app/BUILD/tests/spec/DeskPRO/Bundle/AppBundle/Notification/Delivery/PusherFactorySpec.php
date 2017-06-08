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
