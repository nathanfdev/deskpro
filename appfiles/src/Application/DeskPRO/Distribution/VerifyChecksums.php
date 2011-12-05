<?php
/**
 * Orb
 *
 * @package Orb
 * @category File
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Distribution;

use Symfony\Component\Finder\Finder;

class VerifyChecksums
{
	protected $standard_hashes;
	protected $count_all;

	public function __construct($chunk_size = 200)
	{
		$standard_hashes = require DP_ROOT.'/sys/Resources/distro-checksums.php';
		$this->count_all = count($standard_hashes);
		$standard_hashes = array_chunk($standard_hashes, $chunk_size, true);
		$this->standard_hashes = $standard_hashes;
	}

	/**
	 * Gets the files in a specific chunk as defined in the standard distro checksum file,
	 * and then compares those hashes.
	 *
	 * @param int $chunk
	 */
	public function compareChunk($chunk = 0)
	{
		$standard_chunk_hashes = $this->getStandardChunk($chunk);
		$chunk_files = array_keys($standard_chunk_hashes);
		$chunk_hashes = array();

		foreach ($chunk_files as $f) {
			$filepath = DP_ROOT.$f;
			if (file_exists($filepath)) {
				$chunk_hashes[$f] = md5_file($filepath);
			}
		}

		$results = array(
			'added' => array(),
			'removed' => array(),
			'changed' => array(),
			'okay' => array()
		);

		foreach ($chunk_hashes as $path => $checksum) {
			if (!isset($standard_chunk_hashes[$path])) {
				$results['added'][] = $path;
			} elseif ($checksum != $standard_chunk_hashes[$path]) {
				$results['changed'][] = $path;
			} elseif (!file_exists(DP_ROOT.$path)) {
				$results['removed'][] = $path;
			} else {
				$results['okay'][] = $path;
			}
		}

		return $results;
	}


	/**
	 * Get a chunk
	 *
	 * @param int $chunk
	 * @param int $chunk_size
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
	 * Count how many chunks there are
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
