<?php

namespace DeskPRO\Bundle\InstallBundle\FileIntegrity\Generator;

use DeskPRO\Bundle\InstallBundle\FileIntegrity\FileHasher;
use DeskPRO\Bundle\InstallBundle\FileIntegrity\ProjectFileSet;

class IntegrityMapGenerator
{
    /**
     * @var ProjectFileSet
     */
    private $proj;

    /**
     * @var FileHasher
     */
    private $hasher;

    /**
     * FileIntegritySetGenerator constructor.
     *
     * @param ProjectFileSet $projectFileSet
     * @param FileHasher     $hasher
     */
    public function __construct(ProjectFileSet $projectFileSet, FileHasher $hasher)
    {
        $this->proj   = $projectFileSet;
        $this->hasher = $hasher;
    }

    /**
     * @return array
     */
    public function generateMap()
    {
        $map = [];

        foreach ($this->proj->buildFileSet() as $path) {
            $realPath   = $this->proj->getRealPath($path);
            $map[$path] = $this->hasher->hash($realPath);
        }

        return $map;
    }
}
