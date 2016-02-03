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
        return is_file($this->cache_dir . DIRECTORY_SEPARATOR . $id . '.trigger');
    }

    /**
     * @inheritDoc
     */
    public function enableTrigger($id)
    {
        $tries = 0;
        do {
            if ($this->hasTrigger($id)) {
                return true;
            }
            $success = @file_put_contents($this->cache_dir . DIRECTORY_SEPARATOR . $id . '.trigger', date('Y-m-d H:i:s'), \LOCK_EX) !== false;
        } while (!$success && ++$tries < 3);

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function disableTrigger($id)
    {
        $tries = 0;
        do {
            if (!$this->hasTrigger($id)) {
                return true;
            }
            $success = @unlink($this->cache_dir . DIRECTORY_SEPARATOR . $id . '.trigger');
        } while (!$success && ++$tries < 3);

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function hasDatFile($id)
    {
        return is_file($this->cache_dir . DIRECTORY_SEPARATOR . $id . '.dat');
    }

    /**
     * @inheritDoc
     */
    public function readDatFile($id, $default = '__throw__')
    {
        $content = false;

        if ($this->hasDatFile($id)) {
            $tries = 0;
            do {
                $content = @file_get_contents($this->cache_dir . DIRECTORY_SEPARATOR . $id . '.dat');
            } while (($content === false) && ++$tries < 3);
        }

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

        $tries = 0;
        do {
            $success = @file_put_contents($this->cache_dir . DIRECTORY_SEPARATOR . $id . '.dat', $write_content, \LOCK_EX) !== false;
        } while (!$success && ++$tries < 3);

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function removeDatFile($id)
    {
        $tries = 0;
        do {
            if (!$this->hasDatFile($id)) {
                return true;
            }
            $success = @unlink($this->cache_dir . DIRECTORY_SEPARATOR . $id . '.dat');
        } while (!$success && ++$tries < 3);

        return $success;
    }
}
