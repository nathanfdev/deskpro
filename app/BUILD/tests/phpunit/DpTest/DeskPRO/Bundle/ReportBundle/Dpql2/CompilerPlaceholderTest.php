<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DpTest\DeskPRO\Bundle\ReportBundle\Dpql2;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContext;

/**
 * Class CompilerPlaceholderTest.
 */
class CompilerPlaceholderTest extends AbstractCompilerTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->context = new DpqlContext();
        $this->context->setDate(new \DateTime('2017-12-26 12:00:00'));
    }

    public function test_ever()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.date_created
FROM tickets
WHERE tickets.date_created = %EVER%
DPQL
            ,
            <<<'SQL'
SELECT `tickets`.`date_created`
FROM `tickets`
WHERE 1
LIMIT 2500
SQL
        );
    }

    /**
     * @param string $placeholder
     * @param string $date1
     * @param string $date2
     *
     * @dataProvider pastDateProvider
     */
    public function test_date_interval($placeholder, $date1, $date2)
    {
        $this->assertDpqlQuery(
            <<<DPQL
SELECT tickets.date_created
FROM tickets
WHERE tickets.date_created = %$placeholder%
DPQL
            ,
            <<<SQL
SELECT `tickets`.`date_created`
FROM `tickets`
WHERE (`tickets`.`date_created` BETWEEN '$date1' AND '$date2')
LIMIT 2500
SQL
        );
    }

    /**
     * @return array
     */
    public function pastDateProvider()
    {
        return [
            ['PAST_HOUR', '2017-12-26 11:00:00', '2017-12-26 12:00:00'],
            ['PAST_24_HOURS', '2017-12-25 12:00:00', '2017-12-26 12:00:00'],
            ['PAST_12_HOURS', '2017-12-26 00:00:00', '2017-12-26 12:00:00'],
            ['PAST_7_DAYS', '2017-12-19 00:00:00', '2017-12-26 12:00:00'],
            ['PAST_30_DAYS', '2017-11-26 00:00:00', '2017-12-26 12:00:00'],
            ['PAST_6_MONTHS', '2017-06-26 00:00:00', '2017-12-26 12:00:00'],
            ['PAST_12_MONTHS', '2016-12-26 00:00:00', '2017-12-26 12:00:00'],
            ['LAST_WEEK', '2017-12-18 00:00:00', '2017-12-24 23:59:59'],
            ['LAST_MONTH', '2017-11-01 00:00:00', '2017-11-30 23:59:59'],
            ['LAST_YEAR', '2016-01-01 00:00:00', '2016-12-31 23:59:59'],
            ['THIS_WEEK', '2017-12-25 00:00:00', '2017-12-31 23:59:59'],
            ['THIS_MONTH', '2017-12-01 00:00:00', '2017-12-31 23:59:59'],
            ['THIS_YEAR', '2017-01-01 00:00:00', '2017-12-31 23:59:59'],
            ['TODAY', '2017-12-26 00:00:00', '2017-12-26 23:59:59'],
            ['TOMORROW', '2017-12-27 00:00:00', '2017-12-27 23:59:59'],
            ['YESTERDAY', '2017-12-25 00:00:00', '2017-12-25 23:59:59'],
        ];
    }
}
