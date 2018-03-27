<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery
 */
class DbalQuerySpec extends ObjectBehavior
{
    public function it_handles_select_from_part()
    {
        $this->getFromTable()->shouldBe(null);
        $this->setFromTable('tickets');
        $this->getFromTable()->shouldBe('tickets');

        $this->generateFromString()->shouldBe('tickets');
        $this->__toString()->shouldBe('SELECT * FROM tickets');

        $this->setFromAlias('ticket');
        $this->getFromAlias()->shouldBe('ticket');

        $this->generateFromString()->shouldBe('tickets ticket');
        $this->__toString()->shouldBe('SELECT * FROM tickets ticket');

        $this->setFrom('departments', 'department');
        $this->generateFromString()->shouldBe('departments department');
        $this->__toString()->shouldBe('SELECT * FROM departments department');
    }

    public function it_handles_select_part()
    {
        $this->generateSelectString()->shouldBe('*');
        $this->setSelectPart('{from}.id');

        $this->setFrom('tickets');
        $this->__toString()->shouldBe('SELECT tickets.id FROM tickets');
    }

    public function it_allows_appending_select_pieces()
    {
        $this->setSelectPart('{from}.id');
        $this->addSelectPart('{from}.department_id AS department');

        $this->setFrom('tickets', 'ticket');
        $this->__toString()->shouldBe('SELECT ticket.id, ticket.department_id AS department FROM tickets ticket');
    }

    public function it_handles_where_part()
    {
        $this->generateWhereString()->shouldBe(null);
        $this->setWherePart('{from}.id = 4');
        $this->generateWhereString()->shouldBe('{from}.id = 4');

        $this->setFrom('tickets');

        $this->__toString()->shouldBe('SELECT * FROM tickets WHERE (tickets.id = 4)');
    }

    public function it_handles_shared_joins_using_no_alias_be_default()
    {
        $this->setFrom('tickets');

        $this->addJoin('people_emails', 'people_emails.person_id = {from}.person_id');
        $this->addJoin('departments', 'departments.ticket_id = {from}.id');

        $expected_join_string = 'LEFT JOIN people_emails ON (people_emails.person_id = {from}.person_id) LEFT JOIN departments ON (departments.ticket_id = {from}.id)';

        $this->generateJoinString()->shouldBe($expected_join_string);

        $this->__toString()->shouldBe(
            'SELECT * FROM tickets LEFT JOIN people_emails ON (people_emails.person_id = tickets.person_id) LEFT JOIN departments ON (departments.ticket_id = tickets.id)'
        );
    }

    public function it_handles_shared_joins_that_specify_an_alias()
    {
        $this->setFrom('tickets');

        $this->addJoin('people_emails', 'pe.person_id = {from}.person_id', 'pe');
        $this->addJoin('departments', 'dp.ticket_id = {from}.id', 'dp');

        $expected_join_string = 'LEFT JOIN people_emails pe ON (pe.person_id = {from}.person_id) LEFT JOIN departments dp ON (dp.ticket_id = {from}.id)';

        $this->generateJoinString()->shouldBe($expected_join_string);

        $this->__toString()->shouldBe(
            'SELECT * FROM tickets LEFT JOIN people_emails pe ON (pe.person_id = tickets.person_id) LEFT JOIN departments dp ON (dp.ticket_id = tickets.id)'
        );
    }

    public function it_allows_unique_joins()
    {
        $this->setFrom('tickets');

        $this->addJoin('people_emails', 'people_emails.person_id = {from}.person_id');

        $join_alias = $this->addUniqueJoin(
            'custom_def_people',
            '{alias}.person_id = {from}.person_id',
            DbalQuery::JOIN_INNER
        );

        if (strlen($join_alias->getWrappedObject()) <= 0) {
            throw new \Exception('no join alias returned');
        }

        $this->generateUniqueJoinString()->shouldReturn(
            'INNER JOIN custom_def_people custom_def_people_0 ON (custom_def_people_0.person_id = {from}.person_id)'
        );
    }

