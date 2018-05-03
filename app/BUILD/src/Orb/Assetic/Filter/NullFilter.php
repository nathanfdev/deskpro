<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;

class NullFilter implements FilterInterface
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
