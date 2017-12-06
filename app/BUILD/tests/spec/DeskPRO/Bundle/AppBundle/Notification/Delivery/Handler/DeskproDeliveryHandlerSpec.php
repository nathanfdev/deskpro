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

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler\DeskproDeliveryHandler;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use Firebase\JWT\JWT;
use GuzzleHttp\RequestOptions;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;

/**
 * @mixin DeskproDeliveryHandler.
 */
class DeskproDeliveryHandlerSpec extends ObjectBehavior
{
    public function let(SettingsResolver $resolver, SettingsBag $bag, HttpClient $client, ResponseInterface $response)
    {
        $resolver->getGlobalSettings()->willReturn($bag);
        $response->getStatusCode()->willReturn(200);
        $bag->get('notification.settings.deskpro_client.secret', '')->willReturn('test');
        $this->beConstructedWith($resolver, $client);
    }

    public function it_can_deliver_message(ActionAlert $actionAlert, HttpClient $client, ResponseInterface $response)
    {
        $date = new \DateTime();
        $actionAlert->getTarget()->willReturn(1);
        $actionAlert->getData()->willReturn([]);
        $actionAlert->getDate()->willReturn($date);
        $actionAlert->getType()->willReturn('test.action.alert');

        $actionAlert->getTarget()->shouldBeCalled();
        $actionAlert->getDate()->shouldBeCalled();
        $actionAlert->getId()->shouldBeCalled();
        $actionAlert->getType()->shouldBeCalled();
        $actionAlert->getData()->shouldBeCalled();

        $data = [
            [
                'channel' => 1,
                'name'    => 'action_alert',
                'data'    => [
                        'target' => 1,
                        'date'   => $date,
                        'id'     => null,
                        'type'   => 'test.action.alert',
                    ],
            ],
        ];

        $client
            ->post('/send', [RequestOptions::JSON => ['jwt' => JWT::encode($data, 'test')]])
            ->willReturn($response);

        $this->schedule($actionAlert);
        $this->deliver();
    }
}
