<?php

namespace DpTest\Bundle\AppBundle\TicketFilters\Diff;

use DeskPRO\Bundle\AppBundle\TicketFilters\CustomFieldSet;
use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\DiffEnv;
use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\FilterDiffer;
use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\FilterOp;
use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\TicketChange;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\CustomData;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\CustomField;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\CustomFieldsTermsHandler;
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
        $customTicketFields = [
            new CustomField(1, 'choice', ['my_choice']),
            new CustomField(2, 'choice', ['my_other_choice']),
            new CustomField(3, 'text', ['my_text']),
            new CustomField(4, 'date', ['my_date']),
            new CustomField(5, 'toggle', ['my_toggle']),
        ];

        $customFieldSet = new CustomFieldSet($customTicketFields);

        $resolver = new ValueResolver();
        $matcher  = new TicketMatcher($resolver, [
            new TicketBasicTermsHandler(),
            new CustomFieldsTermsHandler($customFieldSet),
        ]);

        $agents  = FilterData::getAgents();
        $filters = FilterData::getFilters();

        $env = new DiffEnv($matcher, $agents, $filters, $customFieldSet);

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

    public function test_simple_status_change_del()
    {
        $differ = new FilterDiffer($this->makeEnv());

        $ticketA             = new TicketModel();
        $ticketA->id         = 1;
        $ticketA->status     = 'awaiting_agent';
        $ticketA->department = 1;

        $ticketB             = new TicketModel();
        $ticketB->id         = 1;
        $ticketB->status     = 'awaiting_user';
        $ticketB->department = 1;

        $ops = $this->getPlainOpsArray(
            $differ->getFilterChangeOperations(new TicketChange($ticketA, $ticketB))
        );

        $this->assertEquals([
            4   => ['add' => [], 'del' => [1, 2]],
            5   => ['add' => [], 'del' => [1, 2]],
            100 => ['add' => [], 'del' => [1, 2]],
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
        $ticketB->agent      = 0;
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

    public function test_custom_change()
    {
        $differ = new FilterDiffer($this->makeEnv());

        $ticketA                = new TicketModel();
        $ticketA->id            = 1;
        $ticketA->status        = 'awaiting_agent';
        $ticketA->department    = 1;
        $ticketA->custom_fields = [
            new CustomData(1, [10]),
            new CustomData(2, [20]),
            new CustomData(3, 'foo'),
            new CustomData(4, strtotime('2018-04-09 01:00:00')),
            new CustomData(5, 1),
        ];

        $ticketB                = new TicketModel();
        $ticketB->id            = 1;
        $ticketB->status        = 'awaiting_agent';
        $ticketB->department    = 1;
        $ticketB->custom_fields = [
            new CustomData(1, [10]),
            new CustomData(2, [21]),
            new CustomData(4, strtotime('2018-01-01 01:00:00')),
            new CustomData(5, 0),
        ];

        $ops = $this->getPlainOpsArray(
            $differ->getFilterChangeOperations(new TicketChange($ticketA, $ticketB)),
            [200, 201, 202, 203, 204]
        );

        $this->assertEquals([
            200 => ['add' => [], 'del' => [1]],
            201 => ['add' => [1], 'del' => []],
            203 => ['add' => [1], 'del' => []],
            204 => ['add' => [1], 'del' => []],
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
