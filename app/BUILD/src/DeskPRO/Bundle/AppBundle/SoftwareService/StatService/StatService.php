<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
