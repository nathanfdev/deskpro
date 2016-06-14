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

namespace Application\DeskPRO\CacheClearer;

use Orb\Util\Env;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\Process\Process;

class CacheDirClearer implements CacheClearerInterface
{
    public function clear($cache_dir)
    {
        $cwd = getcwd();

        $cache_dir = rtrim(str_replace('\\', '/', $cache_dir), '/');

        $cache_dir_name = trim(basename($cache_dir), '_');

        $dirs = array(
            DP_ROOT.'/sys/cache',
            dp_get_cache_dir(),
        );

        $dirs = array_unique($dirs);

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            chdir($dir);
            $sub_dirs = glob('*', GLOB_ONLYDIR);

            foreach ($sub_dirs as $d) {
                if (trim($d, '_') == $cache_dir_name) {
                    continue;
                }

                if (Env::isWindows()) {
                    $p = new Process("rmdir /s /q $d", $dir);
                } else {
                    $p = new Process("rm -rf $d", $dir);
                }

                $p->run();
            }
        }

        chdir($cwd);

        // Also reset memory because we'll need it when the cache is re-warmed in a second
        @ini_set('memory_limit', '384M');
    }
}
