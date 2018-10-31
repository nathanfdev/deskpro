<?php

namespace DeskPRO\Bundle\BrandBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class BrandBundle.
 */
class BrandBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return __DIR__;
    }
}
