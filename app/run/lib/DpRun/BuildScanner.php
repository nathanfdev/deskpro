<?php

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
