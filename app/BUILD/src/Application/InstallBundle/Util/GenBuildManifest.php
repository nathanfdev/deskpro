<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\InstallBundle\Util;

use Orb\Util\Strings;
use Symfony\Component\Finder\Finder;

class GenBuildManifest
{
    /**
     * @var string
     */
    private $builds_path;

    /**
     * @var array
     */
    private $add = array();

    public function __construct($builds_path, array $add = null)
    {
        $this->builds_path = $builds_path;
        if ($add) {
            $this->add = $add;
        }
    }

    /**
     * @return array
     */
    public function getBuildsArray()
    {
        $builds_path = $this->builds_path;

        $finder = Finder::create()->in($builds_path)->files()->name('/^Build(\\d+)\.php$/');

        $builds = array();

        foreach ($finder as $file) {
            /* @var $file \SplFileInfo */

            $build_id = Strings::extractRegexMatch('/^Build(\\d+)\.php$/', $file->getFilename());
            if (!$build_id) {
                continue;
            }

            $trim_path = str_replace(DP_ROOT, '', $file->getRealPath());
            $classname = 'Application\\InstallBundle\\Upgrade\\Build\\'.str_replace('.php', '', $file->getBasename());

            $builds[$build_id] = array(
                'file'      => $trim_path,
                'classname' => $classname,
            );
        }

        if ($this->add) {
            foreach ($this->add as $filepath) {
                $file = new \SplFileInfo($filepath);

                $build_id  = Strings::extractRegexMatch('/^Build(\\d+)\.php$/', $file->getFilename());
                $trim_path = str_replace(DP_ROOT, '', $file->getRealPath());
                $classname = 'Application\\InstallBundle\\Upgrade\\Build\\'.str_replace('.php', '', $file->getBasename());

                if ($build_id) {
                    $builds[$build_id] = array(
                        'file'      => $trim_path,
                        'classname' => $classname,
                    );
                }
            }
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

        $header = <<<CODE
<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

        $file   = array();
        $file[] = $header.PHP_EOL.'return array(';

        $builds_array       = $this->getBuildsArray();
        $builds_array_count = count($builds_array);
        foreach ($builds_array as $build_id => $build_info) {
            $row = $indent.$build_id.' => array('.PHP_EOL;
            $row .= $indent.$indent."'file'      => '".$build_info['file']."',".PHP_EOL;
            $row .= $indent.$indent."'classname' => '".$build_info['classname']."',".PHP_EOL;
            $row .= $indent.')';
            $row .= ',';

            $file[] = $row;
        }

        $file[] = ');'.PHP_EOL;

        $file = implode(PHP_EOL, $file);

        return $file;
    }
}
