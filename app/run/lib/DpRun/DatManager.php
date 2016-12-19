<?php

namespace DpRun;

class DatManager implements DatManagerInterface
{
    /**
     * @var string
     */
    private $cache_dir;

    /**
     * DatManager constructor.
     *
     * @param string $cache_dir
     */
    public function __construct($cache_dir)
    {
        $this->cache_dir = $cache_dir;
    }

    /**
     * @inheritDoc
     */
    public function hasTrigger($id)
    {
        return $this->hasFile($id.'.trigger');
    }

    /**
     * @inheritDoc
     */
    public function enableTrigger($id)
    {
        return $this->writeFile($id.'.trigger', date('Y-m-d H:i:s'));
    }

    /**
     * @inheritDoc
     */
    public function disableTrigger($id)
    {
        return $this->removeFile($id.'.trigger');
    }

    /**
     * @inheritDoc
     */
    public function hasDatFile($id)
    {
        return $this->hasFile($id.'.dat');
    }

    /**
     * @inheritDoc
     */
    public function readDatFile($id, $default = '__throw__')
    {
        $content = $this->readFile($id.'.dat');

        if ($content === false) {
            if ($default === '__throw__') {
                throw new \RuntimeException("Failed to read datfile: " . $id);
            }
            return $default;
        }

        $content = @json_decode($content, true);
        if ($content === false || !array_key_exists('content', $content)) {
            if ($default === '__throw__') {
                throw new \RuntimeException("Failed to decode datfile: " . $id);
            }
            return $default;
        }

        return $content['content'];
    }

    /**
     * @inheritDoc
     */
    public function writeDatFile($id, array $content)
    {
        $write_content = json_encode([
            'meta'    => ['datetime' => date('Y-m-d H:i:s')],
            'content' => $content
        ]);

        return $this->writeFile($id.'.dat', $write_content);
    }

    /**
     * @inheritDoc
     */
    public function removeDatFile($id)
    {
        return $this->removeFile($id.'.dat');
    }

    /**
     * @inheritDoc
     */
    public function hasTxtFile($id)
    {
        return $this->hasFile($id.'.txt');
    }

    /**
     * @inheritDoc
     */
    public function readTxtFile($id, $default = '__throw__')
    {
        $content = $this->readFile($id.'.txt');

        if ($content === false) {
            if ($default === '__throw__') {
                throw new \RuntimeException("Failed to read datfile: " . $id);
            }
            return $default;
        }

        $content = str_replace(array("\r\n", "\r"), "\n", trim($content));

        return $content;
    }

    /**
     * @inheritDoc
     */
    public function writeTxtFile($id, $txt)
    {
        $txt = str_replace(array("\r\n", "\r"), "\n", trim($txt));
        return $this->writeFile($id.'.txt', $txt);
    }

    /**
     * @inheritDoc
     */
    public function removeTxtFile($id)
    {
        return $this->removeFile($id.'.txt');
    }

    /**
     * @inheritDoc
     */
    public function hasBinFile($id)
    {
        return $this->hasFile($id.'.bin');
    }

    /**
     * @inheritDoc
     */
    public function readBinFile($id, $default = '__throw__')
    {
        return $this->readFile($id.'.bin');
    }

    /**
     * @inheritDoc
     */
    public function writeBinFile($id, $txt)
    {
        return $this->writeFile($id.'.bin', $txt);
    }

    /**
     * @inheritDoc
     */
    public function removeBinFile($id)
    {
        return $this->removeFile($id.'.bin');
    }

    ####################################################################################################################

    protected function hasFile($name)
    {
        return is_file($this->cache_dir . DIRECTORY_SEPARATOR . $name);
    }

    protected function readFile($name)
    {
        $path = $this->cache_dir . DIRECTORY_SEPARATOR . $name;

        if (is_file($path)) {
            $tries = 0;
            do {
                $content = @file_get_contents($path);
            } while (($content === false) && ++$tries < 3);
        } else {
            $content = false;
        }

        return $content;
    }

    protected function writeFile($name, $content)
    {
        $path  = $this->cache_dir . DIRECTORY_SEPARATOR . $name;
        $tries = 0;

        do {
            $success = @file_put_contents($path, $content, \LOCK_EX) !== false;
        } while (!$success && ++$tries < 3);

        return $success;
    }

    protected function removeFile($name)
    {
        $path  = $this->cache_dir . DIRECTORY_SEPARATOR . $name;
        $tries = 0;

        do {
            if (!is_file($path)) {
                return true;
            }
            $success = @unlink($path);
        } while (!$success && ++$tries < 3);

        return $success;
    }
}
