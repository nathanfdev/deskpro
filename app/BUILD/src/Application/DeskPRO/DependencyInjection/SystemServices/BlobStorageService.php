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

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\BlobStorage\StorageAdapter\AmazonS3Storage;
use Application\DeskPRO\BlobStorage\StorageAdapter\DatabaseStorage;
use Application\DeskPRO\BlobStorage\StorageAdapter\FilesystemStorage;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Aws\S3\S3Client;
use Orb\Log\Logger;

class BlobStorageService
{
    public static function create(DeskproContainer $container)
    {
        //------------------------------
        // Create a logger
        //------------------------------

        $env = $container->get('deskpro.app_env');

        $logger = new Logger();

        if (!$env->getConfig('logs.enable_blobstorage_log')) {
            $logger->addFilter(new \Orb\Log\Filter\PriorityFilter(Logger::WARN));
        }

        $wr = new \Orb\Log\Writer\Stream($env->getUserLogsDir().DIRECTORY_SEPARATOR.'blob_storage.log');
        $logger->addWriter($wr);

        //------------------------------
        // Filesystem adapter
        //------------------------------

        $opts = ['base_path' => $container->getBlobDir()];
        if ($container->getSetting('core.filestorage_file_mode')) {
            $opts['file_mode'] = $container->getSetting('core.filestorage_file_mode');
        }
        if ($container->getSetting('core.filestorage_dir_mode')) {
            $opts['dir_mode'] = $container->getSetting('core.filestorage_dir_mode');
        }

        $fs_adapter = new FilesystemStorage($opts);
        $fs_adapter->setLogger($logger);

        //------------------------------
        // S3 Adapter
        //------------------------------

        $s3_adapter = null;
        if ($container->getSetting('core.filestorage_s3_key') && $container->getSetting('core.filestorage_s3_secret') && $container->getSetting('core.filestorage_s3_bucket')) {
            if (!defined('CURLOPT_CONNECTTIMEOUT')) {
                define(CURLOPT_CONNECTTIMEOUT, 78);
            }
            if (!defined('CURLOPT_TIMEOUT')) {
                define(CURLOPT_TIMEOUT, 13);
            }

            $client = S3Client::factory([
                'key'             => $container->getSetting('core.filestorage_s3_key'),
                'secret'          => $container->getSetting('core.filestorage_s3_secret'),
                'request.options' => [
                    'connect_timeout' => 15,
                    'timeout'         => 120,
                ],
                'curl.options' => [
                    CURLOPT_CONNECTTIMEOUT => 15,
                    CURLOPT_TIMEOUT        => 120,
                ],
            ]);
            $s3_adapter = new AmazonS3Storage([
                's3_client'       => $client,
                'bucket'          => $container->getSetting('core.filestorage_s3_bucket'),
                'file_url_domain' => $container->getSetting('core.filestorage_s3_file_url_domain'),
                'base_path'       => $container->getSetting('core.filestorage_s3_basepath'),
            ]);
            $s3_adapter->setLogger($logger);
        }

        //------------------------------
        // Database adapter
        //------------------------------

        $db_adapter = new DatabaseStorage([
            'db'                   => $container->getDb(),
            'table'                => 'blobs_storage',
            'field_name.data'      => 'data',
            'field_name.path'      => 'blob_id',
            'field_name.order'     => 'id',
            'metadata_id_property' => 'blob_id',
        ]);
        $db_adapter->setLogger($logger);

        //------------------------------
        // Create the storage
        //------------------------------

        $bs = new DeskproBlobStorage($container->getEm());
        $bs->setLogger($logger);

        if ($s3_adapter && $container->getSetting('core.filestorage_method') == 's3') {
            $bs->addAdapter('s3', $s3_adapter);
            $bs->addAdapter('fs', $fs_adapter);
            $bs->addAdapter('db', $db_adapter);
            $bs->disableAdapter('fs');
        } elseif ($container->getSetting('core.filestorage_method') == 'fs') {
            $bs->addAdapter('fs', $fs_adapter);
            if ($s3_adapter) {
                $bs->addAdapter('s3', $s3_adapter);
                $bs->disableAdapter('s3');
            }
            $bs->addAdapter('db', $db_adapter);
        } else {
            $bs->addAdapter('db', $db_adapter);
            $bs->addAdapter('fs', $fs_adapter);
            $bs->disableAdapter('fs', $fs_adapter);
            if ($s3_adapter) {
                $bs->addAdapter('s3', $s3_adapter);
                $bs->disableAdapter('s3');
            }
        }

        // Store logs in the database if config flag is set
        if ($log_adapter_id = $container->getSetting('core.filestorage_method_logs')) {
            $bs->setAdapterForTag('logs.email_source_log', $log_adapter_id);
            $bs->setAdapterForTag('logs.sendmail_source_log', $log_adapter_id);
            $bs->setAdapterForTag('logs.ticket_proc_log', $log_adapter_id);
        }

        // Use local storage for CSS because we need to read it
        // from local domain for paths to resolve properly
        $bs->setAdapterForTag('brand_asset.custom_style', 'db');
        $bs->setAdapterForTag('brand_asset.main', 'db');
        $bs->setAdapterForTag('brand_asset.portal_css', 'db');
        $bs->setAdapterForTag('brand_asset.portal_rtl_css', 'db');

        return $bs;
    }
}
