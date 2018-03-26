<?php

namespace Application\DeskPRO\Elastica;

use Application\DeskPRO\Exception\MissingConfigurationException;
use Application\DeskPRO\Settings\Settings;
use Orb\Util\Arrays;
use Orb\Util\OptionsArray;

/**
 * DeskPRO.
 */
class ClientFactory
{
    /**
     * @var \Application\DeskPRO\Settings\Settings
     */
    private $settings;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param array $config
     *
     * @return Client
     */
    public function createSystemClientByConfig(array $config)
    {
        if (isset($config['connections'][0]['host']) && $config['connections'][0]['host'] == 'DEFAULT') {
            return $this->createClientById('default');
        } else {
            return $this->createClientByConfig($config);
        }
    }

    /**
     * @param string $id
     *
     * @return Client
     */
    public function createClientById($id)
    {
        if ($this->settings->get("elastica.clients.$id.url")) {
            $config = self::createConfigFromUrl($this->settings->get("elastica.clients.$id.url"));
        } else {
            $config = [
                'host'      => $this->settings->get("elastica.clients.$id.host"),
                'port'      => $this->settings->get("elastica.clients.$id.port"),
                'path'      => $this->settings->get("elastica.clients.$id.path") ?: null,
                'transport' => $this->settings->get("elastica.clients.$id.transport") ?: null,
            ];
        }

        if (!$config['host'] || !$config['port']) {
            throw new MissingConfigurationException();
        }

        $config = Arrays::removeFalsey($config);

        return $this->createClientByConfig($config);
    }

    /**
     * @param string $url
     *
     * @throws \Application\DeskPRO\Exception\MissingConfigurationException
     *
     * @return array
     */
    public static function createConfigFromUrl($url)
    {
        if (!$url) {
            throw new MissingConfigurationException('No URL specified');
        }

        if (!preg_match('#^\w+://#', $url)) {
            $url = 'http://'.$url;
        }

        $url_info = parse_url($url);
        if (!$url_info) {
            throw new MissingConfigurationException('Invalid URL');
        }

        $url_info = new OptionsArray($url_info);
        if (!$url_info->has('host')) {
            throw new MissingConfigurationException('Missing host');
        }

        $config = [
            'host'      => $url_info->host,
            'port'      => $url_info->port ?: 9200,
            'path'      => $url_info->path ?: null,
            'transport' => strtolower($url_info->get('scheme', 'http')) == 'https' ? 'Https' : 'Http',
        ];

        if ($url_info->user && $url_info->pass) {
            $config['headers'] = ['Authorization' => 'Basic '.base64_encode($url_info->user.':'.$url_info->pass)];
        }

        return $config;
    }

    /**
     * @param array $config
     *
     * @return Client
     */
    public function createClientByConfig(array $config)
    {
        $config = new OptionsArray($config);

        $client_options = [
            'host'      => $config->get('host', 'localhost'),
            'port'      => $config->get('port', 9200),
            'path'      => $config->get('path', null),
            'transport' => $config->get('transport', null),
            'headers'   => $config->get('headers', []),
            'log'       => $config->get('log', null),
        ];

        if ($config->get('transport') == 'Https') {
            $client_options['curl'] = [CURLOPT_SSL_VERIFYPEER => false];
        }

        $client = new Client($client_options);

        if ($config->get('logger')) {
            $client->setLogger($config->get('logger'));
        }

        return $client;
    }
}
