<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\BlobStorage\StorageAdapter;

use Application\DeskPRO\BlobStorage\Blob;
use Orb\Log\Loggable;
use Orb\Log\Logger;
use Orb\Util\OptionsArray;

abstract class AbstractStorageAdapter implements Loggable
{
    const REQUIRES_CACHE = true;

    /**
     * @var \Orb\Util\OptionsArray
     */
    protected $options;

    /**
     * @var \Orb\Log\Logger
     */
    protected $logger;

    public function __construct(array $options = null)
    {
        if ($options) {
            if (is_array($options)) {
                $options = new OptionsArray($options);
            }
            if (!($options instanceof OptionsArray)) {
                throw new \InvalidArgumentException('$options must be an array or an instance of OptionsArray');
            }
        } else {
            $options = new OptionsArray();
        }

        $this->options = $options;
        $this->logger  = new Logger();

        $this->init();
    }

    protected function init()
    {
    }

    /**
     * @param Blob $blob
     *
     * @return string
     */
    public function makePathForBlob(Blob $blob)
    {
        $path = [];
        if ($blob->getMeta('batch')) {
            $path[] = $blob->getMeta('batch');
        }
        if ($blob->getMeta('authcode')) {
            $path[] = $blob->getMeta('authcode');
        } else {
            $path[] = md5(uniqid('', true)).'-'.$blob->getFilenameSafe();
        }

        return implode('/', $path);
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    abstract public function checkBlobExists(Blob $blob);

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     *
     * @return bool
     */
    abstract public function deleteBlob(Blob $blob);

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     * @param $data
     *
     * @return mixed
     */
    abstract public function writeBlobString(Blob $blob, $data);

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     * @param resource                              $data
     *
     * @return int
     */
    abstract public function writeBlobFromStream(Blob $blob, $fp_source);

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     * @param $data
     *
     * @return mixed
     */
    abstract public function writeBlobFromFile(Blob $blob, $source_path);

    /**
     * Loads the entire blob into a string.
     *
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     *
     * @return string
     */
    abstract public function readBlobString(Blob $blob);

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     * @param $target_path
     *
     * @return int
     */
    abstract public function readBlobToFile(Blob $blob, $target_path);

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     * @param resource                              $data
     *
     * @return int
     */
    abstract public function readBlobToStream(Blob $blob, $fp_target);

    /**
     * indicates if temp cache is required for optimization purposes.
     *
     * @return bool
     */
    public function requiresTempCache()
    {
        return static::REQUIRES_CACHE;
    }

    /**
     * How many failures this adapter can have before the system begins to skip it.
     *
     * @return int
     */
    public function getFailLimitPerRequest()
    {
        return $this->options->get('fail_limit_per_request', 0);
    }
}
