<?php

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
