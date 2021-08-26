<?php

namespace DeskPRO\Bundle\AppBundle\Zippy;

use Alchemy\Zippy\Adapter\AdapterContainer;

class ZipFileNoExtStrategy extends \Alchemy\Zippy\FileStrategy\ZipFileStrategy
{
    public function __construct(AdapterContainer $container)
    {
        parent::__construct($container);

        $this->container['Alchemy\\Zippy\\Adapter\\ZipExtensionAdapter'] = function() {
            return ZipExtensionAdapter::newInstance();
        };

        $this->container[ZipExtensionAdapter::class] = function() {
            return ZipExtensionAdapter::newInstance();
        };
    }

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
