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
