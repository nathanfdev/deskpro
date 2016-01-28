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

class ActiveBuildFinder
{
    /**
     * @var string
     */
    private $baseapp_dir;

    /**
     * @var string
     */
    private $cache_dir;

    /**
     * @var \DpRun\ConfigReader
     */
    private $config_reader;

    /**
     * ActiveBuildFinder constructor.
     *
     * @param ConfigReader $config_reader
     * @param string       $baseapp_dir
     */
    public function __construct(ConfigReader $config_reader, $baseapp_dir)
    {
        $this->config_reader = $config_reader;
        $this->baseapp_dir   = $baseapp_dir;
    }

    /**
     * @return string
     */
    public function findActiveBuild()
    {
        $iter   = new \FilesystemIterator($this->baseapp_dir);
        $builds = [];

        /** @var \SplFileInfo $f */
        foreach ($iter as $f) {
            if ($f->isDir() && $f->getBasename() !== 'run') {
                $time_file = $f->getRealPath().'/sys/config/build-time.txt';
                if (file_exists($time_file)) {
                    $time          = intval(trim(file_get_contents($time_file)));
                    $builds[$time] = $f->getBasename();
                }
            }
        }

        if (!$builds) {
            throw new \RuntimeException('There are no builds available');
        }

        ksort($builds, SORT_NUMERIC);

        return $newest_build;
    }

    private function findDbBuild()
    {
        require_once __DIR__.'/LowUtil.php';
    }
}
