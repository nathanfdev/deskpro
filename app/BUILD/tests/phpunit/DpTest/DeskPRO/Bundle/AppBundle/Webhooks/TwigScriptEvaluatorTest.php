<?php

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
            ["twig:{{webhook.data.twig}}", "yes", new WebhookInvocation([], "", [], "", ['twig' => 'yes'])]
        ];
        $evaluator = new TwigScriptEvaluator();
        foreach ($values as $trial) {
            list($script, $expected, $invocation) = $trial;
            $actual = $evaluator->evaluate($invocation, $script);

            $this->assertEquals($expected, $actual);
        }
    }
}
