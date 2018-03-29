<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery
 */
class DbalExecutableQuerySpec extends ObjectBehavior
{
    public function let(DbalQuery $query, Connection $connection)
    {
        $this->beConstructedWith($query, $connection);
    }

    public function it_holds_query_config_data()
    {
        $this->getOrderBy()->shouldBe([]);
        $this->getPage()->shouldBe(1);
        $this->getCount()->shouldBe(null);
        $this->getAndWhere()->shouldBe([]);
        $this->getAndGroupWhere()->shouldBe([]);

        $this->addOrderBy('department', 'DESC');
        $this->getOrderBy()->shouldBe(['department' => 'DESC']);

        $this->setPage(4);
        $this->getPage()->shouldBe(4);

        $this->setCount(10);
        $this->getCount()->shouldBe(10);

        $this->addAndWhere('{from}.department = 5');
        $this->getAndWhere()->shouldBe(['{from}.department = 5']);

        $this->addAndGroupWhere('department', 5);
        $this->getAndGroupWhere()->shouldBe(['department' => 5]);
    }

    public function it_will_run_a_count_query(
        DbalQuery $query,
        Connection $connection,
        Statement $stmt
    ) {
        $query->setSelectPart('COUNT(distinct ticket.id) AS count')->shouldBeCalled();
        $query->setOffset(null)->shouldBeCalled();
        $query->setPage(null)->shouldBeCalled();
        $query->setLimit(null)->shouldBeCalled();

        $query->__toString()->willReturn(
            'SELECT COUNT(distinct ticket.id) as count FROM tickets ticket WHERE ticket.param = :param1'
        );
        $query->getParameters()->willReturn(['param1' => 4]);

        $stmt->fetch()->willReturn(['count' => 5]);
        $stmt->rowCount()->willReturn(5);

        $connection->executeQuery(
            'SELECT COUNT(distinct ticket.id) as count FROM tickets ticket WHERE ticket.param = :param1',
            ['param1' => 4],
            Argument::type('array')
        )->willReturn($stmt);

        $this->fetchCount()->shouldReturn(5);
    }

    public function it_will_execute_a_select_ids_query(
        DbalQuery $query,
        Connection $connection,
        Statement $stmt
    ) {
        $query->setSelectPart('distinct {from}.id')->shouldBeCalled();
        $query->__toString()->willReturn(
            'SELECT ticket.id FROM tickets ticket WHERE ticket.param = :param1'
        );
        $query->getParameters()->willReturn(
            ['param1' => 4]
        );

        $connection->executeQuery(
            'SELECT ticket.id FROM tickets ticket WHERE ticket.param = :param1',
            ['param1' => 4],
            Argument::type('array')
        )->willReturn($stmt);

        $stmt->fetchAll()->willReturn([['id' => 2], ['id' => 3]]);
        $stmt->rowCount()->willReturn(5);
        $this->fetchIds()->shouldReturn([2, 3]);
    }

    public function it_will_execute_a_select_ids_query_with_pagination_custom_where_and_sorting_applied(
        DbalQuery $query,
        Connection $connection,
        Statement $stmt
    ) {
        $query->setSelectPart('distinct {from}.id')->shouldBeCalled();
        $query->__toString()->willReturn(
            'SELECT ticket.id FROM tickets ticket WHERE ticket.param = :param1 AND ticket.test = true ORDER BY ticket.id LIMIT 5,10'
        );
        $query->getParameters()->willReturn(
            ['param1' => 4]
        );

        $connection->executeQuery(
            'SELECT ticket.id FROM tickets ticket WHERE ticket.param = :param1 AND ticket.test = true ORDER BY ticket.id LIMIT 5,10',
            ['param1' => 4],
            Argument::type('array')
        )->willReturn($stmt);

        $stmt->fetchAll()->willReturn([['id' => 2], ['id' => 3]]);
        $stmt->rowCount()->willReturn(5);
        $this->fetchIds([
            'order_by' => [
                '{from}.id' => 'DESC',
            ],
            'page'     => 2,
            'count'    => 5,
            'andWhere' => [
                '{from}.test = true',
            ],
            'andGroupWhere' => [
                'agent' => 5,
            ],
        ])->shouldReturn([2, 3]);
    }

    public function it_will_resolve_parameter_types_for_dbal_execute()
    {
        $input = [
            'a' => 4,
            'b' => '5',
            'c' => 'string',
            'd' => [
                'of',
                'stuff',
            ],
            'e' => [
                1,
                2,
                3,
            ],
        ];

        $this->determineParameterTypes($input)->shouldReturn([
            'a' => \PDO::PARAM_INT,
            'b' => \PDO::PARAM_STR,
            'c' => \PDO::PARAM_STR,
            'd' => Connection::PARAM_STR_ARRAY,
            'e' => Connection::PARAM_INT_ARRAY,
        ]);
    }
}
