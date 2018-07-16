<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters;

use DeskPRO\Bundle\AppStoreBundle\Domain\AppBundle;

interface ZipBundleWriter
{
    /**
     * @param string $dir
     * @param int    $maxDepth
     *
     * @return ZipBundleWriter
     */
    public function addFolder($dir, $maxDepth = -1);

    /**
     * @param string|\SplFileInfo $file
     * @param null                $localName
     *
     * @return ZipBundleWriter
     */
    public function addFile($file, $localName = null);
    /**
     * @param string $manifest
     *
     * @return ZipBundleWriter
     */
    public function setManifest($manifest);

    /**
     * @return AppBundle
     */
    public function build();
}
