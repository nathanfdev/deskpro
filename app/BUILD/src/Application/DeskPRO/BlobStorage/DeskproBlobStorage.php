<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\BlobStorage;

use Application\DeskPRO\BlobStorage\StorageAdapter\AbstractStorageAdapter;
use Application\DeskPRO\Entity\Blob as BlobEntity;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use DpSys\LowError\SystemErrorHandler;
use GuzzleHttp;
use GuzzleHttp\Psr7;
use Orb\Data\ContentTypes;
use Orb\Log\Loggable;
use Orb\Log\Logger;
use Orb\Util\Arrays;
use Orb\Util\DpStrings;
use Orb\Util\Numbers;
use Orb\Util\Strings;

class DeskproBlobStorage implements Loggable
{
    /**
     * @var string
     */
    protected $preferred_adapter_id = null;

    /**
     * @var \Application\DeskPRO\BlobStorage\StorageAdapter\AbstractStorageAdapter[]
     */
    protected $adapters = [];

    /**
     * Map of adapterId => int of failure counts.
     *
     * @var array
     */
    protected $failed_adapters_counts = [];

    /**
     * @var bool
     */
    protected $ignore_fail_limits = false;

    /**
     * @var string[]
     */
    protected $disabled_adapters = [];

    /**
     * @var array
     */
    protected $tag_to_adapter = [];

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Orb\Log\Logger
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $enable_physical_delete = true;

    /**
     * @var string
     */
    protected $tmpDir = '';

    /**
     * @var array
     */
    protected $cachedFiles = [];

