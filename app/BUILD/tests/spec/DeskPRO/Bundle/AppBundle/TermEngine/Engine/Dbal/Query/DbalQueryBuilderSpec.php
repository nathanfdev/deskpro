<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryBuilder
 */
class DbalQueryBuilderSpec extends ObjectBehavior
{
    public function let(
        DbalQuery $query
    ) {
        $this->beConstructedWith($query);
    }

    public function it_is_a_wrapper_around_a_dbal_compiled_query(
        DbalQuery $query
    ) {
        $this->getQuery()->shouldReturn($query);
    }

    public function it_writes_where(
        DbalQuery $query
    ) {
        $query->setWherePart('ticket.id = 3')->shouldBeCalled();

        $this->setWhereString('ticket.id = 3');
    }

    public function it_writes_parameters(
        DbalQuery $query
    ) {
        $query->addParameter('name_prefix', 'value')->shouldBeCalled();

        $this->addParameter('name_prefix', 'value');
    }

    public function it_writes_joins(
        DbalQuery $query
    ) {
        $query->addJoin('table', 'alias')->shouldBeCalled();

        $this->addJoin('table', 'alias');
    }

    public function it_writes_unique_joins(
        DbalQuery $query
    ) {
        $query->addUniqueJoin('table', 'on', 'type')->shouldBeCalled();

        $this->addUniqueJoin('table', 'on', 'type');
    }

    public function it_lets_you_write_from(
        DbalQuery $query
    ) {
        $query->setFrom('table', 'alias')->shouldBeCalled();

        $this->setFrom('table', 'alias');
    }

    public function it_writes_a_dbal_query_part_where_string(
        DbalQuery $query,
        DbalQueryPart $query_part
    ) {
        $query_part->getUniqueJoins()->willReturn([]);
        $query_part->getJoins()->willReturn([]);
        $query_part->getParameters()->willReturn([]);
        $query_part->getWhereString()->willReturn('where.id = 5');

        $query->setWherePart('where.id = 5')->shouldBeCalled();

        $this->writeQueryPart($query_part);
    }

    public function it_writes_dbal_query_parameters(
        DbalQuery $query,
        DbalQueryPart $query_part
    ) {
        $query_part->getJoins()->willReturn([]);
        $query_part->getUniqueJoins()->willReturn([]);
        $query_part->getWhereString()->willReturn(null);
        $query_part->getParameters()->willReturn(
            [
                'custom' => 5,
                'me'     => $me = new TermEngineExpression('agent.getId()'),
            ]
        );

        $query->addParameter('custom', 5)->willReturn($custom_new_name = 'new_custom');
        $query->addParameter('me', $me)->willReturn($me_new_name = 'new_me');

        $query_part->renameParam('custom', $custom_new_name)->shouldBeCalled();
        $query_part->renameParam('me', $me_new_name)->shouldBeCalled();

        $this->writeQueryPart($query_part);
    }

    public function it_writes_dbal_joins(
        DbalQuery $query,
        DbalQueryPart $query_part
    ) {
        $query_part->getUniqueJoins()->willReturn([]);
        $query_part->getWhereString()->willReturn(null);
        $query_part->getParameters()->willReturn([]);
        $query_part->getJoins()->willReturn(
            [
                'ticket_participants' => [
                    'table' => 'ticket_participants',
                    'on'    => 'ticket_participants.id = ticket.participant',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );

        $query->addJoin('ticket_participants', 'ticket_participants.id = ticket.participant')->shouldBeCalled();

        $this->writeQueryPart($query_part);
    }

    public function it_writes_dbal_unqiue_joins(
        DbalQuery $query,
        DbalQueryPart $query_part
    ) {
        $query_part->getJoins()->willReturn([]);
        $query_part->getWhereString()->willReturn(null);
        $query_part->getParameters()->willReturn([]);
        $query_part->getUniqueJoins()->willReturn(
            [
                'participants' => [
                    'table' => 'ticket_participants',
                    'on'    => '{participants}.id = ticket.participant',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );

        $query->addUniqueJoin(
            'ticket_participants',
            '{alias}.id = ticket.participant',
            DbalQuery::JOIN_LEFT
        )->willReturn($new_join_alias = 'new_join');

        $query_part->renameJoinAlias('participants', $new_join_alias)->shouldBeCalled();

        $this->writeQueryPart($query_part);
    }

    public function it_deals_with_multiple_unique_joins_that_reference_each_other(
        DbalQuery $query,
        DbalQueryPart $query_part
    ) {
        $query_part->getJoins()->willReturn([]);
        $query_part->getWhereString()->willReturn(null);
        $query_part->getParameters()->willReturn([]);
        $query_part->getUniqueJoins()->willReturn(
            [
                'participants' => [
                    'table' => 'ticket_participants',
                    'on'    => '{participants}.id = ticket.participant',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
                'custom_data' => [
                    'table' => 'custom_ticket_data',
                    'on'    => '{participants}.id = {custom_data}.id AND {random}.col = {participants}.something',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
                'random' => [
                    'table' => 'random_table',
                    'on'    => '{random}.id = {participants}.participant',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );

        $query->addUniqueJoin(
            'ticket_participants',
            '{alias}.id = ticket.participant',
            DbalQuery::JOIN_LEFT
        )->willReturn('p1');

        $query_part->renameJoinAlias('participants', 'p1')->shouldBeCalled();

        $query->addUniqueJoin(
            'custom_ticket_data',
            'p1.id = {alias}.id AND {random}.col = p1.something',
            DbalQuery::JOIN_LEFT
        )->willReturn('c');

        $query_part->renameJoinAlias('custom_data', 'c')->shouldBeCalled();

        $query->addUniqueJoin(
            'random_table',
            '{alias}.id = p1.participant',
            DbalQuery::JOIN_LEFT
        )->willReturn('d');

        $query_part->renameJoinAlias('random', 'd')->shouldBeCalled();

        $this->writeQueryPart($query_part);
    }
}
