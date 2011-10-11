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

class SmartSprites implements FilterInterface
{
	protected $smartsprites_bin;

	/**
	 * @var \Orb\Util\OptionsArray
	 */
	protected $options;

	public function __construct($smartsprites_bin, array $options = array())
	{
		$this->smartsprites_bin = $smartsprites_bin;
		$this->options = new \Orb\Util\OptionsArray($options);
	}

	public function setOptions(array $options)
	{
		$this->options->setAll($options);
	}

	public function filterDump(AssetInterface $asset)
    {

    }

	public function filterLoad(AssetInterface $asset)
	{
		$pb = new ProcessBuilder(array(
			$this->smartsprites_bin
		));

		$pb->setWorkingDirectory(dirname($this->smartsprites_bin));

		$tmpfile = $asset->getSourceRoot() . '/' . substr(sha1(time().rand(11111, 99999)), 0, 7) . '.css';
		$expect_outfile = str_replace('.css', '-sprite.css', $tmpfile);

		if (!file_put_contents($tmpfile, $asset->getContent())) {
			@unlink($tmpfile);
			throw new \RuntimeException('Error creating tmp CSS file in source directory. SmartSprites requires the file to be in the proper location, so we tried to make a temp file there but failed.');
		}

		$pb->add('--css-files')->add($tmpfile);

		$proc = $pb->getProcess();
		$code = $proc->run();

		@unlink($tmpfile);

		if (0 < $code) {
			if (file_exists($expect_outfile)) {
				unlink($expect_outfile);
			}

			throw new \RuntimeException($proc->getErrorOutput());
		}

		// No file means SmartSprites just didnt need to do anything,
		// so we need to check for it

		if (file_exists($expect_outfile)) {
        	$asset->setContent(file_get_contents($expect_outfile));
			unlink($expect_outfile);
		}
	}
}
