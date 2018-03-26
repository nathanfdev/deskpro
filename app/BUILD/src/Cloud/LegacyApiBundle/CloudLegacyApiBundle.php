<?php

/**
 * DeskPRO.
 */

namespace Cloud\LegacyApiBundle;

class CloudLegacyApiBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getPath()
    {
        return __DIR__;
    }
}
