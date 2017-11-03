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

namespace DpTest\DeskPRO\Bundle\AppBundle\Webhooks;

use DeskPRO\Bundle\AppBundle\Webhooks\TwigScriptEvaluator;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookException;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookInvocation;
use DpTest\DeskProTestCase;

class TwigScriptEvaluatorTest extends DeskProTestCase
{
    public function testCanEvaluateReturnsFalse()
    {
        $values = [
            1,
            "",
            "{{twig}}",
            "twig:{{twig}",
        ];
        $evaluator = new TwigScriptEvaluator();
        foreach ($values as $script) {
            $actual = $evaluator->canEvaluate($script);
            $this->assertFalse($actual);
        }
    }

    public function testCanEvaluateReturnsTrue()
    {
        $values = [
            "twig:Hello webhook {{twig}}"
        ];
        $evaluator = new TwigScriptEvaluator();
        foreach ($values as $script) {
            $actual = $evaluator->canEvaluate($script);
            $this->assertTrue($actual);
        }
    }

    public function testEvaluateThrowsException()
    {
        $invocation = new WebhookInvocation([], "", [], "", []);

        $values = [
            "twig:{{twig}"
        ];
        $evaluator = new TwigScriptEvaluator();
        foreach ($values as $script) {
            $exception = null;
            try {
                $evaluator->evaluate($invocation, $script);
            } catch (WebhookException $e) {
                $exception = $e;
            }

            $this->assertNotNull($exception);
        }
    }

    public function testEvaluateReturnsExpectedResult()
    {
        $values = [
            ["twig:{{data.twig}}", "yes", new WebhookInvocation([], "", [], "", ['twig' => 'yes'])]
        ];
        $evaluator = new TwigScriptEvaluator();
        foreach ($values as $trial) {
            list($script, $expected, $invocation) = $trial;
            $actual = $evaluator->evaluate($invocation, $script);

            $this->assertEquals($expected, $actual);
        }
    }
}
