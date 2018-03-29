<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalStringHelper
 */
class DbalStringHelperSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger)
    {
        $this->beConstructedWith($logger);
    }

    public function it_is_a_dbal_helper()
    {
        $this->shouldImplement('DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface');
        $this->getId()->shouldBe('string');
    }

    public function it_handles_the_simple_IS_case()
    {
        $subject    = 'test subject';
        $query_part = $this->buildQueryPart('ticket.subject', TermInterface::OP_IS, [$subject]);

        $query_part->getWhereString()->shouldBe('ticket.subject = :string0');

        $query_part->getParameters()->shouldBe(
            [
                'string0' => $subject,
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_simple_NOT_case()
    {
        $subject    = 'test subject';
        $query_part = $this->buildQueryPart('ticket.subject', TermInterface::OP_NOT, [$subject]);

        $query_part->getWhereString()->shouldBe('ticket.subject != :string0');

        $query_part->getParameters()->shouldBe(
            [
                'string0' => $subject,
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_simple_HAS_case()
    {
        $subject    = 'test subject';
        $query_part = $this->buildQueryPart('ticket.subject', TermInterface::OP_HAS, [$subject]);

        $query_part->getWhereString()->shouldBe('ticket.subject LIKE :string0');

        $query_part->getParameters()->shouldBe(
            [
                'string0' => '%'.$subject.'%',
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_the_simple_NOT_HAS_case()
    {
        $subject    = 'test subject';
        $query_part = $this->buildQueryPart('ticket.subject', TermInterface::OP_NOT_HAS, [$subject]);

        $query_part->getWhereString()->shouldBe('ticket.subject NOT LIKE :string0');

        $query_part->getParameters()->shouldBe(
            [
                'string0' => '%'.$subject.'%',
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_wildcard_prefix()
    {
        $subject    = 'test subject';
        $query_part = $this->buildQueryPart('ticket.subject', TermInterface::OP_IS, [$subject], true);

        $query_part->getWhereString()->shouldBe('ticket.subject LIKE :string0');

        $query_part->getParameters()->shouldBe(
            [
                'string0' => '%'.$subject,
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_wildcard_postfix()
    {
        $subject    = 'test subject';
        $query_part = $this->buildQueryPart('ticket.subject', TermInterface::OP_IS, [$subject], false, true);

        $query_part->getWhereString()->shouldBe('ticket.subject LIKE :string0');

        $query_part->getParameters()->shouldBe(
            [
                'string0' => $subject.'%',
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_multiple_strings_with_IS_operator()
    {
        $subject1   = 'test subject';
        $subject2   = 'test subject2';
        $query_part = $this->buildQueryPart('ticket.subject', TermInterface::OP_IS, [$subject1, $subject2]);

        $query_part->getWhereString()->shouldBe('ticket.subject = :string0 OR ticket.subject = :string1');

        $query_part->getParameters()->shouldBe(
            [
                'string0' => $subject1,
                'string1' => $subject2,
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }

    public function it_handles_multiple_strings_with_NOT_operator()
    {
        $subject1   = 'test subject';
        $subject2   = 'test subject2';
        $query_part = $this->buildQueryPart('ticket.subject', TermInterface::OP_NOT, [$subject1, $subject2]);

        $query_part->getWhereString()->shouldBe('ticket.subject != :string0 AND ticket.subject != :string1');

        $query_part->getParameters()->shouldBe(
            [
                'string0' => $subject1,
                'string1' => $subject2,
            ]
        );

        $query_part->getJoins()->shouldBe([]);
        $query_part->getUniqueJoins()->shouldBe([]);
    }
}
