<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
