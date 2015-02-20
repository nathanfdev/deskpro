<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DpBehat;


use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Symfony\Component\Filesystem\Filesystem;

class BehatHooksContext extends BasePortalContext
{
    private static $warmed_up_cache = false;

    /**
     * @BeforeSuite
     */
    public static function deleteCacheFolder(BeforeSuiteScope $scope)
    {
        $cache = self::getCacheDir();

        $fs = new Filesystem();
        if (is_dir($cache)) {
            $fs->remove($cache);
        }
        $fs->mkdir($cache, 0777);
        $fs->mkdir($cache . '/annotations', 0777);
        print "made new test folder " . (time() - (int)DP_TESTS_START_TIME) . " seconds in";
    }

    /**
     * @BeforeScenario
     */
    public function warmupCache()
    {
        if (!self::$warmed_up_cache) {
            $this->getContainer()->get('dataset_manager')->install('empty');

            $warmer = $this->getContainer()->get('cache_warmer');
            //$warmer->enableOptionalWarmers();
            $warmer->warmUp(self::getCacheDir());
            self::$warmed_up_cache = true;

            print "finished warming cache " . (time() - (int)DP_TESTS_START_TIME) . " seconds in";
        }
    }

    /**
     * @return string
     */
    private static function getCacheDir()
    {
        return DP_ROOT . '/sys/cache/portal/test';
    }
}
