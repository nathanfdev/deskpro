<?php

namespace DpTest\Bundle\AppBundle\TicketFilters\Terms;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Person as PersonRepos;
use Application\DeskPRO\NewSearch\Manager\Elasticsearch;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\PersonTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketBasicTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketSqlMatcher;
use DpTest\Bundle\AppBundle\TicketFilters\AbstractTicketSqlMatcherTest;
use DpTestSrc\TestBundle\Mock\Dbal\ConnectionMock;

require_once __DIR__.'/../AbstractTicketSqlMatcherTest.php';

class PersonSqlTest extends AbstractTicketSqlMatcherTest
{
    protected function setUpMatcher()
    {
        $person = $this->getMockBuilder(Person::class)
            ->disableArgumentCloning()
            ->disableOriginalConstructor()
            ->getMock();
        $person->method('getId')->willReturn('888');

        $repos = $this->getMockBuilder(PersonRepos::class)
            ->disableArgumentCloning()
            ->disableOriginalConstructor()
            ->getMock();
        $repos->method('findOneByEmail')
            ->willReturn($person);

        $this->matcher = new TicketSqlMatcher($this->valueResolver, [
            new TicketBasicTermsHandler($this->getMockBuilder(Elasticsearch::class)->disableOriginalConstructor()->getMock()),
            new PersonTermsHandler($repos),
        ], ConnectionMock::create(), TicketSqlMatcher::ACTIVE);

        $this->matcher->disableContextPermissions();
    }

    public function test_id()
    {
        $this->assertEqualQuery(
            'ticket.id = 1 AND ticket.person = 1',
            'SELECT COUNT(*) AS count
            FROM tickets_search_active tickets
            WHERE (tickets.id = :c0) AND (tickets.person_id = :c1)
            ',
            ['c0' => 1, 'c1' => 1]
        );
    }

    public function test_email()
    {
        $this->assertEqualQuery(
            'ticket.id = 1 AND ticket.person = "foo@foo.com"',
            'SELECT COUNT(*) AS count
            FROM tickets_search_active tickets
            WHERE (tickets.id = :c0) AND (tickets.person_id = :c1)
            ',
            ['c0' => 1, 'c1' => 888]
        );
    }
}
