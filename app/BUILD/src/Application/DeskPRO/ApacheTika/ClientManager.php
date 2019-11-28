<?php

namespace Application\DeskPRO\ApacheTika;

use Application\DeskPRO\Exception\MissingConfigurationException;
use Application\DeskPRO\Settings\Settings;
use Symfony\Component\DependencyInjection\Container;
use Vaites\ApacheTika\Clients\WebClient;

/**
 * DeskPRO.
 */
class ClientManager
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @var WebClient
     */
    private $client;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    private function getSettings()
    {
        return $this->container->get('deskpro.core.settings');
    }

    private function createClient()
    {
        $client = new WebClient(
            $this->getSettings()->get('elastica.tika.ip_address'),
            $this->getSettings()->get('elastica.tika.port')
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
        return $this->getSettings()->get('elastica.tika.enabled');
    }
}
