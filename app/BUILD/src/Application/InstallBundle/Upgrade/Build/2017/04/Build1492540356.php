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
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Build1492540356 extends AbstractBuild implements SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $settingsBag = $this->container->get('settings_resolver')->getGlobalSettings();

        if ($settingsBag->get('core.filestorage_method') === 's3' && !$settingsBag->get('core.filestorage_s3_region')) {
            $s3Config = [
                'credentials' => [
                    'key'    => $settingsBag->get('core.filestorage_s3_key'),
                    'secret' => $settingsBag->get('core.filestorage_s3_secret'),
                ],
                'region'  => 'us-west-2', // sic(!) we are just using it as default cause we need one
                'version' => 'latest',
            ];

            try {
                $s3Client   = new S3Client($s3Config);
                $bucketName = $settingsBag->get('core.filestorage_s3_bucket');

                if ($bucketName && $bucketLocation = $s3Client->getBucketLocation(['Bucket' => $bucketName])->get('LocationConstraint')) {
                    $this->getDbConnection('default')->insert('settings', [
                        'name'  => 'core.filestorage_s3_region',
                        'value' => $bucketLocation,
                    ]);
                } else {
                    throw new \RuntimeException();
                }
            } catch (\Exception $e) {
                /** @var LoggerInterface $logger */
                $logger = $this->container->get('logger');
                $logger->log(
                    LogLevel::ERROR,
                    'We cant update your s3 settings automatically. 
                     Filestorage switched to db now. 
                     Please fix s3 config manually with Admin->Server->FileUploads section'
                );
                $this->getDbConnection('default')->update(
                    'settings',
                    ['value' => 'db'],
                    ['name'  => 'core.filestorage_method']
                );
            }
        }
    }
}
