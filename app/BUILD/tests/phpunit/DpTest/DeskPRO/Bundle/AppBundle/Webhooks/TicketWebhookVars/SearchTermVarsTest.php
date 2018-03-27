<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Webhooks\WebhookExecutor;


use DeskPRO\Bundle\AppBundle\Webhooks\ScriptEvaluator;
use DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookVars\SearchTermVars;
use DpTest\DeskProTestCase;

class SearchTermVarsTest extends DeskProTestCase
{
    public function testHasVarsReturnsWhenNestedOptionValueIsSearchTermVar()
    {
        $options = [
            'level-1' => [
                'level-2.1' => [
                   'value-1' => 'no-parse',
                   'value-2' => 'no-parse',
                ],
                'level-2.2' => [
                   'level-2.3' => [
                       'value-3' => 'parse'
                   ]
                ]
            ]
        ];

        $mock = $this->getMockForAbstractClass(ScriptEvaluator::class);
        $mock->method('canEvaluate')->will($this->returnCallback(function ($value) {
            return $value === 'parse';
        }));

        $term = [
            'type' => 'not-important',
            'op' => 'not-important',
            'options' => $options
        ];
        $actual = SearchTermVars::hasVars($term, [$mock]);
        $this->assertTrue($actual);
    }
}
