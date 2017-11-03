<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Domain\AppStorage;

use DeskPRO\Bundle\AppStoreBundle\Domain;
use DpTest\DeskProTestCase;

class AccessRuleTest extends DeskProTestCase
{
    public function testMatchesReturnTrueWhenMatchingExact()
    {
        $accessOption = new Domain\AppStorage\AccessOptions();

        $trials = [
            ['pattern' => 'foo.bar.', 'match' => 'foo.bar.'],
            ['pattern' => 'f', 'match' => 'f'],
        ];

        $pattern = null;
        $match   = null;
        foreach ($trials as $trial) {
            extract($trial, EXTR_OVERWRITE);

            $rule         = new Domain\AppStorage\AccessRule($pattern, $accessOption);
            $actualResult = $rule->matchesStateName($match);

            $this->assertTrue($actualResult, sprintf('%s should have matched %s', $pattern, $match));
        }
    }

    public function testMatchesReturnFalseWhenMatchingExact()
    {
        $accessOption = new Domain\AppStorage\AccessOptions();

        $trials = [
            ['pattern' => 'foo.', 'match' => 'foo.bar.'],
            ['pattern' => 'argo', 'match' => 'ogra'],
        ];

        $pattern = null;
        $match   = null;
        foreach ($trials as $trial) {
            extract($trial, EXTR_OVERWRITE);

            $rule         = new Domain\AppStorage\AccessRule($pattern, $accessOption);
            $actualResult = $rule->matchesStateName($match);

            $this->assertFalse($actualResult, sprintf('%s should not have matched %s', $pattern, $match));
        }
    }

    public function testMatchesReturnTrueWhenMatchingWithWildcards()
    {
        $accessOption = new Domain\AppStorage\AccessOptions();

        $trials = [
            ['pattern' => 'foo.bar.*', 'match' => 'foo.bar.'],
            ['pattern' => 'foo.bar.*', 'match' => 'foo.bar.*'],
            ['pattern' => 'foo.bar.*', 'match' => 'foo.bar.baz'],
            ['pattern' => 'foo.bar.*', 'match' => 'foo.bar.123'],
            ['pattern' => 'foo.bar.**', 'match' => 'foo.bar.*'],
            ['pattern' => 'foo.bar.**', 'match' => 'foo.bar.*123'],
            ['pattern' => 'foo.bar.**', 'match' => 'foo.bar.*baz'],
        ];

        $pattern = null;
        $match   = null;
        foreach ($trials as $trial) {
            extract($trial, EXTR_OVERWRITE);

            $rule         = new Domain\AppStorage\AccessRule($pattern, $accessOption);
            $actualResult = $rule->matchesStateName($match);

            $this->assertTrue($actualResult, sprintf('%s should have matched %s', $pattern, $match));
        }
    }

    public function testMatchesReturnFalseWhenMatchingWithWildcards()
    {
        $accessOption = new Domain\AppStorage\AccessOptions();

        $trials = [
            ['pattern' => 'foo.bar.*', 'match' => 'foo'],
            ['pattern' => 'foo.bar.*', 'match' => 'foo.bar'],
            ['pattern' => 'foo.bar.**', 'match' => 'foo.bar.'],
        ];

        $pattern = null;
        $match   = null;
        foreach ($trials as $trial) {
            extract($trial, EXTR_OVERWRITE);

            $rule         = new Domain\AppStorage\AccessRule($pattern, $accessOption);
            $actualResult = $rule->matchesStateName($match);

            $this->assertFalse($actualResult, sprintf('%s should not have matched %s', $pattern, $match));
        }
    }
}
