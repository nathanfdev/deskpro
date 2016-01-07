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

namespace DpBehat\Performance;

use Behat\Behat\Context\SnippetAcceptingContext;
use Sanpi\Behatch\Context\BaseContext;

/**
 * Class PerformanceContext.
 */
class PerformanceContext extends BaseContext implements SnippetAcceptingContext
{
    /**
     * @var float
     */
    private $timer_started;

    /**
     * @var string
     */
    private $timer_target;

    /**
     * @Given I start a timer for the :timer_target feature
     * @Given I started a timer for the :timer_target feature
     * @Given I start a timer for the :timer_target process
     * @Given I started a timer for the :timer_target process
     */
    public function iStartTimer($timer_target)
    {
        $this->timer_target  = $timer_target;
        $this->timer_started = microtime(true);
    }

    /**
     * @Then the timer should not exceed :num seconds
     */
    public function timerShouldNotExceed($num)
    {
        $actual = microtime(true) - $this->timer_started;
        $status = $actual < $num ? 'OK' : 'Fail!';
        echo "\nPerformance: {$this->timer_target} took $actual sec [Limit: $num, $status]\n";
        $this->assertTrue($actual < $num);
    }
}
