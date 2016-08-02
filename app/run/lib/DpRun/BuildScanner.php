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

namespace DpRun;

class BuildScanner
{
    /**
     * @var string
     */
    private $dir;

    /**
     * time => build_id
     * @var array
     */
    private $availableBuilds;

    /**
     * BuildScanner constructor.
     *
     * @param $dir
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * @return array Array of build_id=>dirname
     */
    public function getAvailableBuilds()
    {
        if ($this->availableBuilds !== null) {
            return $this->availableBuilds;
        }

        $iter   = new \FilesystemIterator($this->dir);
        $builds = [];

        /** @var \SplFileInfo $f */
        foreach ($iter as $f) {
            if ($f->isDir() && $f->getBasename() !== 'run') {
                $configDir = $f->getPathname().'/sys/config';
                $timeFile  = $f->getPathname().'/sys/config/build-time.txt';
                if (is_dir($configDir)) {
                    if (file_exists($timeFile)) {
                        $time = intval(trim(file_get_contents($timeFile)));
                    } else {
                        // This case wouldn't happen in prod because a build-time file
                        // will always exist. So this is a test case generally, or an error case
                        $time = count($builds);
                    }
                    $builds[$time] = $f->getBasename();
                }
            }
        }

        // Fallback on dev build if it exists
        if (!$builds && is_dir($this->dir.'/BUILD')) {
            $builds[time()] = 'BUILD';
        }

        if (!$builds) {
            throw new \RuntimeException('There are no builds available in: ' . $this->dir);
        }

        ksort($builds, SORT_NUMERIC);

        return $this->availableBuilds = $builds;
    }

    /**
     * @return string
     */
    public function getLatestBuildDir()
    {
        $builds = $this->getAvailableBuilds();
        end($builds);
        $last = current($builds);
        reset($builds);

        return $last;
    }
}
