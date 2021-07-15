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
     * @var SettingsResolver
     */
    private $settings;

    /**
     * PusherFactory constructor.
     *
     * @param OutboundHttpProxy $proxy
     * @param SettingsResolver $settings
     */
    public function __construct(OutboundHttpProxy $proxy, SettingsResolver $settings)
    {
        $this->proxy = $proxy;
        $this->settings = $settings;
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
                $this->settings,
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
