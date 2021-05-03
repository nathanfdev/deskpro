<?php

namespace Application\DeskPRO\BlobStorage\StorageAdapter;

use Application\DeskPRO\BlobStorage\Blob;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class WebDAVStorage extends AbstractStorageAdapter
{
    /**
     * @var \Sabre\DAV\Client
     */
    protected $davClient;

    protected function init()
    {
        $this->davClient = $this->options->get('dav');
    }

    /**
     * Get the full path from a path string.
     *
     * @param string $path
     *
     * @return string
     */
    public function resolvePath($path)
    {
        return trim('/'.trim($path, '/'), '/');
    }

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     *
     * @return bool
     */
    public function checkBlobExists(Blob $blob)
    {
        return true;
    }

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     *
     * @return bool
     */
    public function deleteBlob(Blob $blob)
    {
        $path = $this->resolvePath($blob->getPath());

        return true;
    }

    /**
     * @param Blob   $blob
     * @param string $data
     *
     * @return mixed
     */
    public function writeBlobString(Blob $blob, $data)
    {
        $path = $this->resolvePath($blob->getPath());

        $headerBag   = new ResponseHeaderBag();
        $disposition = $headerBag->makeDisposition(
            $blob->getMeta("content_disposition") ?: ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $blob->getFilename(),
            $blob->getFilenameSafe()
        );

        return strlen($data);
    }

    /**
     * @param Blob     $blob
     * @param resource $fp_source
     *
     * @return int
     */
    public function writeBlobFromStream(Blob $blob, $fp_source)
    {
        return $this->writeBlobString($blob, stream_get_contents($fp_source));
    }

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     * @param string                                $sourcePath
     *
     * @return int
     */
    public function writeBlobFromFile(Blob $blob, $sourcePath)
    {
        return $this->writeBlobString($blob, file_get_contents($sourcePath));
    }

    /**
     * Loads the entire blob into a string.
     *
     * @param Blob $blob
     *
     * @return string
     */
    public function readBlobString(Blob $blob)
    {
        return '';
    }

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     * @param $targetPath
     *
     * @return int
     */
    public function readBlobToFile(Blob $blob, $targetPath)
    {
        return file_put_contents($targetPath, $this->readBlobString($blob));
    }

    /**
     * @param Blob     $blob
     * @param resource $fp_target
     *
     * @return int
     */
    public function readBlobToStream(Blob $blob, $fp_target)
    {
        return fwrite($fp_target, $this->readBlobString($blob));
    }

    /**
     * @param Blob $blob
     */
    public function getFileUrlLink(Blob $blob)
    {
    }
}
