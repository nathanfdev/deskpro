<?php

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

        $s3_adapter  = null;
        $settingsBag = $container->get('settings_resolver')->getGlobalSettings();
        if ($settingsBag->get('core.filestorage_s3_key') && $settingsBag->get('core.filestorage_s3_secret') && $settingsBag->get('core.filestorage_s3_bucket')) {
            $cumulativeTimeout = $settingsBag->get('filestorage.s3.web.cumulative_timeout', 5);

            if (php_sapi_name() === 'cli') {
                // allow extra time for CLI upload
                // e.g. a bigger upload that was first saved to db on a web request that is now being moved
                $cumulativeTimeout = $settingsBag->get('filestorage.s3.cli.cumulative_timeout');
            }

            $s3_adapter = new AmazonS3Storage([
                's3_client'              => $container->get('amazon_s3_client'),
                'bucket'                 => $settingsBag->get('core.filestorage_s3_bucket'),
                'region'                 => $settingsBag->get('core.filestorage_s3_region'),
                'file_url_domain'        => $settingsBag->get('core.filestorage_s3_file_url_domain'),
                'file_url_template'      => $settingsBag->get('core.filestorage_s3_file_url_template'),
                'base_path'              => $settingsBag->get('core.filestorage_s3_basepath'),
                'fail_limit_per_request' => 1,
                'cumulative_timeout'     => $cumulativeTimeout,
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

        $bs = new DeskproBlobStorage($container->getEm(), $env->getUserTmpDir(), [
            'disable_physical_delete' => $settingsBag->get('core.filestorage_disable_physical_delete'),
        ]);
        $bs->setLogger($logger);

        if ($s3_adapter && $settingsBag->get('core.filestorage_method') == 's3') {
            $bs->addAdapter('s3', $s3_adapter);
            $bs->addAdapter('fs', $fs_adapter);
            $bs->addAdapter('db', $db_adapter);
            $bs->disableAdapter('fs');
        } elseif ($settingsBag->get('core.filestorage_method') == 'fs') {
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
        if ($log_adapter_id = $settingsBag->get('core.filestorage_method_logs')) {
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
