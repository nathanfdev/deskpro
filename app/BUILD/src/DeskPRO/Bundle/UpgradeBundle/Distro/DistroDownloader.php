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

namespace DeskPRO\Bundle\UpgradeBundle\Distro;

use DeskPRO\Bundle\UpgradeBundle\Distro\Manifest\DistroRelease;
use GuzzleHttp;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

class DistroDownloader implements LoggerAwareInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface|null $logger
     * @param LoggerInterface|null $httpLogger
     *
     * @return DistroDownloader
     */
    public static function create(LoggerInterface $logger = null, LoggerInterface $httpLogger = null)
    {
        $stack = GuzzleHttp\HandlerStack::create();

        if ($httpLogger) {
            $stack->push(GuzzleHttp\Middleware::log(
                $httpLogger,
                new GuzzleHttp\MessageFormatter('{method} {uri} -> {code} {phrase}')
            ));
        }

        $client = new GuzzleHttp\Client([
            GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => true,
            GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 10,
            GuzzleHttp\RequestOptions::TIMEOUT         => 1800,

            'handler' => $stack,
        ]);

        $dl = new self($client);

        if ($logger) {
            $dl->setLogger($logger);
        }

        return $dl;
    }

    /**
     * DistroDownloader constructor.
     *
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string      $targetPath
     * @param string|null $checksum
     *
     * @return bool
     */
    private function doesTargetExist($targetPath, $checksum = null)
    {
        if (file_exists($targetPath)) {
            $hash = hash_file('sha256', $targetPath);
            if ($checksum !== $hash) {
                $fs = new Filesystem();
                $fs->remove($targetPath);
            }

            // otherwise we have the file already and it's already correct
            return true;
        }

        return false;
    }

    /**
     * @param string      $targetPath
     * @param string|null $checksum
     */
    private function verifyFile($targetPath, $checksum = null)
    {
        if ($checksum) {
            $hash = hash_file('sha256', $targetPath);
            if ($checksum !== $hash) {
                throw new \UnexpectedValueException('The downloaded file has an invalid checksum.');
            }
        }
    }

    /**
     * @param DistroRelease $releaseDetail
     * @param string        $targetPath
     */
    public function download(DistroRelease $releaseDetail, $targetPath)
    {
        if ($this->doesTargetExist($targetPath, $releaseDetail->getSha256())) {
            return;
        }

        $targetFp     = Psr7\try_fopen($targetPath, 'w');
        $targetStream = Psr7\stream_for($targetFp);

        $request = $this->client->request('GET', $releaseDetail->getZipUrl());
        Psr7\copy_to_stream($request->getBody(), $targetStream);
        fclose($targetFp);

        $this->verifyFile($targetPath, $releaseDetail->getSha256());
    }

    /**
     * @param string $zipUrl
     * @param string $targetPath
     * @param string $checksum
     */
    public function downloadUrl($zipUrl, $targetPath, $checksum = null)
    {
        if ($this->doesTargetExist($targetPath, $checksum)) {
            return;
        }

        $targetFp     = Psr7\try_fopen($targetPath, 'w');
        $targetStream = Psr7\stream_for($targetFp);

        $response = $this->client->request('GET', $zipUrl);
        Psr7\copy_to_stream($response->getBody(), $targetStream);
        fclose($targetFp);

        $this->verifyFile($targetPath, $checksum);
    }

    /**
     * @param string $zipPath
     * @param string $targetPath
     * @param string $checksum
     */
    public function downloadLocalFile($zipPath, $targetPath, $checksum = null)
    {
        if ($this->doesTargetExist($targetPath, $checksum)) {
            return;
        }

        $fs = new Filesystem();
        $fs->copy($zipPath, $targetPath, true);

        $this->verifyFile($targetPath, $checksum);
    }
}
