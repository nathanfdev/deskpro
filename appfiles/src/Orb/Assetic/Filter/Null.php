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

class Null implements FilterInterface
{
	public function __construct()
	{
	}

	public function filterDump(AssetInterface $asset)
    {

    }

	public function filterLoad(AssetInterface $asset)
	{

	}
}
