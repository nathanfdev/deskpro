<?php

/**
 * Orb.
 *
 * @category File
 */

namespace Application\DeskPRO\Distribution;

use DeskPRO\Bundle\InstallBundle\FileIntegrity\FileHasher;
use DeskPRO\Bundle\InstallBundle\FileIntegrity\ProjectFileSet;

class VerifyChecksums
{
    /**
     * @var array
     */
    private $standardHashes;

    /**
     * @var int
     */
    private $countAll;

    /**
     * @var ProjectFileSet
     */
    private $proj;

    /**
     * @var FileHasher
     */
    private $hasher;

    public function __construct($chunkSize = 350)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        $checksumPath = $DP_ENV->getAppBaseKernelCacheDir().'/integrity_file_map.dat';

        if (!is_file($checksumPath)) {
            // Get it to appear in the missing list in admin
            $standardHashes = [$checksumPath => 'missing'];
        } else {
            $standardHashes = json_decode(file_get_contents($checksumPath), true);
        }

        $this->proj   = new ProjectFileSet($DP_ENV);
        $this->hasher = new FileHasher();

        $this->countAll       = count($standardHashes);
        $standardHashes       = array_chunk($standardHashes, $chunkSize, true);
        $this->standardHashes = $standardHashes;
    }

    /**
     * Gets the files in a specific chunk as defined in the standard distro checksum file,
     * and then compares those hashes.
     *
     * @param int $chunk
     *
     * @return array
     */
    public function compareChunk($chunk = 0)
    {
        $standardChunkHashes = $this->getStandardChunk($chunk);
        $chunkFiles          = array_keys($standardChunkHashes);
        $chunkHashes         = [];

        foreach ($chunkFiles as $f) {
            $filepath = $this->proj->getRealPath($f);
            if (file_exists($filepath)) {
                $chunkHashes[$f] = $this->hasher->hash($filepath);
            } else {
                $chunkHashes[$f] = null;
            }
        }

        $results = [
            'added'   => [],
            'removed' => [],
            'changed' => [],
            'okay'    => [],
        ];

        foreach ($chunkHashes as $path => $checksum) {
            if (!isset($standardChunkHashes[$path])) {
                $results['added'][] = $this->proj->getRealPath($path);
            } elseif ($checksum === null) {
                $results['removed'][] = $this->proj->getRealPath($path);
            } elseif ($checksum != $standardChunkHashes[$path]) {
                $results['changed'][] = $this->proj->getRealPath($path);
            } else {
                $results['okay'][] = $this->proj->getRealPath($path);
            }
        }

        return $results;
    }

    /**
     * @param string $fileContents
     *
     * @return string
     */
    protected function normalizeFileString($fileContents)
    {
        static $bom = null;

        if ($bom === null) {
            $bom = pack('CCC', 0xEF, 0xBB, 0xBF);
        }

        if (substr($fileContents, 0, 3) === $bom) {
            $fileContents = substr($fileContents, 3);
        }

        $fileContents = trim(str_replace(["\r", "\n"], '', $fileContents));

        return $fileContents;
    }

    /**
     * Get a chunk.
     *
     * @param int $chunk
     *
     * @return array
     */
    public function getStandardChunk($chunk)
    {
        if (!isset($this->standardHashes[$chunk])) {
            return [];
        }

        return $this->standardHashes[$chunk];
    }

    /**
     * Count how many chunks there are.
     *
     * @return int
     */
    public function countChunks()
    {
        return count($this->standardHashes);
    }

    public function countFiles()
    {
        return $this->countAll;
    }
}
