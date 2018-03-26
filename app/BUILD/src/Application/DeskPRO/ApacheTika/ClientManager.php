<?php

namespace Application\DeskPRO\ApacheTika;

use Application\DeskPRO\Exception\MissingConfigurationException;
use Application\DeskPRO\Settings\Settings;
use Vaites\ApacheTika\Clients\WebClient;

/**
 * DeskPRO.
 */
class ClientManager
{
    /**
     * @var \Application\DeskPRO\Settings\Settings
     */
    private $settings;

    /**
     * @var WebClient
     */
    private $client;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    private function createClient()
    {
        $client = new WebClient(
            $this->settings->get('elastica.tika.ip_address'),
            $this->settings->get('elastica.tika.port')
        );

        return $client;
    }

    /**
     * @param array $config
     *
     * @return WebClient
     */
    private function createClientFromConfig($config)
    {
        $client = new WebClient(
            $config['ip'],
            $config['port']
        );

        return $client;
    }

    /**
     * @param string   $ip
     * @param int|null $port
     *
     * @throws \Application\DeskPRO\Exception\MissingConfigurationException
     *
     * @return array
     */
    public static function createConfigFromUrl($ip, $port = null)
    {
        if (!$ip) {
            throw new MissingConfigurationException('Ip not specified');
        }

        if ($port && !is_numeric($port)) {
            throw new MissingConfigurationException('Invalid port');
        }

        $config = [
            'ip'   => $ip,
            'port' => $port ?: 9998,
        ];

        return $config;
    }

    public function getClient()
    {
        if (!$this->client) {
            $this->client = $this->createClient();
        }

        return $this->client;
    }

    /**
     * @param array $config
     *
     * @return WebClient
     */
    public function getClientFromConfig($config)
    {
        if (!$this->client) {
            $this->client = $this->createClientFromConfig($config);
        }

        return $this->client;
    }

    public function isEnabled()
    {
        return $this->settings->get('elastica.tika.enabled');
    }
}
