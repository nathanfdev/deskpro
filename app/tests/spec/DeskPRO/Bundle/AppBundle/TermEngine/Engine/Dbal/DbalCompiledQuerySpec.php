<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalCompiledQuery;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalCompiledQuery
 */
class DbalCompiledQuerySpec extends ObjectBehavior
{
    function it_handles_select_from_part()
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

    function it_handles_select_part()
    {
        $this->generateSelectString()->shouldBe('*');
        $this->setSelectPart('{from}.id');

        $this->setFrom('tickets');
        $this->__toString()->shouldBe('SELECT tickets.id FROM tickets');
    }

    function it_handles_where_part()
    {
        $this->generateWhereString()->shouldBe(null);
        $this->setWherePart('{from}.id = 4');
        $this->generateWhereString()->shouldBe('{from}.id = 4');

        $this->setFrom('tickets');

        $this->__toString()->shouldBe('SELECT * FROM tickets WHERE tickets.id = 4');
    }

    function it_handles_shared_joins()
    {
        $this->setFrom('tickets');

        $this->addJoin('people_emails', 'people_emails.person_id = {from}.person_id');
        $this->addJoin('departments', 'departments.ticket_id = {from}.id');

        $expected_join_string = 'LEFT JOIN people_emails ON people_emails.person_id = {from}.person_id LEFT JOIN departments ON departments.ticket_id = {from}.id';

        $this->generateJoinString()->shouldBe($expected_join_string);

        $this->__toString()->shouldBe(
            'SELECT * FROM tickets LEFT JOIN people_emails ON people_emails.person_id = tickets.person_id LEFT JOIN departments ON departments.ticket_id = tickets.id'
        );
    }

    function it_allows_unique_joins()
    {
        $this->setFrom('tickets');

        $this->addJoin('people_emails', 'people_emails.person_id = {from}.person_id');

        $join_alias = $this->addUniqueJoin(
            'custom_def_people',
            '{alias}.person_id = {from}.person_id',
            DbalCompiledQuery::JOIN_INNER
        );

        if (strlen($join_alias->getWrappedObject()) <= 0) {
            throw new \Exception('no join alias returned');
        }

        $this->generateUniqueJoinString()->shouldReturn(
            'INNER JOIN custom_def_people custom_def_people_0 ON custom_def_people_0.person_id = {from}.person_id'
        );
    }

    function it_holds_query_parameters()
    {
        $this->setParameters(array('param' => 1, 'foo' => 'bar'));

        $this->getParameters()->shouldBeLike(array('param' => 1, 'foo' => 'bar'));

        $this->setParameter('new_param', 'new value');

        $this->getParameters()->shouldBeLike(array('param' => 1, 'foo' => 'bar', 'new_param' => 'new value'));
    }

    function it_can_accept_group_bys()
    {
        $this->setFrom('tickets', 't');
        $this->addGroupBy('{from}.subject');

        $this->getGroupBy()->shouldBeLike(array('{from}.subject'));
        $this->generateGroupByString()->shouldBe('{from}.subject');
        $this->__toString()->shouldBe('SELECT * FROM tickets t GROUP BY t.subject');

        $this->addGroupBy('{from}.date_created');

        $this->getGroupBy()->shouldBeLike(array('{from}.subject', '{from}.date_created'));
        $this->generateGroupByString()->shouldBe('{from}.subject, {from}.date_created');
        $this->__toString()->shouldBe('SELECT * FROM tickets t GROUP BY t.subject, t.date_created');
    }

    function it_can_accept_order_bys()
    {
        $this->setFrom('tickets', 't');
        $this->addOrderBy('{from}.subject', DbalCompiledQuery::ORDER_ASC);

        $this->getOrderBy()->shouldBeLike(
            array(
                array('{from}.subject', DbalCompiledQuery::ORDER_ASC)
            )
        );
        $this->generateOrderByString()->shouldBe('{from}.subject ASC');
        $this->__toString()->shouldBe('SELECT * FROM tickets t ORDER BY t.subject ASC');

        $this->addOrderBy('{from}.date_created', DbalCompiledQuery::ORDER_DESC);

        $this->getOrderBy()->shouldBeLike(
            array(
                array('{from}.subject', DbalCompiledQuery::ORDER_ASC),
                array('{from}.date_created', DbalCompiledQuery::ORDER_DESC)
            )
        );
        $this->generateOrderByString()->shouldBe('{from}.subject ASC, {from}.date_created DESC');
        $this->__toString()->shouldBe('SELECT * FROM tickets t ORDER BY t.subject ASC, t.date_created DESC');
    }

    function it_accepts_a_limit()
    {
        $this->getLimit()->shouldBe(null);

        $this->setLimit(10);

        $this->getLimit()->shouldBe(10);

        $this->setFrom('tickets', 't');

        $this->__toString()->shouldReturn('SELECT * FROM tickets t LIMIT 0, 10');
    }

    function it_accepts_a_page_that_works_with_limit()
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

    function it_starts_page_at_one()
    {
        $this->getPage()->shouldBe(1);

        $this->setFrom('tickets');
        $this->setLimit(10);

        $this->getPageOffset()->shouldBe(0);
        $this->generateLimitString()->shouldBe('0, 10');
        $this->__toString()->shouldBe('SELECT * FROM tickets LIMIT 0, 10');
    }

    function it_only_uses_page_if_limit_is_set()
    {
        $this->getLimit()->shouldBe(null);

        $this->setPage(2);
        $this->setFrom('tickets');
        $this->getPageOffset()->shouldBe(null);

        $this->generateLimitString()->shouldBe('');
        $this->__toString()->shouldBe('SELECT * FROM tickets');
    }

    function it_lets_you_add_parameters_and_returns_your_parameter_name()
    {
        $this->setFrom('tickets');

        $p1_name = $this->addParameter('name', 'value');
        $p2_name = $this->addParameter('name', 'other value');
        $p3_name = $this->addParameter('other_name', 'bar');

        // you get the param names from the addParameter call. your "name" is just a prefix but not the actual parameter name
        $this->setWherePart(
            't.id = :' . $p1_name->getWrappedObject() . ' OR t.subject = :' . $p2_name->getWrappedObject(
            ) . ' AND t.name = :' . $p3_name->getWrappedObject()
        );

        $this->__toString()->shouldBe(
            'SELECT * FROM tickets WHERE t.id = :name_0 OR t.subject = :name_1 AND t.name = :other_name_0'
        );
    }

    function it_does_everything_at_once()
    {
        $this->setSelectPart('{from}.id');
        $this->setFrom('tickets', 't');
        $this->setWherePart('{from}.id = 5');
        $this->addJoin('people_emails', 'people_emails.person_id = {from}.id');
        $this->addUniqueJoin('custom_def_person', '{alias}.person_id = {from}.id', DbalCompiledQuery::JOIN_INNER);
        $this->addGroupBy('{from}.date_created');
        $this->addGroupBy('{from}.id');
        $this->addOrderBy('{from}.id', DbalCompiledQuery::ORDER_DESC);
        $this->setLimit(15);

        $this->__toString()->shouldBeLike(
            'SELECT t.id FROM tickets t LEFT JOIN people_emails ON people_emails.person_id = t.id INNER JOIN custom_def_person custom_def_person_0 ON custom_def_person_0.person_id = t.id WHERE t.id = 5 GROUP BY t.date_created, t.id ORDER BY t.id DESC LIMIT 0, 15'
        );
    }
}
