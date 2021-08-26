<?php

namespace DeskPRO\Bundle\AppBundle\Zippy;

use Alchemy\Zippy\Adapter\AdapterContainer;

class ZipFileStrategy extends \Alchemy\Zippy\FileStrategy\ZipFileStrategy
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
}
