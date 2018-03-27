<?php

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
        $actionAlert->isBroadcast()->willReturn(false);

        $actionAlert->getTarget()->shouldBeCalled();
        $actionAlert->getDate()->shouldBeCalled();
        $actionAlert->getId()->shouldBeCalled();
        $actionAlert->getType()->shouldBeCalled();
        $actionAlert->getData()->shouldBeCalled();
        $actionAlert->isBroadcast()->shouldBeCalled();

        $data = [
            [
                'channel' => 'private-1',
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
