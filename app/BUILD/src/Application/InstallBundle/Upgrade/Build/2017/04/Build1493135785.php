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

namespace Application\InstallBundle\Upgrade\Build;

use Aws\S3\S3Client;
use DpSys\LowError\SystemErrorHandler;

// This is a blocking build for the possibility that the fs adapter may
// revert to db after if the region lookup fails

class Build1493135785 extends AbstractBuild implements BlockingBuildInterface
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
