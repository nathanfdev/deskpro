<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
