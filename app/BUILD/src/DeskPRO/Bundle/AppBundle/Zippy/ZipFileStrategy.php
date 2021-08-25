<?php

namespace DeskPRO\Bundle\AppBundle\Zippy;

use Alchemy\Zippy\Adapter\ZipExtensionAdapter;

class ZipFileStrategy extends \Alchemy\Zippy\FileStrategy\ZipFileStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function getServiceNames()
    {
        // We only support zip extension
        return [
            ZipExtensionAdapter::class,
        ];
    }
}
