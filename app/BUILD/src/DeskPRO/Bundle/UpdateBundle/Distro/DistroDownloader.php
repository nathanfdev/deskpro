<?php

namespace DeskPRO\Bundle\UpdateBundle\Distro;

use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use DeskPRO\Bundle\UpdateBundle\Distro\Manifest\DistroRelease;
use DeskPRO\Bundle\UpdateBundle\Logger\LogKeyEvent;
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

        $client = new HttpClient([
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
        $this->logger->info(sprintf('Target file: %s', $targetPath));

        if (file_exists($targetPath)) {
            $this->logger->info('Target already exists');

            if ($checksum) {
                $this->logger->info('Verifying checksum');
                $hash = hash_file('sha256', $targetPath);

                $this->logger->info('Calculated checksum: '.$hash);
                $this->logger->info('Expected checksum:   '.$checksum);

                if ($checksum === $hash) {
                    $this->logger->info('Checksum OK');

                    return true;
                }

                $this->logger->info('Checksum invalid');
            } else {
                $this->logger->info('No checksum provided to validate the file');
            }

            $this->logger->info('Deleting existing file');

            $fs = new Filesystem();
            $fs->remove($targetPath);
        }

        return false;
    }

    /**
     * @param string      $targetPath
     * @param string|null $checksum
     */
    private function verifyFile($targetPath, $checksum = null)
    {
        if (!is_file($targetPath)) {
            $this->logger->error("Target path does not exist: $targetPath");
            throw new \UnexpectedValueException('The target path does not exist');
        }

        $this->logger->info('Filesize: '.filesize($targetPath));

        if ($checksum) {
            $hash = hash_file('sha256', $targetPath);
            if ($checksum !== $hash) {
                $this->logger->error("Invalid checksum. Expected: $checksum, Got: $hash");
                throw new \UnexpectedValueException('The downloaded file has an invalid checksum.');
            }
        }
    }

    /**
     * @param DistroRelease $releaseDetail
     * @param string        $targetPath
     *
     * @throws \RuntimeException                                   When failing to copy stream
     * @throws \UnexpectedValueException                           When failing to verify checksum
     * @throws \Symfony\Component\Filesystem\Exception\IOException When failing to delete an existing file
     * @throws \Exception
     */
    public function download(DistroRelease $releaseDetail, $targetPath)
    {
        $this->logger->info(
            "Downloading distro: {$releaseDetail->getId()} from {$releaseDetail->getZipUrl()}",
            ['keyEvent' => LogKeyEvent::create('DistroDownload.start')]
        );

        try {
            if ($this->doesTargetExist($targetPath, $releaseDetail->getSha256())) {
                $this->logger->info('Got existing file OK', ['keyEvent' => LogKeyEvent::create('DistroDownload.success')]);

                return;
            }

            $targetFp     = Psr7\try_fopen($targetPath, 'w');
            $targetStream = Psr7\stream_for($targetFp);

            $request = $this->client->request('GET', $releaseDetail->getZipUrl());
            Psr7\copy_to_stream($request->getBody(), $targetStream);
            fclose($targetFp);

            $this->verifyFile($targetPath, $releaseDetail->getSha256());
            $this->logger->info('Got file OK', ['keyEvent' => LogKeyEvent::create('DistroDownload.success')]);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('[%s:%s] %s', get_class($e), $e->getCode(), $e->getMessage()),
                ['keyEvent' => LogKeyEvent::createForException('DistroDownload.error', $e)]
            );
            throw $e;
        }
    }

    /**
     * @param string $zipUrl
     * @param string $targetPath
     * @param string $checksum
     *
     * @throws \RuntimeException                                   When failing to copy stream
     * @throws \UnexpectedValueException                           When failing to verify checksum
     * @throws \Symfony\Component\Filesystem\Exception\IOException When failing to delete an existing file
     * @throws \Exception
     */
    public function downloadUrl($zipUrl, $targetPath, $checksum = null)
    {
        $this->logger->info(
            "Downloading ZIP: $zipUrl",
            ['keyEvent' => LogKeyEvent::create('DistroDownload.start')]
        );

        try {
            if ($this->doesTargetExist($targetPath, $checksum)) {
                $this->logger->info('Got existing file OK', ['keyEvent' => LogKeyEvent::create('DistroDownload.success')]);

                return;
            }

            $targetFp     = Psr7\try_fopen($targetPath, 'w');
            $targetStream = Psr7\stream_for($targetFp);

            $response = $this->client->request('GET', $zipUrl);
            Psr7\copy_to_stream($response->getBody(), $targetStream);
            fclose($targetFp);

            $this->verifyFile($targetPath, $checksum);
            $this->logger->info('Got file OK', ['keyEvent' => LogKeyEvent::create('DistroDownload.success')]);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('[%s:%s] %s', get_class($e), $e->getCode(), $e->getMessage()),
                ['keyEvent' => LogKeyEvent::createForException('DistroDownload.error', $e)]
            );
            throw $e;
        }
    }

    /**
     * @param string $zipPath
     * @param string $targetPath
     * @param string $checksum
     *
     * @throws \RuntimeException                                   When failing to copy stream
     * @throws \UnexpectedValueException                           When failing to verify checksum
     * @throws \Symfony\Component\Filesystem\Exception\IOException When failing to delete an existing file
     * @throws \Exception
     */
    public function downloadLocalFile($zipPath, $targetPath, $checksum = null)
    {
        $this->logger->info(
            "Copying local file $zipPath",
            ['keyEvent' => LogKeyEvent::create('DistroDownload.start')]
        );

        try {
            if ($this->doesTargetExist($targetPath, $checksum)) {
                $this->logger->info('Got existing file OK', ['keyEvent' => LogKeyEvent::create('DistroDownload.success')]);

                return;
            }

            $fs = new Filesystem();
            $fs->copy($zipPath, $targetPath, true);

            $this->verifyFile($targetPath, $checksum);
            $this->logger->info('Got file OK', ['keyEvent' => LogKeyEvent::create('DistroDownload.success')]);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('[%s:%s] %s', get_class($e), $e->getCode(), $e->getMessage()),
                ['keyEvent' => LogKeyEvent::createForException('DistroDownload.error', $e)]
            );
            throw $e;
        }
    }
}
