<?php

namespace DeskPRO\Bundle\AppBundle\Zippy;

use Alchemy\Zippy\Adapter\Resource\ZipArchiveResource;
use Alchemy\Zippy\Exception\RuntimeException;
use Alchemy\Zippy\Resource\ResourceManager;

class ZipExtensionAdapter extends \Alchemy\Zippy\Adapter\ZipExtensionAdapter
{
    private $errorCodesMapping = array(
        \ZipArchive::ER_EXISTS => "File already exists",
        \ZipArchive::ER_INCONS => "Zip archive inconsistent",
        \ZipArchive::ER_INVAL  => "Invalid argument",
        \ZipArchive::ER_MEMORY => "Malloc failure",
        \ZipArchive::ER_NOENT  => "No such file",
        \ZipArchive::ER_NOZIP  => "Not a zip archive",
        \ZipArchive::ER_OPEN   => "Can't open file",
        \ZipArchive::ER_READ   => "Read error",
        \ZipArchive::ER_SEEK   => "Seek error"
    );

    public static function newInstance()
    {
        return new ZipExtensionAdapter(ResourceManager::create());
    }

    protected function createResource($path)
    {
        $zip = new \ZipArchive();
        $res = $zip->open($path);

        if ($res !== true) {
            throw new RuntimeException($this->errorCodesMapping[$res]);
        }

        return new ZipArchiveResource($zip);
    }
}
