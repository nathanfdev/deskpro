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

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryBuilder;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalEntityHelper;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalEntityHelper
 */
class DbalEntityHelperSpec extends ObjectBehavior
{
    function it_handles_the_simple_is_case(
        DbalQueryBuilder $query_writer
    )
    {
        $query_writer->addParameter('ids', array(1))->willReturn('ids_0');

        $this->write($query_writer, 'ticket.agent_id', TermInterface::OP_IS, array(1))
            ->shouldReturn('ticket.agent_id IN (:ids_0)');
    }

    function it_handles_the_simple_NOT_case(
        DbalQueryBuilder $query_writer
    )
    {
        $query_writer->addParameter('ids', array(1))->willReturn('ids_0');

        $this->write($query_writer, 'ticket.agent_id', TermInterface::OP_NOT, array(1))
            ->shouldReturn('ticket.agent_id NOT IN (:ids_0)');
    }

    function it_handles_the_is_null_case(
        DbalQueryBuilder $query_writer
    )
    {
        $this->write($query_writer, 'ticket.agent_id', TermInterface::OP_IS, array(0))
            ->shouldReturn('ticket.agent_id IS NULL');

        $this->write($query_writer, 'ticket.agent_id', TermInterface::OP_IS, array())
            ->shouldReturn('ticket.agent_id IS NULL');

        $this->write($query_writer, 'ticket.agent_id', TermInterface::OP_IS, array(null))
            ->shouldReturn('ticket.agent_id IS NULL');

        $this->write($query_writer, 'ticket.agent_id', TermInterface::OP_IS, array(null, null, null))
            ->shouldReturn('ticket.agent_id IS NULL');
    }

    function it_handles_the_NOT_null_case(
        DbalQueryBuilder $query_writer
    )
    {
        $this->write($query_writer, 'ticket.agent_id', TermInterface::OP_NOT, array(0))
            ->shouldReturn('ticket.agent_id IS NOT NULL');
        $this->write($query_writer, 'ticket.agent_id', TermInterface::OP_NOT, array())
            ->shouldReturn('ticket.agent_id IS NOT NULL');
        $this->write($query_writer, 'ticket.agent_id', TermInterface::OP_NOT, array(null, null))
            ->shouldReturn('ticket.agent_id IS NOT NULL');
    }

    function it_handles_the_full_is_case(
        DbalQueryBuilder $query_writer
    )
    {
        $query_writer->addParameter('ids', array(3))->willReturn('ids_0');

        $this->write($query_writer, 'ticket.agent_id', TermInterface::OP_IS, array(0, 3))
            ->shouldReturn('ticket.agent_id IN (:ids_0) OR ticket.agent_id IS NULL');

        $this->write($query_writer, 'ticket.agent_id', TermInterface::OP_IS, array(null, 3))
            ->shouldReturn('ticket.agent_id IN (:ids_0) OR ticket.agent_id IS NULL');
    }

    function it_handles_the_full_NOT_case(
        DbalQueryBuilder $query_writer
    )
    {
        $query_writer->addParameter('ids', array(3))->willReturn('ids_0');

        $this->write($query_writer, 'ticket.agent_id', TermInterface::OP_NOT, array(0, 3))
            ->shouldReturn('ticket.agent_id NOT IN (:ids_0) AND ticket.agent_id IS NOT NULL');

        $this->write($query_writer, 'ticket.agent_id', TermInterface::OP_NOT, array(null, 3))
            ->shouldReturn('ticket.agent_id NOT IN (:ids_0) AND ticket.agent_id IS NOT NULL');
    }
}
