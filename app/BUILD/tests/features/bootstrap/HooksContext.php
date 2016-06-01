<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DpBehat;

use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;

/**
 * Class HooksContext.
 */
class HooksContext extends BaseContext
{
    /**
     * @var array Feature file to time map
     */
    private static $timers = [];

    /**
     * @BeforeFeature
     */
    public static function startTimer(BeforeFeatureScope $scope)
    {
        self::$timers[$scope->getFeature()->getFile()] = -microtime(true);
    }

    /**
     * @AfterFeature
     */
    public static function stopTimer(AfterFeatureScope $scope)
    {
        self::$timers[$scope->getFeature()->getFile()] += microtime(true);
    }

    /**
     * @AfterSuite
     */
    public static function showTimers()
    {
        echo "Consider splitting slowest features into smaller ones, so that parallel runners have more equal load:\n";

        arsort(self::$timers);
        foreach (self::$timers as $file => $time) {
            $file = explode('app/BUILD/tests', $file)[1];
            echo $file, ': ', $time, " sec\n";
        }
    }

    /**
     * @BeforeScenario
     */
    public function speedUpDoctrine()
    {
        if ($this->container()->has('doctrine.orm.default_entity_manager')) {
            $this->em()->getConnection()->getConfiguration()->setSQLLogger(null);
            $this->em()->clear();
        }
    }
}
