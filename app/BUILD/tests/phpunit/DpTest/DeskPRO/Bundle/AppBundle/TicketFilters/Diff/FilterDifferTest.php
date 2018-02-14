<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DpTest\Bundle\AppBundle\TicketFilters\Diff;

use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\DiffEnv;
use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\FilterDiffer;
use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\FilterOp;
use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\TicketChange;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketBasicTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketMatcher;
use DeskPRO\Bundle\AppBundle\TicketFilters\ValueResolver;
use DeskPRO\Component\Util\MapUtils;

require_once __DIR__.'/FilterData.php';

class FilterDifferTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return DiffEnv
     */
    private function makeEnv()
    {
        $resolver = new ValueResolver();
        $matcher  = new TicketMatcher($resolver, [
            new TicketBasicTermsHandler(),
        ]);

        $agentContexts = FilterData::getAgents();
        $filters       = FilterData::getFilters();

        $env = new DiffEnv($matcher, $agentContexts, $filters);

        return $env;
    }

    public function test_simple_status_change()
    {
        $differ = new FilterDiffer($this->makeEnv());

        $ticketA             = new TicketModel();
        $ticketA->id         = 1;
        $ticketA->status     = 'awaiting_user';
        $ticketA->department = 1;

        $ticketB             = new TicketModel();
        $ticketB->id         = 1;
        $ticketB->status     = 'awaiting_agent';
        $ticketB->department = 1;

        $ops = $this->getPlainOpsArray(
            $differ->getFilterChangeOperations(new TicketChange($ticketA, $ticketB))
        );

        $this->assertEquals([
            4   => ['add' => [1, 2], 'del' => []],
            5   => ['add' => [1, 2], 'del' => []],
            100 => ['add' => [1, 2], 'del' => []],
        ], $ops);
    }

    public function test_noop()
    {
        $differ = new FilterDiffer($this->makeEnv());

        $ticketA             = new TicketModel();
        $ticketA->id         = 1;
        $ticketA->status     = 'awaiting_user';
        $ticketA->department = 1;

        $ticketB             = new TicketModel();
        $ticketB->id         = 1;
        $ticketB->status     = 'awaiting_user';
        $ticketB->department = 1;

        $ops = $this->getPlainOpsArray(
            $differ->getFilterChangeOperations(new TicketChange($ticketA, $ticketB))
        );

        $this->assertEquals([], $ops);
    }

    public function test_dep()
    {
        $differ = new FilterDiffer($this->makeEnv());

        $ticketA             = new TicketModel();
        $ticketA->id         = 1;
        $ticketA->status     = 'awaiting_agent';
        $ticketA->department = 1;

        $ticketB             = new TicketModel();
        $ticketB->id         = 1;
        $ticketB->status     = 'awaiting_agent';
        $ticketB->department = 2;

        $ops = $this->getPlainOpsArray(
            $differ->getFilterChangeOperations(new TicketChange($ticketA, $ticketB)),
            [1, 2, 3, 4, 5, 100]
        );

        // no ops because still visible in all filters
        $this->assertEquals([
            100 => ['add' => [], 'del' => [1, 2]],
        ], $ops);
    }

    public function test_dep_perm()
    {
        $differ = new FilterDiffer($this->makeEnv());

        $ticketA             = new TicketModel();
        $ticketA->id         = 1;
        $ticketA->status     = 'awaiting_agent';
        $ticketA->department = 1;

        $ticketB             = new TicketModel();
        $ticketB->id         = 1;
        $ticketB->status     = 'awaiting_agent';
        $ticketB->department = 3;

        $ops = $this->getPlainOpsArray(
            $differ->getFilterChangeOperations(new TicketChange($ticketA, $ticketB)),
            [100, 101]
        );

        $this->assertEquals([
            100 => ['add' => [], 'del' => [1, 2]],
            101 => ['add' => [1, 3], 'del' => []],
        ], $ops);
    }

    // same as above, but agent 3 is assigned so can see it in dep 1
    public function test_dep_perm_assign()
    {
        $differ = new FilterDiffer($this->makeEnv());

        $ticketA             = new TicketModel();
        $ticketA->id         = 1;
        $ticketA->agent      = 3;
        $ticketA->status     = 'awaiting_agent';
        $ticketA->department = 1;

        $ticketB             = new TicketModel();
        $ticketB->id         = 1;
        $ticketA->agent      = 3;
        $ticketB->status     = 'awaiting_agent';
        $ticketB->department = 3;

        $ops = $this->getPlainOpsArray(
            $differ->getFilterChangeOperations(new TicketChange($ticketA, $ticketB)),
            [100, 101]
        );

        $this->assertEquals([
            100 => ['add' => [], 'del' => [1, 2, 3]],
            101 => ['add' => [1, 3], 'del' => []],
        ], $ops);
    }

    /**
     * @param FilterOp[] $ops
     * @param int[]|null $filterIds Specific filter IDs we care about, or null for all
     */
    private function getPlainOpsArray(array $ops, $filterIds = null)
    {
        $plain = [];
        foreach ($ops as $op) {
            $plain[$op->getFilterId()] = [
                'add' => $op->getAddAgentIds(),
                'del' => $op->getDelAgentIds(),
            ];
            sort($plain[$op->getFilterId()]['add'], \SORT_NUMERIC);
            sort($plain[$op->getFilterId()]['del'], \SORT_NUMERIC);
        }

        if ($filterIds) {
            return MapUtils::filter($plain, function ($k) use ($filterIds) {
                return in_array($k, $filterIds);
            });
        }

        return $plain;
    }
}
