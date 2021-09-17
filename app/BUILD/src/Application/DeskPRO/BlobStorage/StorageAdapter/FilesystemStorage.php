<?php

namespace Application\DeskPRO\BlobStorage\StorageAdapter;

use Application\DeskPRO\BlobStorage\Blob;
use Application\DeskPRO\BlobStorage\BlobStorageException;
use DeskPRO\Component\Filesystem\SafeFile;
use Orb\Util\Numbers;

class FilesystemStorage extends AbstractStorageAdapter implements ReadStreamInterface, WriteStreamInterface
{
    const REQUIRES_CACHE = false;

    /**
     * @var string
     */
    protected $base_path;

    /**
     * @var int
     */
    protected $file_mode = 0777;

    /**
     * @var int
     */
    protected $dir_mode = 0777;

    protected function init()
    {
        $this->base_path = rtrim($this->options->get('base_path'), '/\\');

        if ($this->options->has('file_mode')) {
            $this->file_mode = (int) $this->options->get('file_mode');
        }
        if ($this->options->has('dir_mode')) {
            $this->file_mode = (int) $this->options->get('dir_mode');
        }
    }

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     *
     * @return bool
     */
    public function checkBlobExists(Blob $blob)
    {
        $path = $this->resolvePath($blob->getPath());

        $exists = SafeFile::is_file($path, $this->base_path);

        $this->logger->logInfo("[FilesystemStorage] (checkBlobExists) $path ".($exists ? 'exists' : 'no exist'));

        return $exists;
    }

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     *
     * @return bool
     */
    public function deleteBlob(Blob $blob)
    {
        $path = $this->resolvePath($blob->getPath());

        if (!SafeFile::file_exists($path, $this->base_path)) {
            $this->logger->logInfo("[FilesystemStorage] (deleteBlob) Path does not exist, nothing to delete: $path");

            return true;
        }

        $res = @SafeFile::unlink($path, $this->base_path);

        if ($res) {
            $this->logger->logInfo("[FilesystemStorage] (deleteBlob) Deleted path: $path");
        } else {
            $this->logger->logInfo("[FilesystemStorage] (deleteBlob) Failed to delete path: $path");
        }

        $metaPath = $path.'.meta.json';
        if (SafeFile::file_exists($metaPath, $this->base_path)) {
            $res = @SafeFile::unlink($metaPath, $this->base_path);

            if ($res) {
                $this->logger->logInfo("[FilesystemStorage] (deleteBlob) Deleted path: $metaPath");
            } else {
                $this->logger->logInfo("[FilesystemStorage] (deleteBlob) Failed to delete path: $metaPath");
            }
        }

        return $res;
    }

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     * @param $data
     *
     * @return int
     */
    public function writeBlobString(Blob $blob, $data)
    {
        $fp = $this->getBlobWriteStream($blob);

        try {
            $ret = $this->_writeChunkToStream($fp, $data);
        } catch (\Exception $e) {
            @fclose($fp);

            throw $e;
        }

        @fflush($fp);
        @fclose($fp);

        $this->_verifyWrite($this->resolvePath($blob->getPath()));

        $this->logger->logInfo('[FilesystemStorage] (writeBlobString) Wrote '.Numbers::filesizeDisplay($ret).' from string to '.$this->resolvePath($blob->getPath()));

        return $ret;
    }

    /**
     * @param Blob   $blob
     * @param string $sourcePath
     *
     *@throws BlobStorageException
     *
     * @return int
     */
    public function writeBlobFromFile(Blob $blob, $sourcePath)
    {
        $fp_source = @SafeFile::fopen($sourcePath, 'r', SafeFile::UNSPECIFIED);

        if (!$fp_source) {
            @fclose($fp_source);
            $this->logger->logError("[FilesystemStorage] (writeBlobFromFile) Could not open $sourcePath for reading");

            throw new BlobStorageException("Could not open source_path for reading: $sourcePath", BlobStorageException::FAILED_RESOURCE_READ);
        }

        try {
            $ret = $this->writeBlobFromStream($blob, $fp_source);
        } catch (\Exception $e) {
            @fclose($fp_source);

            throw $e;
        }

        @fclose($fp_source);

        $this->logger->logInfo('[FilesystemStorage] (writeBlobFromFile) Wrote '.Numbers::filesizeDisplay($ret)." from $sourcePath to ".$this->resolvePath($blob->getPath()));

        return $ret;
    }

    /**
     * @param Blob     $blob
     * @param resource $fp_source
     *
     * @throws BlobStorageException
     *
     * @return int
     */
    public function writeBlobFromStream(Blob $blob, $fp_source)
    {
        $fp  = $this->getBlobWriteStream($blob);
        $ret = $this->_copyStream($fp_source, $fp);
        @fflush($fp);
        @fclose($fp);

        $this->logger->logInfo('[FilesystemStorage] (writeBlobFromStream) Wrote '.Numbers::filesizeDisplay($ret).' from stream to '.$this->resolvePath($blob->getPath()));

        $path = $this->resolvePath($blob->getPath());
        if (SafeFile::file_exists($path, $this->base_path)) {
            $this->_chmod($path, $this->file_mode);
        }

        $this->_verifyWrite($this->resolvePath($blob->getPath()));

        $metaPath = $path.'.meta.json';
        $metadata = [
            'filename' => $blob->getFilename(),
        ];

        if (!@SafeFile::file_put_contents($metaPath, json_encode($metadata), $this->base_path)) {
            $this->logger->logError("[FilesystemStorage] (writeBlobFromStream) Could not write $metaPath for writing");
        }

        return $ret;
    }

