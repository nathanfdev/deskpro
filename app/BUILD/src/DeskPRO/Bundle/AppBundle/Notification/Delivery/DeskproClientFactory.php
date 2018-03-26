<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;

class DeskproClientFactory
{
    public function createClient(SettingsResolver $resolver)
    {
        $settings = $resolver->getGlobalSettings();

        return new HttpClient(
            [
                'base_uri' => sprintf(
                    'http%s://%s:%d',
                    $settings->get('notification.settings.deskpro_client.secure') ? 's' : '',
                    $settings->get('notification.settings.deskpro_client.host'),
                    $settings->get('notification.settings.deskpro_client.port')
                ),
            ]
        );
    }
}
