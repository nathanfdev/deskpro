<?php

namespace DeskPRO\Bundle\AppBundle\SoftwareService\StatService;

use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use GuzzleHttp;

class StatService
{
    const STATS_API_ENDPOINT = 'http://app-stats.deskpro-service.com';

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var
     */
    private $enabled = true;

    /**
     * StatService constructor.
     *
     * @param string $endpoint
     * @param bool   $enabled
     */
    public function __construct($endpoint = self::STATS_API_ENDPOINT, $enabled = true)
    {
        if (!$endpoint) {
            $endpoint = self::STATS_API_ENDPOINT;
        }
        $this->endpoint = $endpoint;
        $this->enabled  = $enabled;
    }

    /**
     * Disable the service.
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Enable the service.
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * @param int $timeout
     *
     * @return HttpClient
     */
    private function getClient($timeout = 5)
    {
        $options = [
            'base_uri' => $this->endpoint,

            GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => true,
            GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => $timeout,
            GuzzleHttp\RequestOptions::TIMEOUT         => $timeout,
            GuzzleHttp\RequestOptions::DECODE_CONTENT  => 'gzip',
        ];

        if (!$this->enabled) {
            $options['handler'] = new GuzzleHttp\Handler\MockHandler([
                new GuzzleHttp\Psr7\Response(200),
                new GuzzleHttp\Psr7\Response(200),
                new GuzzleHttp\Psr7\Response(200),
            ]);
        }

        return new HttpClient($options);
    }

    /**
     * @param StatEvent\InstallStartEvent $event
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendInstallStart(StatEvent\InstallStartEvent $event)
    {
        $data = [
            'installer_version' => $event->getBuild(),
            'user'              => [
                'name'  => $event->getUserName(),
                'email' => $event->getUserEmail(),
            ],
        ];

        return $this->getClient()->request('POST', '/installer/deskpro/'.$event->getUuid().'/start', [
            'json' => $data,
        ]);
    }

    /**
     * @param StatEvent\InstallFailEvent $event
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendInstallFail(StatEvent\InstallFailEvent $event)
    {
        $data = [
            'summary' => $event->getSummary(),
        ];

        return $this->getClient()->request('POST', '/installer/deskpro/'.$event->getUuid().'/error', [
            'json' => $data,
        ]);
    }

    /**
     * @param StatEvent\InstallSuccessEvent $event
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendInstallSuccess(StatEvent\InstallSuccessEvent $event)
    {
        $data = [
            'summary' => $event->getSummary(),
        ];

        return $this->getClient()->request('POST', '/installer/deskpro/'.$event->getUuid().'/success', [
            'json' => $data,
        ]);
    }

    /**
     * @param StatEvent\InstallLogEvent $event
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendInstallLog(StatEvent\InstallLogEvent $event)
    {
        return $this->getClient(15)->request('POST', '/installer/deskpro/'.$event->getUuid().'/logs', [
            'multipart' => [
                [
                    'name'     => 'fileUpload',
                    'filename' => 'log.txt',
                    'contents' => $event->getLogFile() ? file_get_contents($event->getLogFile()->getRealPath()) : $event->getLog(),
                ],
            ],
        ]);
    }

    /**
     * @param StatEvent\UpdateStartEvent $event
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendUpdateStart(StatEvent\UpdateStartEvent $event)
    {
        $data = [
            'installer_version' => $event->getBuild(),
        ];

        return $this->getClient()->request('POST', '/updater/deskpro/'.$event->getUuid().'/start', [
            'json' => $data,
        ]);
    }

    /**
     * @param StatEvent\UpdateFailEvent $event
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendUpdateFail(StatEvent\UpdateFailEvent $event)
    {
        $data = [
            'summary' => $event->getSummary(),
        ];

        return $this->getClient()->request('POST', '/updater/deskpro/'.$event->getUuid().'/error', [
            'json' => $data,
        ]);
    }

    /**
     * @param StatEvent\UpdateSuccessEvent $event
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendUpdateSuccess(StatEvent\UpdateSuccessEvent $event)
    {
        $data = [
            'summary' => $event->getSummary(),
        ];

        return $this->getClient()->request('POST', '/updater/deskpro/'.$event->getUuid().'/success', [
            'json' => $data,
        ]);
    }

    /**
     * @param StatEvent\UpdateLogEvent $event
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendUpdateLog(StatEvent\UpdateLogEvent $event)
    {
        return $this->getClient(15)->request('POST', '/updater/deskpro/'.$event->getUuid().'/logs', [
            'multipart' => [
                [
                    'name'     => 'fileUpload',
                    'filename' => 'log.txt',
                    'contents' => $event->getLogFile() ? file_get_contents($event->getLogFile()->getRealPath()) : $event->getLog(),
                ],
            ],
        ]);
    }
}
