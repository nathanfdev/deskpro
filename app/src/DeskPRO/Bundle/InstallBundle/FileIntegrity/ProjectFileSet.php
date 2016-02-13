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

namespace DeskPRO\Bundle\InstallBundle\FileIntegrity;

use DeskPRO\Component\Util\ListUtils;
use Symfony\Component\Finder\Finder;

/**
 * Builds a list of known files in the project using variable paths.
 */
class ProjectFileSet
{
    /**
     * @var \DpRun\DpEnv
     */
    private $env;

    /**
     * @var array
     */
    private $replacements;

    /**
     * ProjectFileSet constructor.
     *
     * @param \DpRun\DpEnv $env
     */
    public function __construct(\DpRun\DpEnv $env)
    {
        $this->env = $env;
    }

    /**
     * Given a path that has path vars in it, get the real path
     * based on the current enviornment.
     *
     * @param string $path
     *
     * @return string
     */
    public function getRealPath($path)
    {
        if (!$this->replacements) {
            $this->replacements = [
                'find'    => [],
                'replace' => [],
            ];

            $this->replacements['find'][]    = '%DP_DIR%';
            $this->replacements['replace'][] = $this->env->getDpRoot();

            $this->replacements['find'][]    = '%DP_APP_DIR%';
            $this->replacements['replace'][] = $this->env->getAppDir();

            $this->replacements['find'][]    = '%DP_APP_KERNEL_CACHE%';
            $this->replacements['replace'][] = $this->env->getAppBaseKernelCacheDir();

            $this->replacements['find'][]    = '%DP_APP_WWW_ASSET%';
            $this->replacements['replace'][] = $this->env->getAppWwwAssetDir();
        }

        return str_replace(
            $this->replacements['find'],
            $this->replacements['replace'],
            $path
        );
    }

    /**
     * Scans for all files in the project.
     *
     * @return array
     */
    public function buildFileSet()
    {
        $sets = [];

        #------------------------------
        # DpRun type files
        #------------------------------

        $finder = Finder::create()
            ->files()
            ->in($this->env->getDpRoot().DIRECTORY_SEPARATOR.'/app/run')
            ->in($this->env->getDpRoot().DIRECTORY_SEPARATOR.'bin');

        $sets[] = $this->readIterator($finder, $this->env->getDpRoot(), '%DP_DIR%');

        #------------------------------
        # Current build files
        #------------------------------

        $finder = Finder::create()
            ->files()
            ->in($this->env->getAppDir());

        $sets[] = $this->readIterator($finder, $this->env->getAppDir(), '%DP_APP_DIR%');

        #------------------------------
        # Current build kernel cache files
        #------------------------------

        $finder = Finder::create()
            ->files()
            ->in($this->env->getAppBaseKernelCacheDir());

        $sets[] = $this->readIterator($finder, $this->env->getAppBaseKernelCacheDir(), '%DP_APP_KERNEL_CACHE%');

        #------------------------------
        # Asset files
        #------------------------------

        $finder = Finder::create()
            ->files()
            ->in($this->env->getAppWwwAssetDir());

        $sets[] = $this->readIterator($finder, $this->env->getAppWwwAssetDir(), '%DP_APP_WWW_ASSET%');

        return ListUtils::appendListOfLists($sets);
    }

    private function readIterator($iter, $base_path, $varname)
    {
        $set = [];

        /** @var \SplFileInfo $f */
        foreach ($iter as $f) {
            $path  = str_replace($base_path, $varname, $f->getRealPath());
            $set[] = $path;
        }

        return $set;
    }
}