    /**
     * @param EntityManager $em
     * @param string        $tmpDir
     * @param array         $options
     */
    public function __construct(EntityManager $em, $tmpDir, array $options = [])
    {
        $this->adapters = [];
        $this->em       = $em;
        $this->db       = $em->getConnection();
        $this->logger   = new Logger();
        $this->tmpDir   = $tmpDir;

        if (isset($options['disable_physical_delete']) && $options['disable_physical_delete']) {
            $this->enable_physical_delete = false;
        }

        register_shutdown_function([$this, 'clearCache']);
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $tag
     * @param string $adapter_id
     */
    public function setAdapterForTag($tag, $adapter_id)
    {
        $this->tag_to_adapter[$tag] = $adapter_id;
    }

    /**
     * Checks to see if physical delete is enabled (it usually is).
     *
     * A "physical delete" means that the underlying datastore for a blob is removed.
     * For example, the actual file on the filesystem or object in S3.
     *
     * It's useful to disable it in cases where you dont want deletes to affect the real
     * data store for some reason (e.g. testing).
     *
     * @return bool
     */
    public function isPhysicalDeleteEnabled()
    {
        return $this->enable_physical_delete;
    }

    /**
     * @return bool
     */
    public function isIgnoreFailLimits()
    {
        return $this->ignore_fail_limits;
    }

    public function enableIgnoreFailLimits()
    {
        $this->ignore_fail_limits = true;
    }

    public function disableIgnoreFailLimits()
    {
        $this->ignore_fail_limits = false;
    }

    /**
     * Get the adapter for a tag.
     *
     * @param string $tag
     *
     * @return string
     */
    public function getAdapterIdForTag($tag)
    {
        return isset($this->tag_to_adapter[$tag]) ? $this->tag_to_adapter[$tag] : null;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param                        $id
     * @param AbstractStorageAdapter $adapter
     * @param int                    $priority
     */
    public function addAdapter($id, AbstractStorageAdapter $adapter, $priority = 0)
    {
        if ($this->preferred_adapter_id === null) {
            $this->preferred_adapter_id = $id;
        }

        $this->adapters[$id] = $adapter;
    }

    /**
     * @param string $id
     *
     * @throws BlobStorageException
     *
     * @return AbstractStorageAdapter
     */
    public function getAdapter($id)
    {
        if (!isset($this->adapters[$id])) {
            $this->logger->logError("[DeskproBlobStorage] (getAdapter) No adapter by id: $id");
            throw new BlobStorageException("No adapter by id `$id`", BlobStorageException::INVALID_ADAPTER_ID);
        }

        return $this->adapters[$id];
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasAdapter($id)
    {
        return isset($this->adapters[$id]);
    }

    /**
     * @return string[]
     */
    public function getAdapterIds()
    {
        return array_keys($this->adapters);
    }

    /**
     * @return AbstractStorageAdapter
     */
    public function getPreferredAdapter()
    {
        return $this->getAdapter($this->preferred_adapter_id);
    }

    /**
     * @return string
     */
    public function getPreferredAdapterId()
    {
        return $this->preferred_adapter_id;
    }

    /**
     * @param string $id
     *
     * @throws BlobStorageException
     */
    public function setPreferredAdapterId($id)
    {
        if (!isset($this->adapters[$id])) {
            throw new BlobStorageException("No adapter by id `$id`", BlobStorageException::INVALID_ADAPTER_ID);
        }

        $this->preferred_adapter_id = $id;
    }

    /**
     * Mark an adapter as disabled. Means it can still be used to read
     * blobs, but it wont be used when persisting.
     *
     * @param string $id
     */
    public function disableAdapter($id)
    {
        $this->disabled_adapters[$id] = true;
    }

    /**
     * @param string $filename
     * @param string $content_type
     * @param array  $props
     *
     * @return BlobEntity
     */
    private function _createBlobEntity($filename, $content_type, array $props = null)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (_createBlobEntity) Filename: $filename   ContentType: $content_type");

        $blob_entity = new BlobEntity();
        $blob_entity->setFilename($filename)->setContentType($content_type);

        if ($props) {
            if (isset($props['original_blob'])) {
                $blob_entity->setOriginalBlob($props['original_blob']);
            }
            if (isset($props['is_temp']) && $props['is_temp']) {
                $blob_entity->setIsTemp(true);
            }
            if (isset($props['sys_name']) && $props['sys_name']) {
                $blob_entity->setSysName($props['sys_name']);
            }
        }

        return $blob_entity;
    }

    /**
     * @param array $blob_array
     *
     * @return array
     */
    private function _getOrderedAdaptersForBlobArray(array $blob_array)
    {
        $ret = [];

        foreach ($this->adapters as $id => $ad) {
            if (isset($this->disabled_adapters[$id])) {
                continue;
            }
            $ret[$id] = $ad;
        }

        if (!empty($blob_array['storage_loc_pref']) && isset($ret[$blob_array['storage_loc_pref']])) {
            $ad = $ret[$blob_array['storage_loc_pref']];
            unset($ret[$blob_array['storage_loc_pref']]);
            Arrays::unshiftAssoc($ret, $blob_array['storage_loc_pref'], $ad);
        }

        return $ret;
    }

    /**
     * @param string $source_path
     * @param string $filename
     * @param string $content_type
     * @param array  $props
     *
     * @throws BlobStorageException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     *
     * @return int The blob ID that was created
     */
    public function createBlobRowFromFile($source_path, $filename, $content_type, array $props = null)
    {
        $this->logger->logDebug("[DeskproBlobStorage] BEGIN (saveBlobRecordFromFile) From path: $source_path");

        $blob_entity_tmp = $this->_createBlobEntity($filename, $content_type, $props);
        $blob_entity_tmp->setFilesize(filesize($source_path))->setBlobHash(md5_file($source_path));

        if (ContentTypes::isImageContentType($content_type) && $imageinfo = @getimagesize($source_path)) {
            $blob_entity_tmp->setDimensions($imageinfo);
        }

        if ($blob_entity_tmp->getFilesize() === 0 && $this->hasAdapter('db')) {
            $props['storage_loc_specific'] = 'db';
        }

        if ($props && !isset($props['storage_loc_specific']) && isset($props['tag'])) {
            $props['storage_loc_specific'] = $this->getAdapterIdForTag($props['tag']);
        }

        if ($props && isset($props['storage_loc_specific']) && $this->hasAdapter($props['storage_loc_specific'])) {
            $blob_entity_tmp->storage_loc_specific = $props['storage_loc_specific'];
            $blob_entity_tmp->storage_loc_pref     = $props['storage_loc_specific'];
        }

        $blob_array = $blob_entity_tmp->toDbArray();
        $this->db->insert('blobs', $blob_array);
        $blob_array['id']    = $this->db->lastInsertId();
        $blob_entity_tmp->id = $blob_array['id'];

        // We need the ID first to generate a proper unique filename/auth
        $batch = (int) (($blob_entity_tmp->id - 1) / 1000) + 1;
        $this->logger->logDebug("[DeskproBlobStorage] (saveBlobRecordFromFile) Blob ID: {$blob_entity_tmp->id}");

        // Now call the blob storages
        $blob = new Blob(
            $blob_entity_tmp->getFilename(),
            $blob_entity_tmp->getContentType(),
            [
                'blob_id' => $blob_entity_tmp->getId(),
            ]
        );

        $blob->setMeta('batch', $batch);

        $prev_e = null;

        /* @var $adapter \Application\DeskPRO\BlobStorage\StorageAdapter\AbstractStorageAdapter */
        foreach ($this->_getOrderedAdaptersForBlobArray($blob_array) as $adapter_id => $adapter) {
            if (
                !$this->ignore_fail_limits
                && isset($this->failed_adapters_counts[$adapter_id])
                && $adapter->getFailLimitPerRequest()
                && $adapter->getFailLimitPerRequest() >= $this->failed_adapters_counts[$adapter_id]
            ) {
                $this->logger->logWarn("[DeskproBlobStorage] (saveBlobRecordFromFile) Skipping $adapter_id because fail count of {$this->failed_adapters_counts[$adapter_id]} has met the limit of {$adapter->getFailLimitPerRequest()}");
                continue;
            }
            $this->logger->logDebug("[DeskproBlobStorage] (saveBlobRecordFromFile) Attempting adapter: $adapter_id");

            try {
                $authcode = $this->generateAuthCode($adapter_id, $blob_entity_tmp, $props);

                $blob->setMeta('authcode', $authcode);
                $path = $adapter->makePathForBlob($blob);
                $blob->setPath($path);
                $adapter->writeBlobFromFile($blob, $source_path);

                $blob_entity_tmp->save_path   = $path;
                $blob_entity_tmp->storage_loc = $adapter_id;

                if ($adapter->requiresTempCache() && $blob_entity_tmp->getFilesize()) {
                    $this->createCache($blob_entity_tmp, file_get_contents($source_path));
                }

                // Success, dont try others
                break;
            } catch (\Exception $e) {
                if (!isset($this->failed_adapters_counts[$adapter_id])) {
                    $this->failed_adapters_counts[$adapter_id] = 0;
                }
                ++$this->failed_adapters_counts[$adapter_id];

                $this->logger->logWarn("[DeskproBlobStorage] (saveBlobRecordFromFile) $adapter_id failed: {$e->getCode()} {$e->getMessage()}");
                if (isset($GLOBALS['DP_IS_MOVE_BLOBS_COMMAND'])) {
                    SystemErrorHandler::logException($e);
                }
                $prev_e = $e;
            }
        }

        // None of the succeeded, try to delete this half-inserted blob and then throw an error
        if (!$blob_entity_tmp->storage_loc) {
            $this->logger->logError('[DeskproBlobStorage] (saveBlobRecordFromFile) All adapters failed');

            $this->db->delete('blobs', $blob_array['id']);

            throw new BlobStorageException('Failed to store blob, no adapters succeeded', BlobStorageException::FAILED_BLOB_STORE, $prev_e);
        }

        $blob_entity_tmp->authcode = $blob->getMeta('authcode');

        if ($blob->getMeta('file_url')) {
            $blob_entity_tmp->file_url = $blob->getMeta('file_url');
        }

        if ($blob_entity_tmp->storage_loc_specific) {
            if ($blob_entity_tmp->storage_loc != $blob_entity_tmp->storage_loc_specific) {
                $blob_entity_tmp->storage_loc_pref = $blob_entity_tmp->storage_loc_specific;
            } else {
                $blob_entity_tmp->storage_loc_pref = null;
            }
        } else {
            if ($blob_entity_tmp->storage_loc != $this->preferred_adapter_id) {
                $blob_entity_tmp->storage_loc_pref = $this->preferred_adapter_id;
            } else {
                $blob_entity_tmp->storage_loc_pref = null;
            }
        }

        $blob_array  = array_merge($blob_array, $blob_entity_tmp->toDbArray());
        $blob_update = $blob_array;
        unset($blob_update['id']);

        $this->db->update('blobs', $blob_update, ['id' => $blob_array['id']]);

        $this->logger->logDebug('[DeskproBlobStorage] (saveBlobRecordFromFile) Save success');

        return $blob_array;
    }

    /**
     * @param string $source_path
     * @param string $filename
     * @param string $content_type
     * @param array  $props
     *
     * @throws \RuntimeException
     *
     * @return BlobEntity
     */
    public function createBlobRecordFromFile($source_path, $filename, $content_type, array $props = null)
    {
        $blob_info = $this->createBlobRowFromFile($source_path, $filename, $content_type, $props);
        $blob      = $this->em->find(BlobEntity::class, $blob_info['id']);

        return $blob;
    }

    /**
     * @param string $source_data
     * @param string $filename
     * @param string $content_type
     * @param array  $props
     *
     * @throws BlobStorageException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     *
     * @return mixed
     */
    public function createBlobRowFromString($source_data, $filename, $content_type, array $props = null)
    {
        $this->logger->logDebug('[DeskproBlobStorage] BEGIN (saveBlobRecordFromString) From data string '.Numbers::filesizeDisplay(strlen($source_data)));

        // TODO blobs need to have a separate field ot store "content encoding", so we can retain the original
        // filename/content type. right now we are essentially re-writing the file that is stored which
        // only works in some pretty specific cases where the implementation knows to handle gz files, or doesnt care (e.g. logs)
        if ($props && isset($props['prefer_gzipped']) && $props['prefer_gzipped'] && function_exists('gzencode')) {
            $this->logger->logDebug('Rewriting file to gzipped file');

            $filename     = $filename.'.gz';
            $content_type = 'application/gzip';
            $source_data  = gzencode($source_data);
        }

        $blob_entity_tmp = $this->_createBlobEntity($filename, $content_type, $props);
        $blob_entity_tmp->setFilesize(strlen($source_data));
        $blob_entity_tmp->setBlobHash(md5($source_data));

        if (ContentTypes::isImageContentType($content_type)) {
            $tmpfname = @tempnam(sys_get_temp_dir(), 'dpblob_');
            if ($tmpfname && @file_put_contents($tmpfname, $source_data)) {
                $imageinfo = @getimagesize($tmpfname);
                if ($imageinfo) {
                    $blob_entity_tmp->dim_w = $imageinfo[0];
                    $blob_entity_tmp->dim_h = $imageinfo[1];
                }
            }
            @unlink($tmpfname);
        }

        if ($blob_entity_tmp->getFilesize() === 0 && $this->hasAdapter('db')) {
            $props['storage_loc_specific'] = 'db';
        }

        if ($props && !isset($props['storage_loc_specific']) && isset($props['tag'])) {
            $props['storage_loc_specific'] = $this->getAdapterIdForTag($props['tag']);
        }

        if ($props && isset($props['storage_loc_specific']) && $this->hasAdapter($props['storage_loc_specific'])) {
            $blob_entity_tmp->setStorageLocSpecific($props['storage_loc_specific']);
            $blob_entity_tmp->setStorageLocPref($props['storage_loc_specific']);
        }

        $blob_array = $blob_entity_tmp->toDbArray();
        $this->db->insert('blobs', $blob_array);
        $blob_array['id']    = $this->db->lastInsertId();
        $blob_entity_tmp->id = $blob_array['id'];

        // We need the ID first to generate a proper unique filename/auth
        $batch = (int) (($blob_entity_tmp->getId() - 1) / 1000) + 1;
        $this->logger->logDebug("[DeskproBlobStorage] (saveBlobRecordFromString) Blob ID: {$blob_entity_tmp->getId()}");

        // Now call the blob storages
        $blob = new Blob(
            $blob_entity_tmp->getFilename(),
            $blob_entity_tmp->getContentType(),
            [
                'blob_id' => $blob_entity_tmp->getId(),
            ]
        );

        $blob->setMeta('batch', $batch);

        $prev_e = null;

        /* @var $adapter \Application\DeskPRO\BlobStorage\StorageAdapter\AbstractStorageAdapter */
        foreach ($this->_getOrderedAdaptersForBlobArray($blob_array) as $adapter_id => $adapter) {
            if (
                !$this->ignore_fail_limits
                && isset($this->failed_adapters_counts[$adapter_id])
                && $adapter->getFailLimitPerRequest()
                && $adapter->getFailLimitPerRequest() >= $this->failed_adapters_counts[$adapter_id]
            ) {
                $this->logger->logWarn("[DeskproBlobStorage] (saveBlobRecordFromFile) Skipping $adapter_id because fail count of {$this->failed_adapters_counts[$adapter_id]} has met the limit of {$adapter->getFailLimitPerRequest()}");
                continue;
            }
            $this->logger->logDebug("[DeskproBlobStorage] (saveBlobRecordFromString) Attempting adapter: $adapter_id");

            try {
                $authcode = $this->generateAuthCode($adapter_id, $blob_entity_tmp, $props);

                $blob->setMeta('authcode', $authcode);
                $path = $adapter->makePathForBlob($blob);
                $blob->setPath($path);
                $adapter->writeBlobString($blob, $source_data);

                $blob_entity_tmp->setSavePath($path);
                $blob_entity_tmp->setStorageLoc($adapter_id);

                if ($adapter->requiresTempCache() && $blob_entity_tmp->getFilesize()) {
                    $this->createCache($blob_entity_tmp, $source_data);
                }

                // Success, dont try others
                break;
            } catch (\Exception $e) {
                if (!isset($this->failed_adapters_counts[$adapter_id])) {
                    $this->failed_adapters_counts[$adapter_id] = 0;
                }
                ++$this->failed_adapters_counts[$adapter_id];

                $this->logger->logWarn("[DeskproBlobStorage] (saveBlobRecordFromString) $adapter_id failed: {$e->getCode()} {$e->getMessage()}");
                if (isset($GLOBALS['DP_IS_MOVE_BLOBS_COMMAND'])) {
                    SystemErrorHandler::logException($e);
                }
                $prev_e = $e;
            }
        }

        // None of the succeeded, try to delete this half-inserted blob and then throw an error
        if (!$blob_entity_tmp->getStorageLoc()) {
            $this->logger->logError('[DeskproBlobStorage] (saveBlobRecordFromString) All adapters failed');

            $this->db->delete('blobs', $blob_array['id']);

            throw new BlobStorageException('Failed to store blob, no adapters succeeded', BlobStorageException::FAILED_BLOB_STORE, $prev_e);
        }

        $blob_entity_tmp->setAuthCode($blob->getMeta('authcode'));

        if ($blob->getMeta('file_url')) {
            $blob_entity_tmp->setFileUrl($blob->getMeta('file_url'));
        }

        if ($blob_entity_tmp->getStorageLocSpecific()) {
            if ($blob_entity_tmp->getStorageLoc() != $blob_entity_tmp->getStorageLocSpecific()) {
                $blob_entity_tmp->setStorageLocPref($blob_entity_tmp->getStorageLocSpecific());
            } else {
                $blob_entity_tmp->setStorageLocPref(null);
            }
        } else {
            if ($blob_entity_tmp->getStorageLoc() != $this->preferred_adapter_id) {
                $blob_entity_tmp->setStorageLocPref($this->preferred_adapter_id);
            } else {
                $blob_entity_tmp->setStorageLocPref(null);
            }
        }

        $blob_array  = array_merge($blob_array, $blob_entity_tmp->toDbArray());
        $blob_update = $blob_array;
        unset($blob_update['id']);

        $this->db->update('blobs', $blob_update, ['id' => $blob_array['id']]);
        $this->logger->logDebug('[DeskproBlobStorage] (saveBlobRecordFromString) Save success');

        return $blob_array;
    }

    /**
     * @param string $source_data
     * @param string $filename
     * @param string $content_type
     * @param array  $props
     *
     * @throws BlobStorageException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return BlobEntity
     */
    public function createBlobRecordFromString($source_data, $filename, $content_type, array $props = null)
    {
        $blob_info = $this->createBlobRowFromString($source_data, $filename, $content_type, $props);
        $blob      = $this->em->find(BlobEntity::class, $blob_info['id']);

        return $blob;
    }

    /**
     * Read a blob into a string.
     *
     * @param Blob   $blob
     * @param string $adapter_id
     *
     * @throws \Exception
     *
     * @return string
     */
    public function copyBlobToString(Blob $blob, $adapter_id)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (getBlobString) Reading {$blob->getPath()} from $adapter_id");

        $adapter = $this->getAdapter($adapter_id);

        try {
            if (!$data = $this->pickFromCache($blob)) {
                $data = $adapter->readBlobString($blob);
            }
        } catch (\Exception $e) {
            $this->logger->logDebug("[DeskproBlobStorage] (getBlobString) Read failed: {$e->getCode()} {$e->getMessage()}");
            throw $e;
        }

        $this->logger->logDebug('[DeskproBlobStorage] (getBlobString) Read success: '.Numbers::filesizeDisplay(strlen($data)));

        return $data;
    }

    /**
     * Read a blob to a file.
     *
     * @param string $target_path
     * @param Blob   $blob
     * @param string $adapter_id
     *
     * @throws \Exception
     *
     * @return int
     */
    public function copyBlobToFile($target_path, Blob $blob, $adapter_id)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (saveBlobToFile) Saving {$blob->getPath()} from $adapter_id to $target_path");

        $adapter = $this->getAdapter($adapter_id);

        try {
            if ($data = $this->pickFromCache($blob)) {
                $data = file_put_contents($target_path, $data);
            } else {
                $data = $adapter->readBlobToFile($blob, $target_path);
            }
        } catch (\Exception $e) {
            $this->logger->logDebug("[DeskproBlobStorage] (saveBlobToFile) Save failed: {$e->getCode()} {$e->getMessage()}");
            throw $e;
        }

        $this->logger->logDebug('[DeskproBlobStorage] (getBlobString) Save success: '.Numbers::filesizeDisplay($data));

        return $data;
    }

