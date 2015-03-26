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

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalQueryManipulator;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalCompiledQuery;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery
 */
class DbalExecutableQuerySpec extends ObjectBehavior
{
    function let(
        DbalCompiledQuery $query,
        Connection $connection
    )
    {
        $this->beConstructedWith($query, $connection);
    }

    function it_will_execute_a_select_ids_query(
        DbalCompiledQuery $query,
        Connection $connection,
        Statement $stmt
    )
    {
        $query->setSelectPart('{from}.id')->shouldBeCalled();

        $query->__toString()->willReturn(
            'SELECT ticket.id FROM tickets ticket WHERE ticket.param = :param1'
        );
        $query->getParameters()->willReturn(
            array('param1' => 4)
        );

        $connection->executeQuery(
            'SELECT ticket.id FROM tickets ticket WHERE ticket.param = :param1',
            array('param1' => 4),
            Argument::type('array')
        )->willReturn($stmt);

        $stmt->fetchAll()->willReturn(
            $result =
                array(
                    array(
                        'id' => 2
                    ),
                    array(
                        'id' => 3
                    )
                )
        );

        $this->fetchIds()->shouldReturn($result);
    }

    function it_will_resolve_parameter_types_for_dbal_execute()
    {
        $input = array(
            'a' => 4,
            'b' => '5',
            'c' => 'string',
            'd' => array(
                'of',
                'stuff'
            ),
            'e' => array(
                1,
                2,
                3
            )
        );

        $this->determineParameterTypes($input)->shouldReturn(
            array(
                'a' => \PDO::PARAM_INT,
                'b' => \PDO::PARAM_STR,
                'c' => \PDO::PARAM_STR,
                'd' => Connection::PARAM_STR_ARRAY,
                'e' => Connection::PARAM_INT_ARRAY
            )
        );
    }
}
