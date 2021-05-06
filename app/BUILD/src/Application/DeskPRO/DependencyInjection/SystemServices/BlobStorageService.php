<?php

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\BlobStorage\StorageAdapter\AmazonS3Storage;
use Application\DeskPRO\BlobStorage\StorageAdapter\DatabaseStorage;
use Application\DeskPRO\BlobStorage\StorageAdapter\FilesystemStorage;
use Application\DeskPRO\BlobStorage\StorageAdapter\WebDAVStorage;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use DpSys\LowError\SystemErrorHandler;
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

        if (SystemErrorHandler::useSyslog()) {
            $logger->addWriter(new \Orb\Log\Writer\Syslog('deskpro-blobstorage'));
        } else {
            $logger->addWriter(new \Orb\Log\Writer\Stream($env->getUserLogsDir().DIRECTORY_SEPARATOR.'blob_storage.log'));
        }

        //------------------------------
        // Create the storage
        //------------------------------
        $settingsBag = $container->get('settings_resolver')->getGlobalSettings();

        $bs = new DeskproBlobStorage($container->getEm(), $env->getUserTmpDir(), [
            'disable_physical_delete' => $settingsBag->get('core.filestorage_disable_physical_delete'),
        ]);
        $bs->setLogger($logger);

        $fsAdapter  = self::createFsAdapter($container, $logger);
        $s3Adapter  = self::createS3Adapter($container, $logger);
        $davAdapter = self::createDavAdapter($container, $logger);
        $dbAdapter  = self::createDbAdapter($container, $logger);

        $bs->addAdapter('fs', $fsAdapter);
        $bs->addAdapter('db', $dbAdapter);

        if ($s3Adapter) {
            $bs->addAdapter('s3', $s3Adapter);
        }
        if ($davAdapter) {
            $bs->addAdapter('dav', $davAdapter);
        }

        if ($settingsBag->get('core.filestorage_method') == 's3') {
            $adaptersOrder = ['s3', 'db', 'fs', 'dav'];
            $bs->setPreferredAdapterId('s3');
            $bs->disableAdapter('fs');
            $bs->disableAdapter('dav');
        } elseif ($settingsBag->get('core.filestorage_method') == 'fs') {
            $adaptersOrder = ['fs', 'db', 's3', 'dav'];
            $bs->setPreferredAdapterId('fs');
            $bs->disableAdapter('s3');
            $bs->disableAdapter('dav');
        } elseif ($settingsBag->get('core.filestorage_method') == 'dav') {
            $adaptersOrder = ['dav', 'db', 's3', 'fs'];
            $bs->setPreferredAdapterId('dav');
            $bs->disableAdapter('s3');
            $bs->disableAdapter('fs');
        } else {
            $adaptersOrder = ['db', 'fs', 's3', 'dav'];
            $bs->setPreferredAdapterId('db');
            $bs->disableAdapter('fs');
            $bs->disableAdapter('s3');
            $bs->disableAdapter('dav');
        }
        $bs->setAdaptersOrder($adaptersOrder);

        // Store logs in the database if config flag is set
        if ($log_adapter_id = $settingsBag->get('core.filestorage_method_logs')) {
            // forcefully enable adapter for logs
            $bs->enableAdapter($log_adapter_id);
            $bs->setAdapterForTag('logs.email_source_log', $log_adapter_id);
            $bs->setAdapterForTag('logs.sendmail_source_log', $log_adapter_id);
            $bs->setAdapterForTag('logs.ticket_proc_log', $log_adapter_id);
        }

        // Force 'db' adapter for Download attachments if S3 and Download protection enabled
        if ($settingsBag->get('user.attachment_require_auth_downloads')
            && $settingsBag->get('core.filestorage_method') == 's3') {
            $bs->setAdapterForTag(DeskproBlobStorage::TAG_DOWNLOAD_ATTACHMENT, 'db');
        }

        // Force 'db' adapter for Download attachments if DAV and Download protection enabled
        if ($settingsBag->get('user.attachment_require_auth_downloads')
            && $settingsBag->get('core.filestorage_method') == 'dav') {
            $bs->setAdapterForTag(DeskproBlobStorage::TAG_DOWNLOAD_ATTACHMENT, 'db');
        }

        // Use local storage for CSS because we need to read it
        // from local domain for paths to resolve properly
        $bs->setAdapterForTag('brand_asset.custom_style', 'db');
        $bs->setAdapterForTag('brand_asset.main', 'db');
        $bs->setAdapterForTag('brand_asset.portal_css', 'db');
        $bs->setAdapterForTag('brand_asset.portal_rtl_css', 'db');
        $bs->setAdapterForTag('apps.asset', 'db');

        return $bs;
    }

    /**
     * @param $container
     * @param $logger
     *
     * @return FilesystemStorage
     */
    private static function createFsAdapter(DeskproContainer $container, Logger $logger)
    {
        $opts = ['base_path' => $container->getBlobDir()];
        if ($container->getSetting('core.filestorage_file_mode')) {
            $opts['file_mode'] = $container->getSetting('core.filestorage_file_mode');
        }
        if ($container->getSetting('core.filestorage_dir_mode')) {
            $opts['dir_mode'] = $container->getSetting('core.filestorage_dir_mode');
        }

        $fsAdapter = new FilesystemStorage($opts);
        $fsAdapter->setLogger($logger);

        return $fsAdapter;
    }

    /**
     * @param        $container
     * @param Logger $logger
     *
     * @return AmazonS3Storage|null
     */
    private static function createS3Adapter($container, Logger $logger)
    {
        $settingsBag = $container->get('settings_resolver')->getGlobalSettings();

        $s3Adapter  = null;

        if (
            ($settingsBag->get('core.filestorage_s3_key') && $settingsBag->get('core.filestorage_s3_secret') && $settingsBag->get('core.filestorage_s3_bucket'))
            || ($settingsBag->get('core.filestorage_s3_bucket') && $settingsBag->get('core.filestorage_s3_credentials_source') == 'ec2')
        ) {
            $cumulativeTimeout = $settingsBag->get('filestorage.s3.web.cumulative_timeout', 5);

            if (php_sapi_name() === 'cli') {
                // allow extra time for CLI upload
                // e.g. a bigger upload that was first saved to db on a web request that is now being moved
                $cumulativeTimeout = $settingsBag->get('filestorage.s3.cli.cumulative_timeout');
            }

            $s3Adapter = new AmazonS3Storage([
                's3_client'              => $container->get('amazon_s3_client'),
                'bucket'                 => $settingsBag->get('core.filestorage_s3_bucket'),
                'region'                 => $settingsBag->get('core.filestorage_s3_region'),
                'file_url_domain'        => $settingsBag->get('core.filestorage_s3_file_url_domain'),
                'file_url_template'      => $settingsBag->get('core.filestorage_s3_file_url_template'),
                'base_path'              => $settingsBag->get('core.filestorage_s3_basepath'),
                'fail_limit_per_request' => 1,
                'cumulative_timeout'     => $cumulativeTimeout,
            ]);
            $s3Adapter->setLogger($logger);
        }

        return $s3Adapter;
    }

    /**
     * @param DeskproContainer $container
     * @param Logger           $logger
     *
     * @throws \Exception
     *
     * @return WebDAVStorage|null
     */
    private static function createDavAdapter(DeskproContainer $container, Logger $logger)
    {
        $settingsBag = $container->get('settings_resolver')->getGlobalSettings();

        $davAdapter = null;
        if ($settingsBag->get('core.filestorage_dav_host')
            && $settingsBag->get('core.filestorage_dav_port')
        ) {
            $davAdapter = new WebDAVStorage([
                'dav' => self::createDavClient(
                    $settingsBag->get('core.filestorage_dav_host'),
                    $settingsBag->get('core.filestorage_dav_port'),
                    $settingsBag->get('core.filestorage_dav_username'),
                    $settingsBag->get('core.filestorage_dav_password')
                ),
            ]);
            $davAdapter->setLogger($logger);
        }

        return $davAdapter;
    }

    /**
     * @param string $host
     * @param string $port
     * @param string|null $username
     * @param string|null $password
     *
     * @return \Sabre\DAV\Client
     */
    public static function createDavClient($host, $port, $username = null, $password = null)
    {
        $uri = 'http://'
            .$host
            .':'.$port
        ;
        $params = [
            'baseUri'  => $uri,
        ];
        if ($username) {
            $params = array_merge($params, [
                'userName' => $username,
                'password' => $password,
            ]);
        }

        return new \Sabre\DAV\Client($params);
    }

    /**
     * @param DeskproContainer $container
     * @param Logger           $logger
     *
     * @return DatabaseStorage
     */
    private static function createDbAdapter(DeskproContainer $container, Logger $logger)
    {
        $dbAdapter = new DatabaseStorage([
            'db'                   => $container->getDb(),
            'table'                => 'blobs_storage',
            'field_name.data'      => 'data',
            'field_name.path'      => 'blob_id',
            'field_name.order'     => 'id',
            'metadata_id_property' => 'blob_id',
        ]);
        $dbAdapter->setLogger($logger);

        return $dbAdapter;
    }
}