    /**
     * @param BlobEntity $blob_entity
     *
     * @return null|string
     */
    public function copyBlobRecordToString(BlobEntity $blob_entity)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (readBlobStringFromRecord) Read blob record {$blob_entity->id} from {$blob_entity->storage_loc}");

        $data = $this->pickFromCache($blob_entity);

        // Can just use the public URL
        if (!$data && $blob_entity->file_url) {
            $this->logger->logDebug("[DeskproBlobStorage] (readBlobStringFromRecord) Attempting to fetch via URL: {$blob_entity->file_url}");
            $data = $this->downloadFileUrl($blob_entity->file_url);
            if (!$data || strlen($data) != $blob_entity->filesize) {
                $this->logger->logDebug('[DeskproBlobStorage] (readBlobStringFromRecord) Failed');
                $data = null;
            } else {
                $this->logger->logDebug("[DeskproBlobStorage] (readBlobStringFromRecord) Successfully read {$blob_entity->filesize} bytes");
            }
        }

        if (!$data) {
            $blob = $this->getBlobFromBlobRecord($blob_entity);
            $data = $this->copyBlobToString($blob, $blob_entity->storage_loc);
        }

        return $data;
    }

    /**
     * @param array $blob_row
     *
     * @return null|string
     */
    public function copyBlobRowToString(array $blob_row)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (copyBlobRowToString) Read blob row {$blob_row['id']} from {$blob_row['storage_loc']}");

        $data = $this->pickFromCache($blob_row);

        // Can just use the public URL
        if (!$data && $blob_row['file_url']) {
            $this->logger->logDebug("[DeskproBlobStorage] (readcopyBlobRowToString) Attempting to fetch via URL: {$blob_row['file_url']}");
            $data = $this->downloadFileUrl($blob_row['file_url']);
            if (!$data || strlen($data) != $blob_row['filesize']) {
                $this->logger->logDebug('[DeskproBlobStorage] (readcopyBlobRowToString) Failed');
                $data = null;
            } else {
                $this->logger->logDebug("[DeskproBlobStorage] (copyBlobRowToString) Successfully read {$blob_row['filesize']} bytes");
            }
        }

        if (!$data) {
            $blob = $this->getBlobFromBlobRow($blob_row);
            $data = $this->copyBlobToString($blob, $blob_row['storage_loc']);
        }

        return $data;
    }

    /**
     * @param string $url
     *
     * @return null|string
     */
    private function downloadFileUrl($url)
    {
        global $DP_ENV;

        if ($urlRewrites = $DP_ENV->getConfig('settings.remote_blobs_local_urlrewrite')) {
            foreach ($urlRewrites as $pattern => $replace) {
                $url = preg_replace($pattern, $replace, $url);
            }

            $this->logger->logDebug('URL rewritten to: '.$url);
        }

        $client = new HttpClient([
            GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => true,
            GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 4,
            GuzzleHttp\RequestOptions::TIMEOUT         => 10,
        ]);

        try {
            $response = $client->request('GET', $url);

            return Psr7\copy_to_string($response->getBody());
        } catch (\Exception $e) {
            $this->logger->logError(sprintf('Download file failed: [%s:%s] %s', get_class($e), $e->getCode(), substr($e->getMessage(), 0, 1000)));

            return null;
        }
    }

    /**
     * @param int $blob_row_id
     *
     * @throws BlobStorageException
     *
     * @return null|string
     */
    public function copyBlobRowIdToString($blob_row_id)
    {
        $blob_row = $this->db->fetchAssoc('SELECT * FROM blobs WHERE id = ?', [$blob_row_id]);
        if (!$blob_row) {
            throw new BlobStorageException("Could not find blob with ID $blob_row_id", BlobStorageException::INVALID_BLOB_ID);
        }

        return $this->copyBlobRowToString($blob_row);
    }

    /**
     * @param string     $target_path
     * @param BlobEntity $blob_entity
     *
     * @return int|null|string
     */
    public function copyBlobRecordToFile($target_path, BlobEntity $blob_entity)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (saveBlobStringToFile) Read blob record {$blob_entity->id} from {$blob_entity->storage_loc} to $target_path");

        $data = null;

        // Can just use the public URL
        if ($blob_entity->file_url) {
            $this->logger->logDebug("[DeskproBlobStorage] (saveBlobStringToFile) Attempting to fetch via URL: {$blob_entity->file_url}");

            $failed = false;
            if (!@copy($blob_entity->file_url, $target_path)) {
                $failed = true;
            }
            if (!$failed && filesize($target_path) != $blob_entity->filesize) {
                $failed = true;
            }

            if ($failed) {
                $this->logger->logDebug('[DeskproBlobStorage] (saveBlobStringToFile) Failed');
                $data = null;
            } else {
                $this->logger->logDebug("[DeskproBlobStorage] (saveBlobStringToFile) Successfully saved {$blob_entity->filesize} bytes");
                $data = $blob_entity->filesize;
            }
        }

        if (!$data) {
            $blob = $this->getBlobFromBlobRecord($blob_entity);
            $data = $this->copyBlobToFile($target_path, $blob, $blob_entity->storage_loc);
        }

        return $data;
    }

    /**
     * @param Blob   $blob
     * @param string $adapter_id
     * @param bool   $ex_on_error Throw an exception if there's an error (useful if you want raw exception from storage adapter)
     *                            Otherwise, you can still check error state based on the return value
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteBlob(Blob $blob, $adapter_id, $ex_on_error = false)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (deleteBlob) Deleting {$blob->getPath()} from $adapter_id");

        if (!$this->isPhysicalDeleteEnabled()) {
            $this->logger->logDebug('[DeskproBlobStorage] (deleteBlob) Delete request ignored because enable_physical_delete option is off');

            return;
        }

        try {
            $adapter = $this->getAdapter($adapter_id);
            $adapter->deleteBlob($blob);
        } catch (\Exception $e) {
            if ($e instanceof \PDOException) {
                SystemErrorHandler::logException($e);
            }

            $this->logger->logError("[DeskproBlobStorage] (deleteBlob) Delete failed: {$e->getCode()} {$e->getMessage()}");
            if ($ex_on_error) {
                throw $e;
            }

            return false;
        }

        $this->logger->logDebug('[DeskproBlobStorage] (deleteBlob) Delete success');

        return true;
    }

    /**
     * @param BlobEntity $blob_entity
     * @param bool       $ex_on_error Throw an exception if there's an error (useful if you want raw exception from storage adapter)
     *                                Otherwise, you can still check error state based on the return value
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteBlobRecord(BlobEntity $blob_entity, $ex_on_error = false)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (deleteBlobRecord) Deleting {$blob_entity->getId()} from {$blob_entity->storage_loc}");

        $blob = $this->getBlobFromBlobRecord($blob_entity);

        try {
            $this->deleteBlob($blob, $blob_entity->storage_loc);
            $this->em->remove($blob_entity);
            $this->em->flush();
        } catch (\Exception $e) {
            if ($e instanceof \PDOException || $e instanceof DBALException) {
                SystemErrorHandler::logException($e);
            }

            $this->logger->logError("[DeskproBlobStorage] (deleteBlobRecord) Delete failed: {$e->getCode()} {$e->getMessage()}");
            if ($ex_on_error) {
                throw $e;
            }

            return false;
        }

        $this->logger->logDebug('[DeskproBlobStorage] (deleteBlobRecord) Delete success');

        return true;
    }

    /**
     * @param array $blob_row
     * @param bool  $ex_on_error Throw an exception if there's an error (useful if you want raw exception from storage adapter)
     *                           Otherwise, you can still check error state based on the return value
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteBlobRow(array $blob_row, $ex_on_error = false)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (deleteBlobRow) Deleting {$blob_row['id']} from {$blob_row['storage_loc']}");

        $blob = $this->getBlobFromBlobRow($blob_row);

        try {
            $this->deleteBlob($blob, $blob_row['storage_loc']);
            $this->db->delete('blobs', ['id' => $blob_row['id']]);
        } catch (\Exception $e) {
            if ($e instanceof \PDOException || $e instanceof DBALException) {
                SystemErrorHandler::logException($e);
            }

            $this->logger->logError("[DeskproBlobStorage] (deleteBlobRow) Delete failed: {$e->getCode()} {$e->getMessage()}");
            if ($ex_on_error) {
                throw $e;
            }

            return false;
        }

        $this->logger->logDebug('[DeskproBlobStorage] (deleteBlobRow) Delete success');

        return true;
    }

    /**
     * @param int $blob_row_id
     *
     * @return null|string
     */
    public function deleteBlobRowId($blob_row_id)
    {
        $blob_row = $this->db->fetchAssoc('SELECT * FROM blobs WHERE id = ?', [$blob_row_id]);
        if (!$blob_row) {
            return;
        }

        return $this->deleteBlobRow($blob_row);
    }

    /**
     * @param BlobEntity $blob_entity
     *
     * @return Blob
     */
    public function getBlobFromBlobRecord(BlobEntity $blob_entity)
    {
        $blob = new Blob(
            $blob_entity->getFilename(),
            $blob_entity->getContentType(),
            [
                'blob_id' => $blob_entity->getId(),
            ]
        );
        $blob->setPath($blob_entity->getSavePath());
        if ($blob_entity->getFileUrl()) {
            $blob->setMeta('file_url', $blob_entity->getFileUrl());
        }

        return $blob;
    }

    /**
     * @param array $blob_row
     *
     * @throws \InvalidArgumentException
     *
     * @return Blob
     */
    public function getBlobFromBlobRow(array $blob_row)
    {
        $blob = new Blob(
            $blob_row['filename'],
            $blob_row['content_type'],
            [
                'blob_id' => $blob_row['id'],
            ]
        );
        $blob->setPath($blob_row['save_path']);
        if ($blob_row['file_url']) {
            $blob->setMeta('file_url', $blob_row['file_url']);
        }

        return $blob;
    }

    /**
     * @param BlobEntity $blob_entity
     * @param string     $adapter_id
     */
    public function moveBlobRecordToAdapter(BlobEntity $blob_entity, $adapter_id)
    {
        $old_blob       = $this->getBlobFromBlobRecord($blob_entity);
        $old_adapter_id = $blob_entity->storage_loc;

        $blob      = $this->getBlobFromBlobRecord($blob_entity);
        $file_data = $this->copyBlobRecordToString($blob_entity);

        $batch = (int) (($blob_entity->id - 1) / 1000) + 1;

        $authcode = $this->generateAuthCode(
            $adapter_id,
            $blob_entity,
            $blob_entity->isTicketAttachment() ? ['tag' => 'ticket_attachment'] : null
        );

        $blobauth_moved = [
            'old_authcode' => $blob_entity->getAuthcode(),
            'new_authcode' => $authcode,
            'filename'     => $blob_entity->getFilename(),
        ];

        $blob->setMeta('authcode', $authcode);
        $blob->setMeta('batch', $batch);

        $adapter = $this->getAdapter($adapter_id);
        $path    = $adapter->makePathForBlob($blob);
        $blob->setPath($path);
        $adapter->writeBlobString($blob, $file_data);

        $blob_entity->save_path   = $path;
        $blob_entity->storage_loc = $adapter_id;

        $blob_entity->authcode = $blob->getMeta('authcode');

        if ($blob->getMeta('file_url')) {
            $blob_entity->file_url = $blob->getMeta('file_url');
        }

        $this->em->persist($blob_entity);
        $this->em->flush();

        $this->db->insert('blobs_auth_moved', $blobauth_moved);

        // Delete the old one
        $this->deleteBlob($old_blob, $old_adapter_id);
    }

    public function generateAuthCode($adapterId, BlobEntity $blobEntity, array $props = null)
    {
        $authCode = null;
        $batch    = (int) (($blobEntity->id - 1) / 1000) + 1;

        if ($adapterId == 'fs') {
            $authCode = $batch.DpStrings::random(10, Strings::CHARS_KEY_ALPHA).$blobEntity->getId().$blobEntity->getNameHash();
        } else {
            $authCode = $blobEntity->getId().DpStrings::random(15, Strings::CHARS_KEY_ALPHA).'0';
        }

        $props = $props ?: [];
        if (isset($props['tag']) && $props['tag'] === 'ticket_attachment') {
            $authCode .= 'T';
        }

        return $authCode;
    }

    /**
     * @param BlobEntity $blob
     * @param            $fileData
     */
    public function createCache(BlobEntity $blob, $fileData)
    {
        $cache               = $this->getCachePath($blob);
        $this->cachedFiles[] = $cache;
        $result              = @file_put_contents($cache, $fileData);
        if (!$result) {
            $this->logger->logWarn(sprintf(
                '[DeskproBlobStorage] (createCache) Failed to create cache Filename: %s Adapter: %s',
                $cache, $this->getPreferredAdapterId()
            ));
        } else {
            $this->logger->logDebug(sprintf(
                '[DeskproBlobStorage] (createCache) Cache created Filename: %s Adapter: %s',
                $cache, $this->getPreferredAdapterId()
            ));
        }
    }

    /**
     * @param BlobEntity|Blob|array $blob
     *
     * @return string|null
     */
    public function pickFromCache($blob)
    {
        $data = null;
        if (isset($this->cachedFiles[$this->getCachePath($blob)])) {
            $filename = $this->cachedFiles[$this->getCachePath($blob)];
            $data     = is_file($filename) ? @file_get_contents($filename) : null;

            if ($data) {
                $this->logger->logDebug(sprintf(
                    '[DeskproBlobStorage] (pickFromCache) Filename: %s Adapter: %s',
                    $filename, $this->getPreferredAdapterId()
                ));
            } else {
                $this->logger->logWarn(sprintf(
                    '[DeskproBlobStorage] (pickFromCache) Failed to pick file from cache! Filename: %s Adapter: %s',
                    $filename, $this->getPreferredAdapterId()
                ));
            }
        }

        return $data ?: null;
    }

    /**
     * registered as shutdown function.
     */
    public function clearCache()
    {
        foreach ($this->cachedFiles as $file) {
            if (is_file($file)) {
                if (!$result = unlink($file)) {
                    $this->logger->logWarn(sprintf(
                        '[DeskproBlobStorage] (clearCache) Failed to unlink file! Filename: %s Adapter: %s',
                        $file, $this->getPreferredAdapterId()
                    ));
                }
            }
        }
    }

    /**
     * @param BlobEntity|Blob|array $blob
     *
     * @return string
     */
    protected function getCachePath($blob)
    {
        $filenameSafe = $blob instanceof BlobEntity || $blob instanceof Blob
            ? $blob->getId()
            : $blob['id'];

        return $this->tmpDir.DIRECTORY_SEPARATOR.$filenameSafe.'.blob';
    }
}
