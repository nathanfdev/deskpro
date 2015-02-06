<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\BlobStorage;

use Application\DeskPRO\BlobStorage\StorageAdapter\AbstractStorageAdapter;
use Application\DeskPRO\Entity\Blob as BlobEntity;
use DeskPRO\Kernel\KernelErrorHandler;
use Doctrine\ORM\EntityManager;
use Orb\Data\ContentTypes;
use Orb\Log\Loggable;
use Orb\Log\Logger;
use Orb\Util\Arrays;
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
    protected $adapters = array();

    /**
     * @var string[]
     */
    protected $disabled_adapters = array();

    /**
     * @var array
     */
    protected $tag_to_adapter = array();

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
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->adapters = array();
        $this->em = $em;
        $this->db = $em->getConnection();
        $this->logger = new Logger();
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
     * Get the adapter for a tag
     *
     * @param  string $tag
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
     * @param  string                    $id
     * @return AbstractStorageAdapter
     * @throws \InvalidArgumentException
     */
    public function getAdapter($id)
    {
        if (!isset($this->adapters[$id])) {
            $this->logger->logError("[DeskproBlobStorage] (getAdapter) No adapter by id: $id");
            throw new \InvalidArgumentException("No adapter by id `$id`");
        }

        return $this->adapters[$id];
    }


    /**
     * @param  string $id
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
     * @param  string                    $id
     * @throws \InvalidArgumentException
     */
    public function setPreferredAdapterId($id)
    {
        if (!isset($this->adapters[$id])) {
            throw new \InvalidArgumentException("No adapter by id `$id`");
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
     * @param  string                           $filename
     * @param  string                           $content_type
     * @param  array                            $props
     * @return \Application\DeskPRO\Entity\Blob
     */
    private function _createBlobEntity($filename, $content_type, array $props = null)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (_createBlobEntity) Filename: $filename   ContentType: $content_type");

        $blob_entity = new BlobEntity();
        $blob_entity->filename     = $filename;
        $blob_entity->content_type = $content_type;

        if ($props) {
            if (isset($props['original_blob'])) {
                $blob_entity->original_blob = $props['original_blob'];
            }
            if (isset($props['is_temp']) && $props['is_temp']) {
                $blob_entity->is_temp = true;
            }
            if (isset($props['sys_name']) && $props['sys_name']) {
                $blob_entity->sys_name = $props['sys_name'];
            }
        }

        return $blob_entity;
    }


    /**
     * @param  array $blob_array
     * @return array
     */
    private function _getOrderedAdaptersForBlobArray(array $blob_array)
    {
        $ret = array();

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
     * @param  string            $source_path
     * @param  string            $filename
     * @param  string            $content_type
     * @param  array             $props
     * @return int               The blob ID that was created
     * @throws \RuntimeException
     */
    public function createBlobRowFromFile($source_path, $filename, $content_type, array $props = null)
    {
        $this->logger->logDebug("[DeskproBlobStorage] BEGIN (saveBlobRecordFromFile) From path: $source_path");

        $blob_entity_tmp = $this->_createBlobEntity($filename, $content_type, $props);
        $blob_entity_tmp->filesize     = filesize($source_path);
        $blob_entity_tmp->blob_hash    = md5_file($source_path);

        if (ContentTypes::isImageContentType($content_type)) {
            $imageinfo = @getimagesize($source_path);
            if ($imageinfo) {
                $blob_entity_tmp->dim_w = $imageinfo[0];
                $blob_entity_tmp->dim_h = $imageinfo[1];
            } else {
                throw new \RuntimeException("Attempting to save invalid image");
            }
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
        $blob_array['id'] = $this->db->lastInsertId();
        $blob_entity_tmp->id = $blob_array['id'];

        // We need the ID first to generate a proper unique filename/auth
        $batch = (int)(($blob_entity_tmp->id-1) / 1000) + 1;
        $this->logger->logDebug("[DeskproBlobStorage] (saveBlobRecordFromFile) Blob ID: {$blob_entity_tmp->id}");

        // Now call the blob storages
        $blob = new Blob(
            $blob_entity_tmp->filename,
            $blob_entity_tmp->content_type,
            array(
                'blob_id' => $blob_entity_tmp->id
            )
        );

        $blob->setMeta('batch', $batch);

        $prev_e = null;
        foreach ($this->_getOrderedAdaptersForBlobArray($blob_array) as $adapter_id => $adapter) {

            $this->logger->logDebug("[DeskproBlobStorage] (saveBlobRecordFromFile) Attempting adapter: $adapter_id");

            /** @var $adapter \Application\DeskPRO\BlobStorage\StorageAdapter\AbstractStorageAdapter */
            try {
                if ($adapter_id == 'fs') {
                    $authcode = $batch  . Strings::random(10, Strings::CHARS_KEY_ALPHA) . $blob_entity_tmp->getId() . $blob_entity_tmp->getNameHash();
                } else {
                    $authcode = $blob_entity_tmp->getId() . Strings::random(15, Strings::CHARS_KEY_ALPHA) . '0';
                }

                $blob->setMeta('authcode', $authcode);
                $path = $adapter->makePathForBlob($blob);
                $blob->setPath($path);
                $adapter->writeBlobFromFile($blob, $source_path);

                $blob_entity_tmp->save_path = $path;
                $blob_entity_tmp->storage_loc = $adapter_id;

                // Success, dont try others
                break;
            } catch (\Exception $e) {
                $this->logger->logWarn("[DeskproBlobStorage] (saveBlobRecordFromFile) $adapter_id failed: {$e->getCode()} {$e->getMessage()}");
                if (isset($GLOBALS['DP_IS_MOVE_BLOBS_COMMAND'])) KernelErrorHandler::logException($e);
                $prev_e = $e;
            }
        }

        // None of the succeeded, try to delete this half-inserted blob and then throw an error
        if (!$blob_entity_tmp->storage_loc) {
            $this->logger->logError("[DeskproBlobStorage] (saveBlobRecordFromFile) All adapters failed");

            $this->db->delete('blobs', $blob_array['id']);

            throw new \RuntimeException("Failed to store blob, no adapters succeeded", 1, $prev_e);
        }

        $blob_entity_tmp->authcode  = $blob->getMeta('authcode');

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

        $blob_array = array_merge($blob_array, $blob_entity_tmp->toDbArray());
        $blob_update = $blob_array;
        unset($blob_update['id']);

        $this->db->update('blobs', $blob_update, array('id' => $blob_array['id']));

        $this->logger->logDebug("[DeskproBlobStorage] (saveBlobRecordFromFile) Save success");

        return $blob_array;
    }


    /**
     * @param  string            $source_path
     * @param  string            $filename
     * @param  string            $content_type
     * @param  array             $props
     * @return BlobEntity
     * @throws \RuntimeException
     */
    public function createBlobRecordFromFile($source_path, $filename, $content_type, array $props = null)
    {
        $blob = $this->createBlobRowFromFile($source_path, $filename, $content_type, $props);
        $blob = $this->em->find('DeskPRO:Blob', $blob['id']);

        return $blob;
    }


    /**
     * @param  string            $source_data
     * @param  string            $filename
     * @param  string            $content_type
     * @param  array             $props
     * @return array
     * @throws \RuntimeException
     */
    public function createBlobRowFromString($source_data, $filename, $content_type, array $props = null)
    {
        $this->logger->logDebug("[DeskproBlobStorage] BEGIN (saveBlobRecordFromString) From data string " . Numbers::filesizeDisplay(strlen($source_data)));

        $blob_entity_tmp = $this->_createBlobEntity($filename, $content_type, $props);
        $blob_entity_tmp->filesize  = strlen($source_data);
        $blob_entity_tmp->blob_hash = md5($source_data);

        if (ContentTypes::isImageContentType($content_type)) {
            $tmpfname = @tempnam(sys_get_temp_dir(), "dpblob_");
            if ($tmpfname && @file_put_contents($tmpfname, $source_data)) {
                $imageinfo = @getimagesize($tmpfname);
                if ($imageinfo) {
                    $blob_entity_tmp->dim_w = $imageinfo[0];
                    $blob_entity_tmp->dim_h = $imageinfo[1];
                } else {
                    throw new \RuntimeException("Attempting to save invalid image");
                }
            }
            @unlink($tmpfname);
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
        $blob_array['id'] = $this->db->lastInsertId();
        $blob_entity_tmp->id = $blob_array['id'];

        // We need the ID first to generate a proper unique filename/auth
        $batch = (int)(($blob_entity_tmp->id-1) / 1000) + 1;
        $this->logger->logDebug("[DeskproBlobStorage] (saveBlobRecordFromString) Blob ID: {$blob_entity_tmp->id}");

        // Now call the blob storages
        $blob = new Blob(
            $blob_entity_tmp->filename,
            $blob_entity_tmp->content_type,
            array(
                'blob_id' => $blob_entity_tmp->id
            )
        );

        $blob->setMeta('batch', $batch);

        $prev_e = null;
        foreach ($this->_getOrderedAdaptersForBlobArray($blob_array) as $adapter_id => $adapter) {

            $this->logger->logDebug("[DeskproBlobStorage] (saveBlobRecordFromString) Attempting adapter: $adapter_id");

            /** @var $adapter \Application\DeskPRO\BlobStorage\StorageAdapter\AbstractStorageAdapter */
            try {
                if ($adapter_id == 'fs') {
                    $authcode = $batch  . Strings::random(10, Strings::CHARS_KEY_ALPHA) . $blob_entity_tmp->getId() . $blob_entity_tmp->getNameHash();
                } else {
                    $authcode = $blob_entity_tmp->getId() . Strings::random(15, Strings::CHARS_KEY_ALPHA) . '0';
                }

                $blob->setMeta('authcode', $authcode);
                $path = $adapter->makePathForBlob($blob);
                $blob->setPath($path);
                $adapter->writeBlobString($blob, $source_data);

                $blob_entity_tmp->save_path = $path;
                $blob_entity_tmp->storage_loc = $adapter_id;

                // Success, dont try others
                break;
            } catch (\Exception $e) {
                $this->logger->logWarn("[DeskproBlobStorage] (saveBlobRecordFromString) $adapter_id failed: {$e->getCode()} {$e->getMessage()}");
                if (isset($GLOBALS['DP_IS_MOVE_BLOBS_COMMAND'])) KernelErrorHandler::logException($e);
                $prev_e = $e;
            }
        }

        // None of the succeeded, try to delete this half-inserted blob and then throw an error
        if (!$blob_entity_tmp->storage_loc) {
            $this->logger->logError("[DeskproBlobStorage] (saveBlobRecordFromString) All adapters failed");

            $this->db->delete('blobs', $blob_array['id']);

            throw new \RuntimeException("Failed to store blob, no adapters succeeded", 1, $prev_e);
        }

        $blob_entity_tmp->authcode  = $blob->getMeta('authcode');

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

        $blob_array = array_merge($blob_array, $blob_entity_tmp->toDbArray());
        $blob_update = $blob_array;
        unset($blob_update['id']);

        $this->db->update('blobs', $blob_update, array('id' => $blob_array['id']));

        $this->logger->logDebug("[DeskproBlobStorage] (saveBlobRecordFromString) Save success");

        return $blob_array;
    }


    /**
     * @param              $source_data
     * @param              $filename
     * @param              $content_type
     * @param  array       $props
     * @return null|object
     */
    public function createBlobRecordFromString($source_data, $filename, $content_type, array $props = null)
    {
        $blob_id = $this->createBlobRowFromString($source_data, $filename, $content_type, $props);
        $blob = $this->em->find('DeskPRO:Blob', $blob_id);

        return $blob;
    }


    /**
     * Read a blob into a string
     *
     * @param  Blob       $blob
     * @param  string     $adapter_id
     * @return string
     * @throws \Exception
     */
    public function copyBlobToString(Blob $blob, $adapter_id)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (getBlobString) Reading {$blob->getPath()} from $adapter_id");

        $adapter = $this->getAdapter($adapter_id);

        try {
            $data = $adapter->readBlobString($blob);
        } catch (\Exception $e) {
            $this->logger->logDebug("[DeskproBlobStorage] (getBlobString) Read failed: {$e->getCode()} {$e->getMessage()}");
            throw $e;
        }

        $this->logger->logDebug("[DeskproBlobStorage] (getBlobString) Read success: " . Numbers::filesizeDisplay(strlen($data)));

        return $data;
    }


    /**
     * Read a blob to a file
     *
     * @param  string     $target_path
     * @param  Blob       $blob
     * @param  string     $adapter_id
     * @return int
     * @throws \Exception
     */
    public function copyBlobToFile($target_path, Blob $blob, $adapter_id)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (saveBlobToFile) Saving {$blob->getPath()} from $adapter_id to $target_path");

        $adapter = $this->getAdapter($adapter_id);

        try {
            $data = $adapter->readBlobToFile($blob, $target_path);
        } catch (\Exception $e) {
            $this->logger->logDebug("[DeskproBlobStorage] (saveBlobToFile) Save failed: {$e->getCode()} {$e->getMessage()}");
            throw $e;
        }

        $this->logger->logDebug("[DeskproBlobStorage] (getBlobString) Save success: " . Numbers::filesizeDisplay($data));

        return $data;
    }


    /**
     * @param  BlobEntity  $blob_entity
     * @return null|string
     */
    public function copyBlobRecordToString(BlobEntity $blob_entity)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (readBlobStringFromRecord) Read blob record {$blob_entity->id} from {$blob_entity->storage_loc}");

        $data = null;

        // Can just use the public URL
        if ($blob_entity->file_url) {
            $this->logger->logDebug("[DeskproBlobStorage] (readBlobStringFromRecord) Attempting to fetch via URL: {$blob_entity->file_url}");
            $data = @file_get_contents($blob_entity->file_url);
            if (!$data || strlen($data) != $blob_entity->filesize) {
                $this->logger->logDebug("[DeskproBlobStorage] (readBlobStringFromRecord) Failed");
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
     * @param  array $blob_row
     * @return null|string
     */
    public function copyBlobRowToString(array $blob_row)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (copyBlobRowToString) Read blob row {$blob_row['id']} from {$blob_row['storage_loc']}");

        $data = null;

        // Can just use the public URL
        if ($blob_row['file_url']) {
            $this->logger->logDebug("[DeskproBlobStorage] (readcopyBlobRowToString) Attempting to fetch via URL: {$blob_row['file_url']}");
            $data = @file_get_contents($blob_row->file_url);
            if (!$data || strlen($data) != $blob_row->filesize) {
                $this->logger->logDebug("[DeskproBlobStorage] (readcopyBlobRowToString) Failed");
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
     * @param  int $blob_row_id
     * @return null|string
     */
    public function copyBlobRowIdToString($blob_row_id)
    {
        $blob_row = $this->db->fetchAssoc("SELECT * FROM blobs WHERE id = ?", array($blob_row_id));
        if (!$blob_row) {
            throw new \InvalidArgumentException("Could not find blob with ID $blob_row_id");
        }

        return $this->copyBlobRowToString($blob_row);
    }


    /**
     * @param  string          $target_path
     * @param  BlobEntity      $blob_entity
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
                $this->logger->logDebug("[DeskproBlobStorage] (saveBlobStringToFile) Failed");
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
     * @param  Blob       $blob
     * @param  string     $adapter_id
     * @param  bool       $ex_on_error Throw an exception if there's an error (useful if you want raw exception from storage adapter)
     *                                 Otherwise, you can still check error state based on the return value.
     * @return bool
     * @throws \Exception
     */
    public function deleteBlob(Blob $blob, $adapter_id, $ex_on_error = false)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (deleteBlob) Deleting {$blob->getPath()} from $adapter_id");

        $adapter = $this->getAdapter($adapter_id);

        try {
            $adapter->deleteBlob($blob);
        } catch (\Exception $e) {
            $this->logger->logDebug("[DeskproBlobStorage] (deleteBlob) Delete failed: {$e->getCode()} {$e->getMessage()}");
            if ($ex_on_error) {
                throw $e;
            }

            return false;
        }

        $this->logger->logDebug("[DeskproBlobStorage] (deleteBlob) Delete success");

        return true;
    }

    /**
     * @param  BlobEntity $blob_entity
     * @param  bool       $ex_on_error Throw an exception if there's an error (useful if you want raw exception from storage adapter)
     *                                 Otherwise, you can still check error state based on the return value.
     * @return bool
     * @throws \Exception
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
            $this->logger->logDebug("[DeskproBlobStorage] (deleteBlobRecord) Delete failed: {$e->getCode()} {$e->getMessage()}");
            if ($ex_on_error) {
                throw $e;
            }

            return false;
        }

        $this->logger->logDebug("[DeskproBlobStorage] (deleteBlobRecord) Delete success");

        return true;
    }


    /**
     * @param  array      $blob_row
     * @param  bool       $ex_on_error Throw an exception if there's an error (useful if you want raw exception from storage adapter)
     *                                 Otherwise, you can still check error state based on the return value.
     * @return bool
     * @throws \Exception
     */
    public function deleteBlobRow(array $blob_row, $ex_on_error = false)
    {
        $this->logger->logDebug("[DeskproBlobStorage] (deleteBlobRow) Deleting {$blob_row['id']} from {$blob_row['storage_loc']}");

        $blob = $this->getBlobFromBlobRow($blob_row);

        try {
            $this->deleteBlob($blob, $blob_row['storage_loc']);
            $this->db->delete('blobs', array('id' => $blob_row['id']));
        } catch (\Exception $e) {
            $this->logger->logDebug("[DeskproBlobStorage] (deleteBlobRow) Delete failed: {$e->getCode()} {$e->getMessage()}");
            if ($ex_on_error) {
                throw $e;
            }

            return false;
        }

        $this->logger->logDebug("[DeskproBlobStorage] (deleteBlobRow) Delete success");

        return true;
    }

    /**
     * @param  int $blob_row_id
     * @return null|string
     */
    public function deleteBlobRowId($blob_row_id)
    {
        $blob_row = $this->db->fetchAssoc("SELECT * FROM blobs WHERE id = ?", array($blob_row_id));
        if (!$blob_row) {
            return null;
        }

        return $this->deleteBlobRow($blob_row);
    }

    /**
     * @param  BlobEntity $blob_entity
     * @return Blob
     */
    public function getBlobFromBlobRecord(BlobEntity $blob_entity)
    {
        $blob = new Blob(
            $blob_entity->filename,
            $blob_entity->content_type,
            array(
                'blob_id' => $blob_entity->id
            )
        );
        $blob->setPath($blob_entity->save_path);
        if ($blob_entity->file_url) {
            $blob->setMeta('file_url', $blob_entity->file_url);
        }

        return $blob;
    }

    /**
     * @param  array                     $blob_row
     * @return Blob
     * @throws \InvalidArgumentException
     */
    public function getBlobFromBlobRow(array $blob_row)
    {
        $blob = new Blob(
            $blob_row['filename'],
            $blob_row['content_type'],
            array(
                'blob_id' => $blob_row['id']
            )
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
        $old_blob = $this->getBlobFromBlobRecord($blob_entity);
        $old_adapter_id = $blob_entity->storage_loc;

        $blob = $this->getBlobFromBlobRecord($blob_entity);
        $file_data = $this->copyBlobRecordToString($blob_entity);

        $batch = (int)(($blob_entity->id-1) / 1000) + 1;
        if ($adapter_id == 'fs') {
            $authcode = $batch  . Strings::random(10, Strings::CHARS_KEY_ALPHA) . $blob_entity->getId() . $blob_entity->getNameHash();
        } else {
            $authcode = $blob_entity->getId() . Strings::random(15, Strings::CHARS_KEY_ALPHA) . '0';
        }

        $blob->setMeta('authcode', $authcode);
        $blob->setMeta('batch', $batch);

        $adapter = $this->getAdapter($adapter_id);
        $path = $adapter->makePathForBlob($blob);
        $blob->setPath($path);
        $adapter->writeBlobString($blob, $file_data);

        $blob_entity->save_path = $path;
        $blob_entity->storage_loc = $adapter_id;

        $blob_entity->authcode  = $blob->getMeta('authcode');

        if ($blob->getMeta('file_url')) {
            $blob_entity->file_url = $blob->getMeta('file_url');
        }

        $this->em->persist($blob_entity);
        $this->em->flush();

        // Delete the old one
        $this->deleteBlob($old_blob, $old_adapter_id);
    }
}
