<?php

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
