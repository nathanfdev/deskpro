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

namespace DeskPRO\Bundle\UpdateBundle\BuildTasks;

use Application\InstallBundle\Upgrade\Build\BlockingBuildInterface;
use Application\InstallBundle\Upgrade\Build\OnlineBuildInterface;
use Application\InstallBundle\Upgrade\Build\SkipPostBuildInterface;
use Orb\Util\Strings;
use Symfony\Component\Finder\Finder;

class GenBuildManifest
{
    /**
     * @var string
     */
    private $buildsPath;

    public function __construct($buildsPath)
    {
        $this->buildsPath = $buildsPath;
    }

    /**
     * @return string
     */
    public function getBuildsPath()
    {
        return $this->buildsPath;
    }

    /**
     * @return array
     */
    public function getBuildsArray()
    {
        $buildsPath = $this->buildsPath;

        $finder = Finder::create()->in($buildsPath)->files()->name('/^Build.*?(\\d+)(.*?)\.php$/');
        $finder->sortByName();
        $startIds = [];

        $builds = [];

        foreach ($finder as $file) {
            /* @var $file \SplFileInfo */

            $buildId = Strings::extractRegexMatch('/^Build.*?(\\d+)(.*?)\.php$/', $file->getFilename());
            if (!$buildId) {
                continue;
            }

            // if this dir is a build-pack, we read files sequentually but get the 'id'
            // of the build based off of a start build ID.
            // it allows the whole directory to be moved/renamed up/down the timeline easily (e.g, for big merges)
            $d = dirname($file->getRealPath());
            if (!isset($startIds[$d])) {
                if (file_exists($d.'/build-pack.txt')) {
                    $packinfo = Strings::parseEqualsLines(file_get_contents($d.'/build-pack.txt'));
                    if ($packinfo['start_build_id']) {
                        $startIds[$d] = $packinfo['start_build_id'];
                    }
                }
            }

            if (isset($startIds[$d])) {
                $buildId = $startIds[$d]++;
            }

            $trimPath  = str_replace(DP_ROOT, '', $file->getRealPath());
            $classname = 'Application\\InstallBundle\\Upgrade\\Build\\'.str_replace('.php', '', $file->getBasename());

            $classInfo = [
                'file'          => $trimPath,
                'classname'     => $classname,
                'isOnlineBuild' => false,
                'skipPostBuild' => false,
            ];

            require_once DP_ROOT.$trimPath;
            $refl                       = new \ReflectionClass($classname);
            $classInfo['isOnlineBuild'] = $refl->implementsInterface(OnlineBuildInterface::class);
            $classInfo['skipPostBuild'] = $refl->implementsInterface(SkipPostBuildInterface::class);

            if ($refl->implementsInterface(OnlineBuildInterface::class) && $refl->implementsInterface(BlockingBuildInterface::class)) {
                throw new \InvalidArgumentException("{$classname} implements both OnlineBuildInterface and BlockingBuildInterface interfaces. You must choose one!");
            }

            $builds[$buildId] = $classInfo;
        }

        ksort($builds, \SORT_NUMERIC);

        return $builds;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        $indent = '    ';

        $year = date('Y');

        $header = <<<CODE
<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) $year, DeskPRO Ltd.
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

CODE;

        $file   = [];
        $file[] = $header.PHP_EOL.'return [';

        $buildsArray = $this->getBuildsArray();
        foreach ($buildsArray as $buildId => $buildInfo) {
            $row = $indent.$buildId.' => ['.PHP_EOL;
            $row .= $indent.$indent."'file'          => '".$buildInfo['file']."',".PHP_EOL;
            $row .= $indent.$indent."'classname'     => '".$buildInfo['classname']."',".PHP_EOL;
            $row .= $indent.$indent."'skipPostBuild' => ".($buildInfo['skipPostBuild'] ? 'true' : 'false').','.PHP_EOL;
            $row .= $indent.$indent."'isOnlineBuild' => ".($buildInfo['isOnlineBuild'] ? 'true' : 'false').','.PHP_EOL;
            $row .= $indent.']';
            $row .= ',';

            $file[] = $row;
        }

        $file[] = '];'.PHP_EOL;

        $file = implode(PHP_EOL, $file);

        return $file;
    }
}
