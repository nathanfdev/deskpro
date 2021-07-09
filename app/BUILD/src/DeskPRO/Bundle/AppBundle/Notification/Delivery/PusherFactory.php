<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery;

use Application\DeskPRO\NewSettings\SettingsResolver;
use Application\DeskPRO\Proxy\OutboundHttpProxy;

/**
 * Class PusherFactory
 *
 * @package DeskPRO\Bundle\AppBundle\Notification\Delivery
 */
class PusherFactory
{
    /**
     * @var OutboundHttpProxy
     */
    private $proxy;

    /**
     * PusherFactory constructor.
     *
     * @param OutboundHttpProxy $proxy
     */
    public function __construct(OutboundHttpProxy $proxy)
    {
        $this->proxy = $proxy;
    }

    /**
     * @param SettingsResolver $resolver
     * @return \Pusher
     */
    public function createPusher(SettingsResolver $resolver)
    {
        $settings = $resolver->getGlobalSettings();

        if (OutboundHttpProxy::isUsingProxy()) {
            return new PusherClient(
                $this->proxy,
                $settings->get('notification.settings.pusher_client.appKey'),
                $settings->get('notification.settings.pusher_client.secret'),
                $settings->get('notification.settings.pusher_client.appId'),
                [
                    'encrypted' => true,
                    'cluster' => $settings->get('notification.settings.pusher_client.cluster'),
                    'timeout' => $settings->get('notification.settings.pusher_client.timeout', 5), //in seconds as Pusher
                    // uses CURLOPT_TIMEOUT
                ]
            );
        }

        return new \Pusher(
            $settings->get('notification.settings.pusher_client.appKey'),
            $settings->get('notification.settings.pusher_client.secret'),
            $settings->get('notification.settings.pusher_client.appId'),
            [
                'cluster' => $settings->get('notification.settings.pusher_client.cluster'),
                'timeout' => $settings->get('notification.settings.pusher_client.timeout', 5), //in seconds as Pusher
                // uses CURLOPT_TIMEOUT
            ]
        );
    }
}
