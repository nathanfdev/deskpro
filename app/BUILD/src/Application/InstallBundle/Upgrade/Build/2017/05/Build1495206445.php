<?php

namespace Application\InstallBundle\Upgrade\Build;

use Aws\S3\S3Client;
use DpSys\LowError\SystemErrorHandler;

// This is a blocking build for the possibility that the s3 adapter may
// revert to db after if the region lookup fails

class Build1495206445 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        if (defined('DPC_IS_CLOUD')) {
            return;
        }

        $settings = $this->readMultiSetting([
            'core.filestorage_s3_region',
            'core.filestorage_s3_bucket',
            'core.filestorage_s3_key',
            'core.filestorage_s3_secret',
            'core.filestorage_method',
        ]);

        if ($settings['core.filestorage_method'] === 's3' && !$settings['core.filestorage_s3_region']) {
            $s3Config = [
                'credentials' => [
                    'key'    => $settings['core.filestorage_s3_key'],
                    'secret' => $settings['core.filestorage_s3_secret'],
                ],
                'region'  => 'us-west-2', // sic(!) we are just using it as default cause we need one
                'version' => 'latest',
            ];

            try {
                $s3Client   = new S3Client($s3Config);
                $bucketName = $settings['core.filestorage_s3_bucket'];

                if ($bucketName && $bucketLocation = $s3Client->getBucketLocation(['Bucket' => $bucketName])->get('LocationConstraint')) {
                    $this->saveSetting('core.filestorage_s3_region', $bucketLocation);
                } else {
                    throw new \RuntimeException();
                }
            } catch (\Exception $e) {
                $e = new \Exception(
                    'We cant update your s3 settings automatically. '
                     .'Filestorage switched to db now. '
                     .'Please fix s3 config manually with Admin->Server->FileUploads section'
                );
                SystemErrorHandler::logException($e);
                $this->out($e->getMessage());
                $this->saveSetting('core.filestorage_method', 'db');
            }
        }
    }
}
