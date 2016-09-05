<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\UpdateBundle\Distro;

use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use DeskPRO\Bundle\UpdateBundle\Distro\Manifest\DistroRelease;
use DeskPRO\Bundle\UpdateBundle\Distro\Manifest\DistroReleaseCollection;
use DeskPRO\Bundle\UpdateBundle\Logger\LogKeyEvent;
use DeskPRO\Component\Util\DebugUtils;
use DeskPRO\Component\Util\Timer;
use DeskPRO\Component\Util\TypeUtils;
use GuzzleHttp;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DistroManifestLoader implements LoggerAwareInterface
{
    const VERSION_API_ROOT  = 'https://deskpro.github.io/';
    const MANIFEST_ENDPOINT = 'releases/manifest.json';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string               $versionApiRoot
     * @param LoggerInterface|null $logger
     * @param LoggerInterface|null $httpLogger
     *
     * @return DistroManifestLoader
     */
    public static function create($versionApiRoot = self::VERSION_API_ROOT, LoggerInterface $logger = null, LoggerInterface $httpLogger = null)
    {
        $stack = GuzzleHttp\HandlerStack::create();

        if ($httpLogger) {
            $stack->push(GuzzleHttp\Middleware::log(
                $httpLogger,
                new GuzzleHttp\MessageFormatter('{method} {uri} -> {code} {phrase}')
            ));
        }

        $client = new HttpClient([
            'base_uri' => $versionApiRoot,

            GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => true,
            GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 10,
            GuzzleHttp\RequestOptions::TIMEOUT         => 10,
            GuzzleHttp\RequestOptions::DECODE_CONTENT  => 'gzip',

            'handler' => $stack,
        ]);

        $l = new self($client);

        if ($logger) {
            $l->setLogger($logger);
        }

        return $l;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->logger = new NullLogger();
    }

    /**
     * @param array $criteria Any criteria to filter the release list by
     *
     * @throws \Exception        Any other uncaught error (dont think there are any)
     * @throws GuzzleException   HTTP error
     * @throws \RuntimeException If the result is invalid
     *
     * @return DistroReleaseCollection
     */
    public function loadReleases(array $criteria = [])
    {
        $t = Timer::start();
        $this->logger->debug('loadReleases -- begin', ['keyEvent' => LogKeyEvent::create('DistroManifestLoader.start')]);

        try {
            $res = $this->doLoadReleases();
            $this->logger->info('Loaded releases OK', ['keyEvent' => LogKeyEvent::create('DistroManifestLoader.success')]);

            if ($criteria) {
                $this->logger->info('Filtering releases with criteria: '.DebugUtils::varToString($criteria));
                $res = $res->filterByCriteria($criteria);
                $this->logger->info(sprintf('Number of filtered releases: %d', $res->count()));
            }

            return $res;
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('[%s:%s] %s', TypeUtils::getBaseTypeName($e), $e->getCode(), $e->getMessage()),
                ['keyEvent' => LogKeyEvent::createForException('DistroManifestLoader.error', $e)]
            );
            throw $e;
        } finally {
            $this->logger->debug(sprintf('loadReleases -- done in %s', $t->formatTotalTime()));
        }
    }

    /**
     * @throws GuzzleException
     * @throws \RuntimeException If the result is invalid
     *
     * @return DistroReleaseCollection
     */
    private function doLoadReleases()
    {
        $response = $this->client->request('GET', self::MANIFEST_ENDPOINT);
        $data     = @json_decode($response->getBody()->getContents(), true);

        /*
            We expect a payload like:
                {
                    "releases": [
                        {
                            "id": "15742.0",
                            "date": "2016-06-10 11:40:00",
                            "track": "stable",
                            "commit": "67f6ad54857b5190c5e5acc96a4ba9aabb807cee",
                            "detail_url": "https://deskpro.github.io/releases/stable/2016-06/15742.0/release.json",
                            "zip_url": "https://github.com/DeskPRO/deskpro.github.io/blob/master/releases/stable/2016-06/15742.0/deskpro.zip?raw=true",
                            "filesize": 165581571,
                            "flags": [],
                            "checksums": {
                                "crc32": "b66232ec",
                                "md5": "7b3bf315bb82edfc252cbaf1fe373a13",
                                "sha1": "cf8b35352e0cbdac0f12e7f44d2eb18b6bab19e1",
                                "sha256": "05c5cc03182ff6a89c847173a3e538c38d939ad73208a7414ef30e196e4771cd"
                            }
                        }
                    ]
                }
         */

        if (!$data || empty($data['releases'])) {
            throw new \UnexpectedValueException('Invalid service result');
        }

        $releases = array_map(function ($r) {
            return new DistroRelease($r);
        }, $data['releases']);

        usort($releases, function (DistroRelease $ra, DistroRelease $rb) {
            $a = $ra->getDate();
            $b = $rb->getDate();

            if ($a === $b) {
                return 0;
            }

            return $a < $b ? -1 : 1;
        });

        $this->logger->info(sprintf('Manifest contains %d releases', count($releases)));

        return new DistroReleaseCollection($releases);
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->client->getConfig('base_uri');
    }
}
