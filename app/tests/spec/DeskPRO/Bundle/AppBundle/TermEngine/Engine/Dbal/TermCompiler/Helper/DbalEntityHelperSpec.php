<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalEntityHelper
 */
class DbalEntityHelperSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger)
    {
        $this->beConstructedWith($logger);
    }

    public function it_is_a_dbal_helper()
    {
        $this->shouldImplement('DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface');
        $this->getId()->shouldBe('entity');
    }

    public function it_handles_the_simple_IS_case()
    {
        $query_part = $this->buildQueryPart('ticket.agent_id', TermInterface::OP_IS, array(1));

        $query_part->getWhereString()->shouldBe(
            'ticket.agent_id IN (:ids)'
        );

        $query_part->getParameters()->shouldBe(
            array(
                'ids' => array(1),
            )
        );

        $query_part->getJoins()->shouldBe(array());
        $query_part->getUniqueJoins()->shouldBe(array());
    }

    public function it_handles_the_simple_NOT_case()
    {
        $query_part = $this->buildQueryPart('ticket.agent_id', TermInterface::OP_NOT, array(1));

        $query_part->getWhereString()->shouldBe(
            'ticket.agent_id NOT IN (:ids)'
        );

        $query_part->getParameters()->shouldBe(
            array(
                'ids' => array(1),
            )
        );

        $query_part->getJoins()->shouldBe(array());
        $query_part->getUniqueJoins()->shouldBe(array());
    }

    public function it_handles_the_IS_NULL_case()
    {
        $first_query_part_result = $this->buildQueryPart('ticket.agent_id', TermInterface::OP_IS, array(0));

        $first_query_part_result->getWhereString()->shouldBe(
            'ticket.agent_id IS NULL'
        );
        $first_query_part_result->getParameters()->shouldBe(array());
        $first_query_part_result->getJoins()->shouldBe(array());
        $first_query_part_result->getUniqueJoins()->shouldBe(array());

        $this->buildQueryPart('ticket.agent_id', TermInterface::OP_IS, array())
            ->shouldBeLike($first_query_part_result);

        $this->buildQueryPart('ticket.agent_id', TermInterface::OP_IS, array(null))
            ->shouldBeLike($first_query_part_result);

        $this->buildQueryPart('ticket.agent_id', TermInterface::OP_IS, array(null, null, null))
            ->shouldBeLike($first_query_part_result);
    }

    public function it_handles_the_NOT_NULL_case()
    {
        $first_query_part_result = $this->buildQueryPart('ticket.agent_id', TermInterface::OP_NOT, array(0));

        $first_query_part_result->getWhereString()->shouldBe(
            'ticket.agent_id IS NOT NULL'
        );
        $first_query_part_result->getParameters()->shouldBe(array());
        $first_query_part_result->getJoins()->shouldBe(array());
        $first_query_part_result->getUniqueJoins()->shouldBe(array());

        $this->buildQueryPart('ticket.agent_id', TermInterface::OP_NOT, array())
            ->shouldBeLike($first_query_part_result);

        $this->buildQueryPart('ticket.agent_id', TermInterface::OP_NOT, array(null))
            ->shouldBeLike($first_query_part_result);

        $this->buildQueryPart('ticket.agent_id', TermInterface::OP_NOT, array(null, null, null))
            ->shouldBeLike($first_query_part_result);
    }

    public function it_handles_the_FULL_IS_case()
    {
        $query_part = $this->buildQueryPart('ticket.agent_id', TermInterface::OP_IS, array(0, 3));

        $query_part->getWhereString()->shouldBe('ticket.agent_id IN (:ids) OR ticket.agent_id IS NULL');
        $query_part->getParameters()->shouldBe(
            array(
                'ids' => array(3),
            )
        );
    }

    public function it_handles_the_FULL_NOT_case()
    {
        $query_part = $this->buildQueryPart('ticket.agent_id', TermInterface::OP_NOT, array(0, 3));

        $query_part->getWhereString()->shouldBe('ticket.agent_id NOT IN (:ids) AND ticket.agent_id IS NOT NULL');
        $query_part->getParameters()->shouldBe(
            array(
                'ids' => array(3),
            )
        );
    }
}
