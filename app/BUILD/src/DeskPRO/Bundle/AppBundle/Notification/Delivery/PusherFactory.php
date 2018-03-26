<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery;

use Application\DeskPRO\NewSettings\SettingsResolver;

class PusherFactory
{
    public function createPusher(SettingsResolver $resolver)
    {
        $settings = $resolver->getGlobalSettings();

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
