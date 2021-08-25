<?php

namespace DeskPRO\Bundle\AppBundle\Zippy;

use Alchemy\Zippy\Adapter\ZipExtensionAdapter;

class ZipFileNoExtStrategy extends \Alchemy\Zippy\FileStrategy\ZipFileStrategy
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

    /**
     * Match on no file ext
     *
     * {@inheritDoc}
     */
    public function getFileExtension()
    {
        return '';
    }
}
