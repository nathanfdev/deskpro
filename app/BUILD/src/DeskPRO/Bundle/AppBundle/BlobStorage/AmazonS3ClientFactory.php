<?php

namespace DeskPRO\Bundle\AppBundle\BlobStorage;

use Application\DeskPRO\NewSettings\SettingsResolver;
use Aws\DoctrineCacheAdapter;
use Aws\S3\S3Client;
use Doctrine\Common\Cache\FilesystemCache;

/**
 * Class AmazonS3ClientFactory.
 */
class AmazonS3ClientFactory
{
    /**
     * @param SettingsResolver $settingsResolver
     * @param string           $tmpDir           Used to cache credentials fetched from EC2 instance
     *
     * @return S3Client
     */
    public static function create(SettingsResolver $settingsResolver, $tmpDir)
    {
        if (!defined('CURLOPT_CONNECTTIMEOUT')) {
            define(CURLOPT_CONNECTTIMEOUT, 78);
        }
        if (!defined('CURLOPT_TIMEOUT')) {
            define(CURLOPT_TIMEOUT, 13);
        }

        $settingsBag    = $settingsResolver->getGlobalSettings();
        $connectTimeout = $settingsBag->get('filestorage.s3.web.connect_timeout', 2);
        $timeout        = $settingsBag->get('filestorage.s3.web.upload_timeout', 4);

        if (php_sapi_name() === 'cli') {
            // allow extra time for CLI upload
            // e.g. a bigger upload that was first saved to db on a web request that is now being moved
            $connectTimeout = $settingsBag->get('filestorage.s3.cli.connect_timeout', 4);
            $timeout        = $settingsBag->get('filestorage.s3.cli.upload_timeout', 10);
        }

        $s3Config = [
//            'credentials' => [
//                'key'    => $settingsBag->get('core.filestorage_s3_key'),
//                'secret' => $settingsBag->get('core.filestorage_s3_secret'),
//            ],
            'region'          => $settingsBag->get('core.filestorage_s3_region', 'us-east-1'),
            'version'         => 'latest',
            'request.options' => [
                'connect_timeout' => $connectTimeout,
                'timeout'         => $timeout,
            ],
            'curl.options' => [
                CURLOPT_CONNECTTIMEOUT => $connectTimeout,
                CURLOPT_TIMEOUT        => $timeout,
            ],
        ];

        if ($endpoint = $settingsBag->get('core.filestorage_s3_endpoint')) {
            if ($endpoint && !parse_url($endpoint, PHP_URL_SCHEME)) {
                $endpoint = 'https://'.$endpoint;
            }
            $s3Config['endpoint'] = $endpoint;
        }

        if (strtolower($settingsBag->get('core.filestorage_s3_credentials_source', '')) === 'ec2') {
            $cacheAdapter            = new DoctrineCacheAdapter(new FilesystemCache($tmpDir));
            $s3Config['credentials'] = $cacheAdapter;
        } else {
            $s3Config['credentials'] = [
                'key'    => $settingsBag->get('core.filestorage_s3_key'),
                'secret' => $settingsBag->get('core.filestorage_s3_secret'),
            ];
        }

        return new S3Client($s3Config);
    }
}
