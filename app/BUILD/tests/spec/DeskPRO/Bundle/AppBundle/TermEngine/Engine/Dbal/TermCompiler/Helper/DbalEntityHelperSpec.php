<?php

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
        $query_part = $this->buildQueryPart('ticket.agent_id', TermInterface::OP_IS, [1]);

        $query_part->getWhereString()->shouldBe(
            'ticket.agent_id IN (:ids)'
        );

        $query_part->getParameters()->shouldBe(
            [
                'ids' => [1],
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_simple_NOT_case()
    {
        $query_part = $this->buildQueryPart('ticket.agent_id', TermInterface::OP_NOT, [1]);

        $query_part->getWhereString()->shouldBe(
            'ticket.agent_id NOT IN (:ids)'
        );

        $query_part->getParameters()->shouldBe(
            [
                'ids' => [1],
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_IS_NULL_case()
    {
        $first_query_part_result = $this->buildQueryPart('ticket.agent_id', TermInterface::OP_IS, [0]);

        $first_query_part_result->getWhereString()->shouldBe(
            'ticket.agent_id IS NULL'
        );
        $first_query_part_result->getParameters()->shouldBe([]);
        $first_query_part_result->getJoins()->shouldBe([]);
        $first_query_part_result->getUniqueJoins()->shouldBe([]);

        $this->buildQueryPart('ticket.agent_id', TermInterface::OP_IS, [])
            ->shouldBeLike($first_query_part_result);

        $this->buildQueryPart('ticket.agent_id', TermInterface::OP_IS, [null])
            ->shouldBeLike($first_query_part_result);

        $this->buildQueryPart('ticket.agent_id', TermInterface::OP_IS, [null, null, null])
            ->shouldBeLike($first_query_part_result);
    }

    public function it_handles_the_NOT_NULL_case()
    {
        $first_query_part_result = $this->buildQueryPart('ticket.agent_id', TermInterface::OP_NOT, [0]);

        $first_query_part_result->getWhereString()->shouldBe(
            'ticket.agent_id IS NOT NULL'
        );
        $first_query_part_result->getParameters()->shouldBe([]);
        $first_query_part_result->getJoins()->shouldBe([]);
        $first_query_part_result->getUniqueJoins()->shouldBe([]);

        $this->buildQueryPart('ticket.agent_id', TermInterface::OP_NOT, [])
            ->shouldBeLike($first_query_part_result);

        $this->buildQueryPart('ticket.agent_id', TermInterface::OP_NOT, [null])
            ->shouldBeLike($first_query_part_result);

        $this->buildQueryPart('ticket.agent_id', TermInterface::OP_NOT, [null, null, null])
            ->shouldBeLike($first_query_part_result);
    }

    public function it_handles_the_FULL_IS_case()
    {
        $query_part = $this->buildQueryPart('ticket.agent_id', TermInterface::OP_IS, [0, 3]);

        $query_part->getWhereString()->shouldBe('ticket.agent_id IN (:ids) OR ticket.agent_id IS NULL');
        $query_part->getParameters()->shouldBe(
            [
                'ids' => [3],
            ]
        );
    }

    public function it_handles_the_FULL_NOT_case()
    {
        $query_part = $this->buildQueryPart('ticket.agent_id', TermInterface::OP_NOT, [0, 3]);

        $query_part->getWhereString()->shouldBe('ticket.agent_id NOT IN (:ids) AND ticket.agent_id IS NOT NULL');
        $query_part->getParameters()->shouldBe(
            [
                'ids' => [3],
            ]
        );
    }
}