    public function it_can_accept_group_bys()
    {
        $this->setFrom('tickets', 't');
        $this->addGroupBy('{from}.subject');

        $this->getGroupBy()->shouldBeLike(['{from}.subject']);
        $this->generateGroupByString()->shouldBe('{from}.subject');
        $this->__toString()->shouldBe('SELECT * FROM tickets t GROUP BY t.subject WITH ROLLUP');

        $this->addGroupBy('{from}.date_created');

        $this->getGroupBy()->shouldBeLike(['{from}.subject', '{from}.date_created']);
        $this->generateGroupByString()->shouldBe('{from}.subject, {from}.date_created');
        $this->__toString()->shouldBe('SELECT * FROM tickets t GROUP BY t.subject, t.date_created WITH ROLLUP');
    }

    public function it_can_accept_order_bys()
    {
        $this->setFrom('tickets', 't');
        $this->addOrderBy(
            '{from}.subject',
            \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery::ORDER_ASC
        );

        $this->getOrderBy()->shouldBeLike(
            [
                [
                    '{from}.subject',
                    \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery::ORDER_ASC,
                ],
            ]
        );
        $this->generateOrderByString()->shouldBe('{from}.subject ASC');
        $this->__toString()->shouldBe('SELECT * FROM tickets t ORDER BY t.subject ASC');

        $this->addOrderBy('{from}.date_created', DbalQuery::ORDER_DESC);

        $this->getOrderBy()->shouldBeLike(
            [
                ['{from}.subject', DbalQuery::ORDER_ASC],
                [
                    '{from}.date_created',
                    \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery::ORDER_DESC,
                ],
            ]
        );
        $this->generateOrderByString()->shouldBe('{from}.subject ASC, {from}.date_created DESC');
        $this->__toString()->shouldBe('SELECT * FROM tickets t ORDER BY t.subject ASC, t.date_created DESC');
    }

    public function it_accepts_a_limit()
    {
        $this->getLimit()->shouldBe(null);

        $this->setLimit(10);

        $this->getLimit()->shouldBe(10);

        $this->setFrom('tickets', 't');

        $this->__toString()->shouldReturn('SELECT * FROM tickets t LIMIT 10');
    }

    public function it_accepts_a_page_that_works_with_limit()
    {
        $this->setPage(3);

        $this->getPage()->shouldBe(3);

        $this->setFrom('tickets');
        $this->setLimit(10);

        $this->getPageOffset()->shouldBe(20);
        $this->generateLimitString()->shouldBe('20, 10');
        $this->__toString()->shouldBe('SELECT * FROM tickets LIMIT 20, 10');

        $this->setLimit(15);

        $this->getPageOffset()->shouldBe(30);
        $this->generateLimitString()->shouldBe('30, 15');
        $this->__toString()->shouldBe('SELECT * FROM tickets LIMIT 30, 15');
    }

    public function it_starts_page_at_one()
    {
        $this->getPage()->shouldBe(1);

        $this->setFrom('tickets');
        $this->setLimit(10);

        $this->getPageOffset()->shouldBe(0);
        $this->generateLimitString()->shouldBe('10');
        $this->__toString()->shouldBe('SELECT * FROM tickets LIMIT 10');
    }

    public function it_only_uses_page_if_limit_is_set()
    {
        $this->getLimit()->shouldBe(null);

        $this->setPage(2);
        $this->setFrom('tickets');
        $this->getPageOffset()->shouldBe(0);

        $this->generateLimitString()->shouldBe('');
        $this->__toString()->shouldBe('SELECT * FROM tickets');
    }

    public function it_lets_you_add_parameters_and_returns_your_parameter_name()
    {
        $this->setFrom('tickets');

        $p1_name = $this->addParameter('name', 'value');
        $p2_name = $this->addParameter('name', 'other value');
        $p3_name = $this->addParameter('other_name', 'bar');

        // you get the param names from the addParameter call. your "name" is just a prefix but not the actual parameter name
        $this->setWherePart(
            't.id = :'.$p1_name->getWrappedObject().' OR t.subject = :'.$p2_name->getWrappedObject(
            ).' AND t.name = :'.$p3_name->getWrappedObject()
        );

        $this->__toString()->shouldBe(
            'SELECT * FROM tickets WHERE (t.id = :name_0 OR t.subject = :name_1 AND t.name = :other_name_0)'
        );
    }

