<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Assetic\Filter;

use Assetic\Filter\FilterInterface;
use Assetic\Asset\AssetInterface;
use Assetic\Util\ProcessBuilder;

class Lessc implements FilterInterface
{
	protected $lessc_bin;

	public function __construct($lessc_bin)
	{
		$this->lessc_bin = $lessc_bin;
	}

	public function filterDump(AssetInterface $asset)
    {

    }

	public function filterLoad(AssetInterface $asset)
	{
		$tempDir = realpath(sys_get_temp_dir());
		$madefiles = array();

		$source_files = array();
		if ($asset instanceof \Assetic\Asset\FileAsset) {
			$source_files[] = $asset->getSourceRoot() . '/' . $asset->getSourcePath();
		} else if ($asset instanceof \Assetic\Asset\AssetCollection) {
			foreach ($asset->getIterator() as $sub_asset) {
				$source_files[] = $asset->getSourceRoot() . '/' . $sub_asset->getSourcePath();
			}
		} else {
			throw new \RunTimeException("Cannot handle asset type `" . get_class($asset) . "`");
		}

		foreach ($source_files as $source_file) {
			$hash = substr(sha1(time().rand(11111, 99999)), 0, 7);
			$output = $tempDir.DIRECTORY_SEPARATOR.$hash.'.css';

			$pb = new ProcessBuilder();
			$pb->add($this->lessc_bin);

			$pb->add($source_file)->add($output);
			$proc = $pb->getProcess();
        	$code = $proc->run();

			if (0 < $code) {
				if (file_exists($output)) {
					unlink($output);
				}

				foreach ($madefiles as $tmpfile) {
					if (file_exists($tmpfile)) {
						unlink($tmpfile);
					}
				}

				throw new \RuntimeException("[Lessc] " . $pb->getProcess()->getCommandLine() . " " . $proc->getOutput() . "\n\n" . $proc->getErrorOutput());
			} elseif (!file_exists($output)) {
				throw new \RuntimeException('Error creating output file.');
			}

			$madefiles[] = $output;
		}

		$complete_file = array();
		foreach ($madefiles as $f) {
			$complete_file[] = file_get_contents($f);
			unset($f);
		}

		$complete_file = implode("\n", $complete_file);

        $asset->setContent($complete_file);
	}
}
