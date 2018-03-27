<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart
 */
class DbalQueryPartSpec extends ObjectBehavior
{
    public function it_initizlizes_empty()
    {
        $this->getParameters()->shouldReturn([]);
        $this->getJoins()->shouldReturn([]);
        $this->getUniqueJoins()->shouldReturn([]);
        $this->getWhereString()->shouldReturn(null);
    }

    public function it_is_capable_of_holding_parameters()
    {
        $this->setParameter('ids', [1, 2, 3]);
        $this->setParameter('agent', new TermEngineExpression('agent.getId()'));
        $this->setParameter('scalar', 'hello');

        $this->getParameters()->shouldBeLike(
            [
                'ids'    => [1, 2, 3],
                'agent'  => new TermEngineExpression('agent.getId()'),
                'scalar' => 'hello',
            ]
        );
    }

    public function it_allows_adding_simple_joins()
    {
        $this->addJoin('table', 'on condition');
        $this->addJoin('other_table', 'foo = baz', DbalQuery::JOIN_INNER);

        $this->getJoins()->shouldBeLike(
            [
                'table' => [
                    'table' => 'table',
                    'on'    => 'on condition',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
                'other_table' => [
                    'table' => 'other_table',
                    'on'    => 'foo = baz',
                    'type'  => DbalQuery::JOIN_INNER,
                ],
            ]
        );
    }

    public function it_allows_adding_unique_joins()
    {
        $this->addUniqueJoin('alias_1', 'table', '{alias_1}.id = ticket.something', DbalQuery::JOIN_RIGHT);

        $this->addUniqueJoin('alias_2', 'table', '{alias_2}.id = ticket.something_else');

        $this->getUniqueJoins()->shouldBeLike(
            [
                'alias_1' => [
                    'table' => 'table',
                    'on'    => '{alias_1}.id = ticket.something',
                    'type'  => DbalQuery::JOIN_RIGHT,
                ],
                'alias_2' => [
                    'table' => 'table',
                    'on'    => '{alias_2}.id = ticket.something_else',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function it_does_not_allow_alias_to_be_the_alias()
    {
        // {alias} is a reserved alias, and you CAN NOT set it as the alias of a unique join
        $this->shouldThrow('\InvalidArgumentException')->during(
            'addUniqueJoin',
            ['alias', 'table', '{alias}.id = 4']
        );
    }

    public function it_allows_setting_the_where_condition()
    {
        $this->setWhereString('{alias_2}.agent = :agent');

        $this->getWhereString()->shouldBe('{alias_2}.agent = :agent');
    }

    public function it_can_rename_a_parameter_and_it_affects_all_parts()
    {
        $this->setParameter('param_name', 5);
        $this->setParameter('unchanged_param', 15);

        $this->setWhereString('something = :param_name AND :unchanged_param > 0');
        $this->addJoin('departments', ':unchanged_param > 4 OR table.something = :param_name');
        $this->addUniqueJoin('alias_2', 'people', '{alias_2}.id = :param_name AND :unchanged_param > 8');

        // no we'll rename it (this is used in the sql writer to make sure all unique)
        $this->renameParam('param_name', 'xyz_2');

        $this->getParameters()->shouldBeLike(
            [
                'xyz_2'           => 5,
                'unchanged_param' => 15,
            ]
        );

        $this->getWhereString()->shouldBe(
            'something = :xyz_2 AND :unchanged_param > 0'
        );

        $this->getJoins()->shouldBeLike(
            [
                'departments' => [
                    'table' => 'departments',
                    'on'    => ':unchanged_param > 4 OR table.something = :xyz_2',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );

        $this->getUniqueJoins()->shouldBeLike(
            [
                'alias_2' => [
                    'table' => 'people',
                    'on'    => '{alias_2}.id = :xyz_2 AND :unchanged_param > 8',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }

    public function it_can_rename_unique_join_aliases()
    {
        $this->setParameter('param_name', 5);
        $this->setParameter('unchanged_param', 15);

        $this->setWhereString('something = :param_name AND {alias_2}.bool = 1');
        $this->addJoin('departments', ':unchanged_param > 4 OR table.something = {alias_2}.name');
        $this->addUniqueJoin('alias_2', 'people', '{alias_2}.id = :param_name AND :unchanged_param > 8');

        $this->renameJoinAlias('alias_2', 'xyz_123');

        $this->getWhereString()->shouldBe(
            'something = :param_name AND {xyz_123}.bool = 1'
        );

        $this->getJoins()->shouldBeLike(
            [
                'departments' => [
                    'table' => 'departments',
                    'on'    => ':unchanged_param > 4 OR table.something = {xyz_123}.name',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );

        $this->getUniqueJoins()->shouldBeLike(
            [
                'xyz_123' => [
                    'table' => 'people',
                    'on'    => '{xyz_123}.id = :param_name AND :unchanged_param > 8',
                    'type'  => DbalQuery::JOIN_LEFT,
                ],
            ]
        );
    }
}
