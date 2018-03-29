<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalNumericHelper
 */
class DbalNumericHelperSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger)
    {
        $this->beConstructedWith($logger);
    }

    public function it_is_a_dbal_helper()
    {
        $this->shouldImplement('DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface');
        $this->getId()->shouldBe('numeric');
    }

    public function it_handles_the_simple_IS_case()
    {
        $num        = [1, 2, 3];
        $query_part = $this->buildQueryPart('ticket.id', TermInterface::OP_IS, $num);

        $query_part->getWhereString()->shouldBe('ticket.id IN (:num)');

        $query_part->getParameters()->shouldBe(
            [
                'num' => $num,
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_simple_NOT_case()
    {
        $num        = [1, 2, 3];
        $query_part = $this->buildQueryPart('ticket.id', TermInterface::OP_NOT, $num);

        $query_part->getWhereString()->shouldBe('ticket.id NOT IN (:num)');

        $query_part->getParameters()->shouldBe(
            [
                'num' => $num,
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_simple_GT_case()
    {
        $num        = [1];
        $query_part = $this->buildQueryPart('ticket.id', TermInterface::OP_GT, $num);

        $query_part->getWhereString()->shouldBe('ticket.id > :num');

        $query_part->getParameters()->shouldBe(
            [
                'num' => reset($num),
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_simple_GTE_case()
    {
        $num        = [1];
        $query_part = $this->buildQueryPart('ticket.id', TermInterface::OP_GTE, $num);

        $query_part->getWhereString()->shouldBe('ticket.id >= :num');

        $query_part->getParameters()->shouldBe(
            [
                'num' => reset($num),
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_simple_LT_case()
    {
        $num        = [1];
        $query_part = $this->buildQueryPart('ticket.id', TermInterface::OP_LT, $num);

        $query_part->getWhereString()->shouldBe('ticket.id < :num');

        $query_part->getParameters()->shouldBe(
            [
                'num' => reset($num),
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_simple_LTE_case()
    {
        $num        = [1];
        $query_part = $this->buildQueryPart('ticket.id', TermInterface::OP_LTE, $num);

        $query_part->getWhereString()->shouldBe('ticket.id <= :num');

        $query_part->getParameters()->shouldBe(
            [
                'num' => reset($num),
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_multiple_RANGE_case()
    {
        $num        = [1, 2, 3];
        $num2       = 3;
        $query_part = $this->buildQueryPart('ticket.id', TermInterface::OP_RANGE, $num, $num2);

        $query_part->getWhereString()->shouldBe('ticket.id BETWEEN :num AND :num2');

        $query_part->getParameters()->shouldBe(
            [
                'num'  => reset($num),
                'num2' => $num2,
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_multiple_NOT_RANGE_case()
    {
        $num        = [1, 2, 3];
        $num2       = 3;
        $query_part = $this->buildQueryPart('ticket.id', TermInterface::OP_NOT_RANGE, $num, $num2);

        $query_part->getWhereString()->shouldBe('ticket.id NOT BETWEEN :num AND :num2');

        $query_part->getParameters()->shouldBe(
            [
                'num'  => reset($num),
                'num2' => $num2,
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_multiple_GT_case()
    {
        $num        = [1, 2, 3];
        $query_part = $this->buildQueryPart('ticket.id', TermInterface::OP_GT, $num);

        $query_part->getWhereString()->shouldBe('ticket.id > :num');

        $query_part->getParameters()->shouldBe(
            [
                'num' => max($num),
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_multiple_GTE_case()
    {
        $num        = [1, 2, 3];
        $query_part = $this->buildQueryPart('ticket.id', TermInterface::OP_GTE, $num);

        $query_part->getWhereString()->shouldBe('ticket.id >= :num');

        $query_part->getParameters()->shouldBe(
            [
                'num' => max($num),
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_multiple_LT_case()
    {
        $num        = [2, 1, 3];
        $query_part = $this->buildQueryPart('ticket.id', TermInterface::OP_LT, $num);

        $query_part->getWhereString()->shouldBe('ticket.id < :num');

        $query_part->getParameters()->shouldBe(
            [
                'num' => min($num),
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_multiple_LTE_case()
    {
        $num        = [2, 1, 3];
        $query_part = $this->buildQueryPart('ticket.id', TermInterface::OP_LTE, $num);

        $query_part->getWhereString()->shouldBe('ticket.id <= :num');

        $query_part->getParameters()->shouldBe(
            [
                'num' => min($num),
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_one_num_RANGE_case()
    {
        $num        = [1];
        $query_part = $this->buildQueryPart('ticket.id', TermInterface::OP_RANGE, $num);

        $query_part->getWhereString()->shouldBe('ticket.id BETWEEN :num AND :num2');

        $query_part->getParameters()->shouldBe(
            [
                'num'  => reset($num),
                'num2' => reset($num),
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_one_num_NOT_RANGE_case()
    {
        $num        = [1];
        $query_part = $this->buildQueryPart('ticket.id', TermInterface::OP_NOT_RANGE, $num);

        $query_part->getWhereString()->shouldBe('ticket.id NOT BETWEEN :num AND :num2');

        $query_part->getParameters()->shouldBe(
            [
                'num'  => reset($num),
                'num2' => reset($num),
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }
}
