<?php

namespace DpTest\DeskPRO\Bundle\ReportBundle\Dpql2;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContext;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContextStorage;

/**
 * Class CompilerDateTest.
 */
class CompilerDateTest extends AbstractCompilerTest
{
    public function test_hours_added_when_using_timezone()
    {
        $person = new Person();
        $person->setTimezone('Europe/Moscow');
        $this->context = new DpqlContext($person);

        //we need to calculate date programmatically cause we can just face with daylight savings
        $dateString   = '2010-06-10 12:00:00';
        $dateModified = $this->getModifiedDate('Europe/Moscow', $dateString);

        $this->assertDpqlQuery(
            <<<DPQL
SELECT tickets.date_created FROM tickets WHERE tickets.date_created > '$dateString'
DPQL
            ,
            <<<SQL
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`date_created`
FROM `tickets`
WHERE (`tickets`.`date_created` > '$dateModified')
LIMIT 2500
SQL
            ,
            DpqlContextStorage::MODE_RUN
        );
    }

    public function test_hours_substracted_when_using_timezone()
    {
        $person = new Person();
        $person->setTimezone('America/New_York');
        $this->context = new DpqlContext($person);

        //we need to calculate date programmatically cause we can just face with daylight savings
        $dateString   = '2010-06-10 12:00:00';
        $dateModified = $this->getModifiedDate('America/New_York', $dateString);

        $this->assertDpqlQuery(
            <<<DPQL
SELECT tickets.date_created FROM tickets WHERE tickets.date_created > '$dateString'
DPQL
            ,
            <<<SQL
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`date_created`
FROM `tickets`
WHERE (`tickets`.`date_created` > '$dateModified')
LIMIT 2500
SQL
            ,
            DpqlContextStorage::MODE_RUN
        );
    }

    public function test_timezone_applied_for_placeholders()
    {
        $person = new Person();
        $person->setTimezone('Europe/Moscow');
        $this->context = new DpqlContext($person);

        //we need to calculate date programmatically cause we can just face with daylight savings
        $dateStart = new \DateTime('today midnight');
        $dateEnd   = new \DateTime('tomorrow midnight');
        $dateEnd->modify('-1 second');

        $dateModifiedStart       = $this->getModifiedDate('Europe/Moscow', $dateStart->format('Y-m-d H:i:s'));
        $dateModifiedBeforeStart = $this->getModifiedDate('Europe/Moscow', $dateStart->modify('-1 second')->format('Y-m-d H:i:s'));
        $dateModifiedEnd         = $this->getModifiedDate('Europe/Moscow', $dateEnd->format('Y-m-d H:i:s'));

        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.date_created FROM tickets WHERE tickets.date_created = %TODAY%
DPQL
            ,
            <<<SQL
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`date_created`
FROM `tickets`
WHERE ((`tickets`.`date_created` > '$dateModifiedBeforeStart' AND `tickets`.`date_created` BETWEEN '$dateModifiedStart' 
AND '$dateModifiedEnd') OR `tickets`.`date_created` > NOW())
LIMIT 2500
SQL
);
    }

    private function getModifiedDate($timeZone, $dateString)
    {
        $dateTZ = new \DateTimeZone($timeZone);
        $date   = new \DateTime($dateString);

        return $date->modify((-$dateTZ->getOffset(new \DateTime('now'))).' seconds')->format('Y-m-d H:i:s');
    }
}
