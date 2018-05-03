<?php

namespace DeskPRO\Bundle\InstallBundle\FileIntegrity\Checker;

use DeskPRO\Bundle\InstallBundle\FileIntegrity\FileHasher;
use DeskPRO\Bundle\InstallBundle\FileIntegrity\ProjectFileSet;

class IntegrityChecker
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
     * FileIntegrityChecker constructor.
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
     * @param array    $map     The map of file=>hash
     * @param callable $stepper A custom callable to run after every file is chacek
     *
     * @return IntegrityCheckResult
     */
    public function checkAgainstMap(array $map, $stepper = null)
    {
        $result = new IntegrityCheckResult();

        foreach ($map as $path => $correctHash) {
            if (strpos($path, 'integrity_file_map.dat') !== false) {
                continue;
            }
            $realPath = $this->proj->getRealPath($path);
            if (!file_exists($realPath)) {
                $result->recordBadPath($realPath, IntegrityCheckResult::MISSING);
            } else {
                $hash = $this->hasher->hash($realPath);

                if ($hash !== $correctHash) {
                    $result->recordBadPath($realPath, IntegrityCheckResult::INVALID);
                } else {
                    $result->recordOkayPath($realPath);
                }
            }

            if ($stepper) {
                call_user_func($stepper, $result);
            }
        }

        return $result;
    }
}