    public function it_lets_you_add_query_params_and_returns_the_name_of_the_param()
    {
        $param1 = $this->addParameter('param', 1);
        $param2 = $this->addParameter('param', 'bar');

        $param1->shouldBe('param_0');
        $param2->shouldBe('param_1');

        $this->getParameters()->shouldBeLike(
            [
                'param_0' => 1,
                'param_1' => 'bar',
            ]
        );

        $param3 = $this->addParameter('new_param', 'new value');

        $param3->shouldBe('new_param_0');

        $this->getParameters()->shouldBeLike(
            [
                'param_0'     => 1,
                'param_1'     => 'bar',
                'new_param_0' => 'new value',
            ]
        );
    }

    public function it_throws_an_exception_if_you_set_a_non_existant_param()
    {
        $this->shouldThrow('\InvalidArgumentException')
            ->during(
                'replaceParameter',
                ['new_param', 'new value']
            );
    }

    public function it_also_allows_a_term_engine_expression_as_a_param_value()
    {
        $this->setFrom('agents', 'agent');

        $p1_name = $this->addParameter('my_name', new TermEngineExpression('agent.name'));

        $this->setWherePart('agent.name = :'.$p1_name->getWrappedObject());

        $this->__toString()->shouldBe(
            'SELECT * FROM agents agent WHERE (agent.name = :my_name_0)'
        );
    }

    public function it_lets_you_get_and_manipulate_parameters()
    {
        $agent_p_name = $this->addParameter('agent', 1);
        $time_p_name  = $this->addParameter('time', new TermEngineExpression('agent.dateLastLogin'));
        $foo_p_name   = $this->addParameter('foo', 'bar');

        // remember, the name is generated, so it wont be 'agent'
        $this->replaceParameter($agent_p_name, 2);

        // note that you are not guaranteed the exact same expression object, but it will
        // have the same value.
        $this->getParameter($time_p_name)->shouldBeLike(new TermEngineExpression('agent.dateLastLogin'));

        $this->replaceParameter($foo_p_name, new TermEngineExpression('func(my.expression)'));

        $this->getParameters()->shouldBeLike(
            [
                'agent_0' => 2,
                'time_0'  => new TermEngineExpression('agent.dateLastLogin'),
                'foo_0'   => new TermEngineExpression('func(my.expression)'),
            ]
        );
    }

    public function it_does_allow_you_to_append_to_the_where_string()
    {
        $this->setWherePart($initial_where = '(agent.id = 5 AND agent.name = "Jim")');

        $this->generateWhereString()->shouldBe($initial_where);

        $this->appendWhere($appended = 'AND agent.fav_color = :fav_color_param');

        $this->generateWhereString()->shouldBe($initial_where.' '.$appended);

        $this->appendWhere($second_append = 'OR (x.foo = x.bar AND y.baz = 89');

        $this->generateWhereString()->shouldBe(
            $initial_where.' '.$appended.' '.$second_append
        );

        $this->setFrom('tickets');

        $this->__toString()->shouldBe(
            sprintf(
                'SELECT * FROM tickets WHERE (%s %s %s)',
                $initial_where,
                $appended,
                $second_append
            )
        );
    }

    public function it_does_everything_at_once()
    {
        $this->setSelectPart('{from}.id');
        $this->setFrom('tickets', 't');
        $this->setWherePart('{from}.id = 5');
        $this->addJoin('people_emails', 'people_emails.person_id = {from}.id');
        $this->addUniqueJoin(
            'custom_def_person',
            '{alias}.person_id = {from}.id',
            \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery::JOIN_INNER
        );
        $this->addGroupBy('{from}.date_created');
        $this->addGroupBy('{from}.id');
        $this->addOrderBy('{from}.id', DbalQuery::ORDER_DESC);
        $this->setLimit(15);
        $this->setPage(4);

        $this->__toString()->shouldBeLike(
            'SELECT t.id FROM tickets t LEFT JOIN people_emails ON (people_emails.person_id = t.id) INNER JOIN custom_def_person custom_def_person_0 ON (custom_def_person_0.person_id = t.id) WHERE (t.id = 5) GROUP BY t.date_created, t.id WITH ROLLUP ORDER BY t.id DESC LIMIT 45, 15'
        );
    }
}
