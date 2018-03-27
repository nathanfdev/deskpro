<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Package;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Doctrine\ORM\EntityManager;

class NativePackages
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\BlobStorage\DeskproBlobStorage
     */
    private $blob_storage;

    /**
     * @var string
     */
    private $root_path;

    /**
     * @var string[]
     */
    private $native_names;

    /**
     * @param EntityManager      $em
     * @param DeskproBlobStorage $blob_storage
     * @param string             $root_path
     */
    public function __construct(EntityManager $em, DeskproBlobStorage $blob_storage, $root_path = null)
    {
        $this->em           = $em;
        $this->blob_storage = $blob_storage;

        if (!$root_path) {
            $root_path = DP_ROOT.'/apps';
        }
        $this->root_path = $root_path;
    }

    /**
     * @return string[]
     */
    public function getPackageNames()
    {
        if ($this->native_names !== null) {
            return $this->native_names;
        }

        $this->native_names = [];
        $dir                = dir($this->root_path);

        while (($f = $dir->read()) !== false) {
            $manifest_path = $this->root_path.'/'.$f.'/manifest.json';
            if (file_exists($manifest_path)) {
                $this->native_names[] = $f;
            }
        }

        return $this->native_names;
    }
}