    /**
     * Loads the entire blob into a string.
     *
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     *
     * @return string
     */
    public function readBlobString(Blob $blob)
    {
        $fp = $this->getBlobReadStream($blob);

        $str = '';
        while (!feof($fp)) {
            $str .= @fread($fp, 1000);
        }

        @fclose($fp);

        $this->logger->logInfo('[FilesystemStorage] (readBlobString) Read '.Numbers::filesizeDisplay(strlen($str)).' from '.$this->resolvePath($blob->getPath()));

        return $str;
    }

    /**
     * @param Blob   $blob
     * @param string $targetPath
     *
     * @throws \Exception
     * @throws BlobStorageException
     *
     * @return int
     */
    public function readBlobToFile(Blob $blob, $targetPath)
    {
        $fpTarget = @SafeFile::fopen($targetPath, 'w', SafeFile::UNSPECIFIED);

        if (!$fpTarget) {
            @fclose($fpTarget);
            $this->logger->logError("[FilesystemStorage] (readBlobToFile) Could not open $targetPath for writing");

            throw new BlobStorageException("Could not open target_path for writing: $targetPath", BlobStorageException::FAILED_RESOURCE_WRITE);
        }

        try {
            $ret = $this->readBlobToStream($blob, $fpTarget);
        } catch (\Exception $e) {
            @fclose($fpTarget);

            throw $e;
        }

        $this->logger->logInfo('[FilesystemStorage] (readBlobToFile) Read '.Numbers::filesizeDisplay($ret)." to $targetPath from ".$this->resolvePath($blob->getPath()));

        return $ret;
    }

    /**
     * @param \Application\DeskPRO\BlobStorage\Blob $blob
     * @param resource                              $fp_target
     *
     * @return int
     */
    public function readBlobToStream(Blob $blob, $fp_target)
    {
        $fp  = $this->getBlobReadStream($blob);
        $ret = $this->_copyStream($fp, $fp_target);
        fclose($fp);

        $this->logger->logInfo('[FilesystemStorage] (readBlobToStream) Read '.Numbers::filesizeDisplay($ret).' to stream from '.$this->resolvePath($blob->getPath()));

        return $ret;
    }

    /**
     * @return resource
     */
    public function getBlobWriteStream(Blob $blob)
    {
        $path = $this->resolvePath($blob->getPath());
        $dir  = dirname($path);

        if (!SafeFile::is_dir($dir, $this->base_path)) {
            @SafeFile::mkdir($dir, $this->base_path, 0777, true);
            $this->_chmod($dir, $this->dir_mode);
        }

        $fp = SafeFile::fopen($path, 'w', $this->base_path);

        if (!$fp) {
            $this->logger->logError("[FilesystemStorage] (getBlobWriteStream) Failed to open path for writing: $path");

            throw new BlobStorageException("Could not open blob for writing: $path", BlobStorageException::FAILED_RESOURCE_WRITE);
        }

        return $fp;
    }

    /**
     * @return resource
     */
    public function getBlobReadStream(Blob $blob)
    {
        $path = $this->resolvePath($blob->getPath());

        $fp = SafeFile::fopen($path, 'r', $this->base_path);

        if (!$fp) {
            $this->logger->logError("[FilesystemStorage] (getBlobReadStream) Failed to open path for reading: $path");

            throw new BlobStorageException("Could not open blob for reading: $path", BlobStorageException::FAILED_RESOURCE_READ);
        }

        return $fp;
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
        $path = trim($path, '/\\');

        return $this->base_path.DIRECTORY_SEPARATOR.$path;
    }

    /**
     * @param $fp_from
     * @param $fp_to
     *
     * @return int
     */
    protected function _copyStream($fp_from, $fp_to)
    {
        $size = 0;
        while (!feof($fp_from)) {
            $size += $this->_writeChunkToStream($fp_to, fread($fp_from, 8192));
        }

        return $size;
    }

    /**
     * chmod's a file to $mode. Resets current umask in case it is set.
     *
     * @param string $file
     * @param int    $mode
     */
    private function _chmod($file, $mode)
    {
        $current_umask = umask();
        @umask(0000);
        @chmod($file, $mode);
        @umask($current_umask);
    }

    /**
     * @param resource $fp
     * @param string   $chunk
     *
     * @throws BlobStorageException
     */
    private function _writeChunkToStream($fp, $chunk)
    {
        $expectSize = strlen($chunk);
        $wroteSize  = @fwrite($fp, $chunk);

        if ($wroteSize !== $expectSize) {
            $this->logger->logInfo(sprintf('[FilesystemStorage] (_writeChunkToStream) Attempted fwrite of %d bytes but only wrote %d bytes', $expectSize, $wroteSize));

            throw new BlobStorageException('Failed to write total bytes to file', BlobStorageException::FAILED_RESOURCE_WRITE);
        }

        return $wroteSize;
    }

    private function _verifyWrite($path)
    {
        if (!$this->_fileSeemsOkay($path)) {
            $this->logger->logInfo(sprintf('[FilesystemStorage] (_verifyWrite) File %d is not written', $path));

            throw new BlobStorageException('Written file could not be verified', BlobStorageException::FAILED_RESOURCE_WRITE);
        }
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function _fileSeemsOkay($path)
    {
        if (!SafeFile::file_exists($path, $this->base_path)) {
            return false;
        }

        $size = @SafeFile::filesize($path, $this->base_path);
        if (!$size || $size < 1) {
            return false;
        }

        return true;
    }
}
