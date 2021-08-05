<?php

namespace Application\DeskPRO\BlobStorage\StorageAdapter;

use Application\DeskPRO\BlobStorage\Blob;

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
        return $this->davClient->request('HEAD', $this->resolvePath($blob->getPath()))['statusCode'] === 200;
    }

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     *
     * @return bool
     */
    public function deleteBlob(Blob $blob)
    {
        $path = $this->resolvePath($blob->getPath());
        $this->davClient->request('DELETE', $path);

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
        $path      = $this->resolvePath($blob->getPath());
        $pathArray = explode('/', $path);
        array_pop($pathArray);
        $segments = [];
        foreach ($pathArray as $segment) {
            array_push($segments, $segment);
            $this->davClient->request('MKCOL', sprintf('/%s', implode('/', $segments)));
        }

        $this->davClient->request('PUT', $path, $data);

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
        $path = $this->resolvePath($blob->getPath());

        return $this->davClient->request('GET', $path)['body'];
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
