<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
    private $standard_hashes;

    /**
     * @var int
     */
    private $count_all;

    /**
     * @var ProjectFileSet
     */
    private $proj;

    /**
     * @var FileHasher
     */
    private $hasher;

    public function __construct($chunk_size = 350)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        $checksumPath = $DP_ENV->getAppBaseKernelCacheDir().'/integrity_file_map.dat';

        if (!is_file($checksumPath)) {
            // Get it to appear in the missing list in admin
            $standard_hashes = array($checksumPath => 'missing');
        } else {
            $standard_hashes = json_decode(file_get_contents($checksumPath), true);
        }

        $this->proj   = new ProjectFileSet($DP_ENV);
        $this->hasher = new FileHasher();

        $this->count_all       = count($standard_hashes);
        $standard_hashes       = array_chunk($standard_hashes, $chunk_size, true);
        $this->standard_hashes = $standard_hashes;
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
        $standard_chunk_hashes = $this->getStandardChunk($chunk);
        $chunk_files           = array_keys($standard_chunk_hashes);
        $chunk_hashes          = array();

        foreach ($chunk_files as $f) {
            $filepath = $this->proj->getRealPath($f);
            if (file_exists($filepath)) {
                $chunk_hashes[$f] = $this->hasher->hash($filepath);
            } else {
                $chunk_hashes[$f] = null;
            }
        }

        $results = array(
            'added'   => array(),
            'removed' => array(),
            'changed' => array(),
            'okay'    => array(),
        );

        foreach ($chunk_hashes as $path => $checksum) {
            if (!isset($standard_chunk_hashes[$path])) {
                $results['added'][] = $path;
            } elseif ($checksum === null) {
                $results['removed'][] = $path;
            } elseif ($checksum != $standard_chunk_hashes[$path]) {
                $results['changed'][] = $path;
            } else {
                $results['okay'][] = $path;
            }
        }

        return $results;
    }

    /**
     * @param string $file_contents
     *
     * @return string
     */
    protected function normalizeFileString($file_contents)
    {
        static $bom = null;

        if ($bom === null) {
            $bom = pack('CCC', 0xEF, 0xBB, 0xBF);
        }

        if (substr($file_contents, 0, 3) === $bom) {
            $file_contents = substr($file_contents, 3);
        }

        $file_contents = trim(str_replace(array("\r", "\n"), '', $file_contents));

        return $file_contents;
    }

    /**
     * Get a chunk.
     *
     * @param int $chunk
     * @param int $chunk_size
     *
     * @return array
     */
    public function getStandardChunk($chunk)
    {
        if (!isset($this->standard_hashes[$chunk])) {
            return array();
        }

        return $this->standard_hashes[$chunk];
    }

    /**
     * Count how many chunks there are.
     *
     * @return int
     */
    public function countChunks()
    {
        return count($this->standard_hashes);
    }

    public function countFiles()
    {
        return $this->count_all;
    }
}
