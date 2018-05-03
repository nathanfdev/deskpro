<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Serializer;

use Application\DeskPRO\Settings\Settings;
use Orb\Serializer\SerializerInterface;
use Orb\Util\Strings;

/**
 * This service should be used as a factory to create API array data from people in various contexts.
 */
class PersonSerializer implements SerializerInterface
{
    /**
     * @var Settings
     */
    private $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param mixed  $data   anything that the serializer can handle
     * @param string $view   defaults to "default" but can be anything and the handlers understand what to do
     * @param string $format requested return format - defaults to an array
     *
     * @return mixed
     */
    public function serialize($data, $view = 'default', $format = 'array')
    {
        /* @var \Application\DeskPRO\Entity\Person $data */
        $agent = $data;

        $data = $agent->toApiData();
        if (!isset($data['primary_phone_number_region']) || !$data['primary_phone_number_region']) {
            $data['primary_phone_number_region'] = $this->settings->get('core.default_country_code');
        }

        $data['notification_settings'] = [
            'no_allow_set_email'   => (bool) $agent->getPref('agent_notif.no_allow_set_email'),
            'no_allow_set_browser' => (bool) $agent->getPref('agent_notif.no_allow_set_browser'),
        ];

        return $data;
    }

    /**
     * @param mixed  $data   anything that the serializer can handle
     * @param string $view   defaults to "default" but can be anything and the handlers understand what to do
     * @param string $format requested return format - defaults to an array
     *
     * @return mixed
     */
    public function supports($data, $view = 'default', $format = 'array')
    {
        // use endsWith, because doctrine entities can be proxy names
        return is_object($data) && Strings::endsWith('Application\DeskPRO\Entity\Person', get_class($data));
    }
}
