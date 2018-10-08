<?php

namespace DeskPRO\Bundle\ImportBundle\Storage\StorageAdapter;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

/**
 * Class AmazonS3Adapter.
 */
class AmazonS3Adapter extends AbstractStorageAdapter
{
    /**
     * @var S3Client
     */
    private $client;

    /**
     * @var string
     */
    private $bucket;

    /**
     * Constructor.
     *
     * @param string   $basePath
     * @param S3Client $client
     * @param string   $bucket
     */
    public function __construct($basePath, S3Client $client, $bucket)
    {
        parent::__construct($basePath);

        $this->client = $client;
        $this->bucket = $bucket;
    }

    /**
     * {@inheritdoc}
     */
    public function getNextBatchId($type)
    {
        $result = $this->client->listObjects([
            'Bucket'    => $this->bucket,
            'Prefix'    => $this->getTypePath($type),
            'Delimiter' => '/',
        ]);

        if (!count($result['CommonPrefixes'])) {
            return 1;
        }

        $maxBatchId = 0;
        foreach ($result['CommonPrefixes'] as $batchMetadata) {
            $batchId = rtrim($batchMetadata['Prefix'], '/');
            if (preg_match('/(\d+)$/', $batchId, $m)) {
                $batchId    = (int) $m[0];
                $maxBatchId = max($maxBatchId, $batchId);
            }
        }

        return $maxBatchId + 1;
    }

    /**
     * {@inheritdoc}
     */
    public function hasBatch($type, $batchId)
    {
        return iterator_count($this->getBatchIterator($type, $batchId)) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function readBatch($type, $batchId)
    {
        $data = [];
        foreach ($this->getBatchIterator($type, $batchId) as $metadata) {
            $object = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key'    => $metadata['Key'],
            ]);

            $data[] = $object['Body']->getContents();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function hasModel($type, $batchId, $filename)
    {
        return $this->client->doesObjectExist($this->bucket, $this->getFilePath($type, $batchId, $filename));
    }

    /**
     * {@inheritdoc}
     */
    public function readModel($type, $batchId, $filename)
    {
        return $this->readFile($this->getFilePath($type, $batchId, $filename));
    }

    /**
     * {@inheritdoc}
     */
    public function writeModel($type, $batchId, $filename, $encodedData)
    {
        $this->writeFile($this->getFilePath($type, $batchId, $filename), $encodedData);
    }

    /**
     * {@inheritdoc}
     */
    public function readBatchConfig()
    {
        return $this->readFile($this->getBatchFilePath());
    }

    /**
     * {@inheritdoc}
     */
    public function writeBatchConfig($encodedData)
    {
        $this->writeFile($this->getBatchFilePath(), $encodedData);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastLogFile($maxSize)
    {
        $result = $this->client->listObjects([
            'Bucket' => $this->bucket,
            'Prefix' => $this->getLogsPath(),
        ]);

        if (!count($result['Contents'])) {
            return;
        }

        $lastFileName = null;
        foreach ($result['Contents'] as $fileMetadata) {
            if ($fileMetadata['Size'] > $maxSize) {
                continue;
            }

            $fileName = rtrim($fileMetadata['Key'], '/');
            if (preg_match('#([^/]+\.log)$#', $fileName, $m)) {
                $lastFileName = $m[0];
                break;
            }
        }

        return $lastFileName;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogFilenames()
    {
        $result = $this->client->listObjects([
            'Bucket' => $this->bucket,
            'Prefix' => $this->getLogsPath(),
        ]);

        if (!count($result['Contents'])) {
            return [];
        }

        $filenames = [];
        foreach ($result['Contents'] as $fileMetadata) {
            $filename = rtrim($fileMetadata['Key'], '/');
            if (preg_match('#([^/]+\.log)$#', $filename, $m)) {
                $filenames[] = $m[0];
                break;
            }
        }

        return $filenames;
    }

    /**
     * {@inheritdoc}
     */
    public function readLogFile($filename)
    {
        return $this->readFile($this->getLogFilePath($filename));
    }

    /**
     * {@inheritdoc}
     */
    public function writeLogFile($filename, $data)
    {
        $this->writeFile($this->getLogFilePath($filename), $data);
    }

    /**
     * {@inheritdoc}
     */
    public function clean()
    {
        $this->client->deleteMatchingObjects($this->bucket, $this->basePath);
    }

    /**
     * @param string $type
     * @param int    $batchId
     *
     * @return \Iterator
     */
    private function getBatchIterator($type, $batchId)
    {
        return $this->client->getIterator('ListObjects', [
            'Bucket'    => $this->bucket,
            'Prefix'    => $this->getBatchPath($type, $batchId),
            'Delimiter' => '/',
        ]);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function readFile($path)
    {
        try {
            $result = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key'    => $path,
            ]);

            return $result['Body']->getContents();
        } catch (S3Exception $e) {
            if ($e->getAwsErrorCode() === 'NoSuchKey') {
                return;
            }

            throw $e;
        }
    }

    /**
     * @param string $path
     * @param string $data
     */
    private function writeFile($path, $data)
    {
        $this->client->putObject([
            'Bucket'             => $this->bucket,
            'Body'               => $data,
            'Key'                => $path,
            'ContentType'        => 'application/json',
            'ContentDisposition' => 'attachment',
            'ACL'                => 'public-read',
        ]);
    }
}
