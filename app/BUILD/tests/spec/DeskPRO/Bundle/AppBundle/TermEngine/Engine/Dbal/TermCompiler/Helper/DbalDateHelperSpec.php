<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalDateHelper
 */
class DbalDateHelperSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger)
    {
        $this->beConstructedWith($logger);
    }

    public function it_is_a_dbal_helper()
    {
        $this->shouldImplement('DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface');
        $this->getId()->shouldBe('date');
    }

    public function it_handles_the_simple_cases()
    {
        $date  = new \DateTime('now', new \DateTimeZone('Europe/London'));
        $date2 = new \DateTime('+1 day', new \DateTimeZone('Europe/London'));

        $check1 = clone $date;
        $check1->setTimezone(new \DateTimeZone('UTC'));
        $check1 = $check1->format('Y-m-d H:i:s');
        $check2 = clone $date2;
        $check2->setTimezone(new \DateTimeZone('UTC'));
        $check2 = $check2->format('Y-m-d H:i:s');

        $ops = [
            TermInterface::OP_IS        => 'ticket.date_created = :date',
            TermInterface::OP_NOT       => 'ticket.date_created != :date',
            TermInterface::OP_GT        => 'ticket.date_created > :date',
            TermInterface::OP_LT        => 'ticket.date_created < :date',
            TermInterface::OP_GTE       => 'ticket.date_created >= :date',
            TermInterface::OP_LTE       => 'ticket.date_created <= :date',
            TermInterface::OP_RANGE     => 'ticket.date_created BETWEEN :date AND :date2',
            TermInterface::OP_NOT_RANGE => 'ticket.date_created NOT BETWEEN :date AND :date2',
        ];

        foreach ($ops as $op => $where) {
            if (TermInterface::OP_RANGE === $op || TermInterface::OP_NOT_RANGE === $op) {
                $query_part = $this->buildQueryPart('ticket.date_created', $op, $date, $date2);
                $query_part->getParameters()->shouldBe(
                    [
                        'date'  => $check1,
                        'date2' => $check2,
                    ]
                );
            } else {
                $query_part = $this->buildQueryPart('ticket.date_created', $op, $date);
                $query_part->getParameters()->shouldBe(
                    [
                        'date' => $check1,
                    ]
                );
            }
            $query_part->getWhereString()->shouldBe($where);
            $query_part->getJoins()->shouldBe([]);
            $query_part->getUniqueJoins()->shouldBe([]);
        }
    }

    public function it_converts_single_date_into_range()
    {
        $date = new \DateTime('now', new \DateTimeZone('Europe/London'));

        $check1 = new \DateTime('00:00:00', new \DateTimeZone('Europe/London'));
        $check1->setTimezone(new \DateTimeZone('UTC'));
        $check1 = $check1->format('Y-m-d H:i:s');
        $check2 = new \DateTime('23:59:59', new \DateTimeZone('Europe/London'));
        $check2->setTimezone(new \DateTimeZone('UTC'));
        $check2 = $check2->format('Y-m-d H:i:s');

        $ops = [
            TermInterface::OP_IS        => 'ticket.date_created BETWEEN :date AND :date2',
            TermInterface::OP_NOT       => 'ticket.date_created NOT BETWEEN :date AND :date2',
            TermInterface::OP_GT        => 'ticket.date_created > :date',
            TermInterface::OP_LT        => 'ticket.date_created < :date',
            TermInterface::OP_GTE       => 'ticket.date_created >= :date',
            TermInterface::OP_LTE       => 'ticket.date_created <= :date',
            TermInterface::OP_RANGE     => 'ticket.date_created BETWEEN :date AND :date2',
            TermInterface::OP_NOT_RANGE => 'ticket.date_created NOT BETWEEN :date AND :date2',
        ];

        foreach ($ops as $op => $where) {
            $query_part = $this->buildQueryPart('ticket.date_created', $op, $date, null, true);

            if (in_array($op, [TermInterface::OP_RANGE, TermInterface::OP_NOT_RANGE, TermInterface::OP_IS, TermInterface::OP_NOT])) {
                $query_part->getParameters()->shouldBe(
                    [
                        'date'  => $check1,
                        'date2' => $check2,
                    ]
                );
            } else {
                $query_part->getParameters()->shouldBe(
                    [
                        'date' => $check1,
                    ]
                );
            }

            $query_part->getWhereString()->shouldBe($where);
            $query_part->getJoins()->shouldBe([]);
            $query_part->getUniqueJoins()->shouldBe([]);
        }
    }
}
