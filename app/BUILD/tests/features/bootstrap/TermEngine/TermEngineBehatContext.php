<?php

/**
 * DeskPRO.
 */

namespace DpBehat\TermEngine;

use Application\DeskPRO\Entity\Ticket;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use DeskPRO\Bundle\AppBundle\TermEngine\CompositeTermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\DbalTicketFilterEngine;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\PhpTicketCheckerInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpBehat\BaseContext;

class TermEngineBehatContext extends BaseContext
{
    /**
     * @var TermInterface
     */
    protected $term;

    /**
     * @var CompositeTermInterface[]
     */
    protected $composites = [];

    /**
     * @var int
     */
    protected $composite_scope = 0;

    /**
     * @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery
     */
    protected $compiled;

    /**
     * @var TermEngineContext
     */
    protected $engine_context;

    /**
     * @var DbalExecutableQuery|PhpTicketCheckerInterface
     */
    protected $engine_evaluation;

    /**
     * @var mixed
     */
    protected $engine_result;

    /**
     * @var DbalTicketFilterEngine
     */
    protected $engine;

    /**
     * @Given I set the context agent to :who
     */
    public function iSetTheContextAgentTo($who)
    {
        $ud = $this->get('user_details');

        $agent = $ud->getWho($who);

        $this->engine_context = new TermEngineContext($agent);
    }

    /**
     * @Given I am using the :engine
     */
    public function iAmUsingTheEngine($engine)
    {
        if ('DbalTicketFilterEngine' === $engine) {
            $this->engine = $this->get('term_engine.dbal_ticket_filters.engine');

            return;
        }
        if ('PhpTicketCheckerEngine' === $engine) {
            $this->engine = $this->get('term_engine.php_ticket_checker.engine');

            return;
        }

        throw new \InvalidArgumentException('engine "'.$engine.'" does not exist');
    }

    /**
     * @When I check tickets with the following ids:
     */
    public function iCheckTicketsWithTheFollowingIds(TableNode $table)
    {
        $expected_ids = [];

        foreach ($table->getRows() as $vals) {
            $expected_ids[] = current($vals);
        }

        $ticket_repo = $this->repository('DeskPRO:Ticket');

        $this->engine_result = [];
        foreach ($expected_ids as $id) {
            $ticket                = $ticket_repo->find($id);
            $this->engine_result[] = $this->engine_evaluation->isTicketMatch($ticket);
        }
    }

    /**
     * @Then all of the checks should match
     */
    public function theAllOfTheChecksShouldMatch()
    {
        foreach ($this->engine_result as $bool) {
            expect($bool)->toBe(true);
        }
    }

    /**
     * @Then none of the checks should match
     */
    public function theNoneOfTheChecksShouldMatch()
    {
        foreach ($this->engine_result as $bool) {
            expect($bool)->toBe(false);
        }
    }

    /**
     * @When I evaluate the filter :filter_name
     */
    public function iEvaluateTheFilter($filter_name)
    {
        $filter = $this->repository('DeskPRO\Bundle\AppBundle\Entity\TicketFilter')->findOneBy(
            ['title' => $filter_name]
        );

        $this->engine_evaluation = $this->engine
            ->evaluate($filter, $this->engine_context);
    }

    /**
     * @Then I fetch the ids from the executable query
     */
    public function iExecForIds()
    {
        $this->engine_result = $this->engine_evaluation->fetchIds();
    }

    /**
     * @Then print the last run query
     */
    public function iPrintLastRunQuery()
    {
        echo $this->engine_evaluation->getLastRunSql();
    }

    /**
     * @When I run a count on the executable query
     */
    public function iRunACountOnTheExecutableQuery()
    {
        $this->engine_result = $this->engine_evaluation->fetchCount();
    }

    /**
     * @Given I set the executable query page to :val
     */
    public function iSetTheQueryOptionpageTo($val)
    {
        $this->engine_evaluation->setPage($val);
    }

    /**
     * @Given I set the executable query count to :val
     */
    public function iSetTheQueryOptionCountTo($val)
    {
        $this->engine_evaluation->setCount($val);
    }

    /**
     * @When I add the count group :group to the executable query
     */
    public function iAddTheCountGroupToTheExecutableQuery($group)
    {
        $this->engine_evaluation->addCountGroup($group);
    }

    /**
     * @When I append :field :dir to the executable query order
     */
    public function iAppendIdDescToTheExecutableQueryOptionOrderby($field, $dir)
    {
        $this->engine_evaluation->addOrderBy($field, $dir);
    }

    /**
     * @Given I append :group_name = :val to the executable query andGroupWhere
     */
    public function iAppendDepartmentToTheQueryOptionGroupWhere($group_name, $val)
    {
        $this->engine_evaluation->addAndGroupWhere($group_name, $val);
    }

    /**
     * @Then I should be given the count :num
     */
    public function ishouldSeeCount($num)
    {
        expect($this->engine_result)->toBe($num);
    }

    /**
     * @Then I should be given the count of all tickets in the db with :status
     */
    public function iShouldBeGivenTheCountOfAllTicketsInTheDb($status)
    {
        $status = constant(sprintf('Application\DeskPRO\Entity\Ticket::%s', $status));
        $count  = count($this->repository('DeskPRO:Ticket')->findBy(['status' => $status]));
        expect($this->engine_result)->toBe($count);
    }

    /**
     * @Then I should be given the following dbal rows:
     */
    public function iShouldBeGivenTheFollowingDbalRows(TableNode $table)
    {
        $rows = $table->getColumnsHash();

        foreach ($rows as $inc => $expected_data) {
            $expected = [];
            foreach ($expected_data as $key => $val) {
                if ($val == 'null') {
                    $val = null;
                }
                $expected[$key] = $val;
            }

            expect($this->engine_result[$inc])->toBeLike($expected);
        }
    }

    /**
     * @Then I should have an array with the following ids:
     */
    public function iShouldHaveAnArrayWithTheFollowingIds(TableNode $table)
    {
        $expected_ids = [];

        foreach ($table->getRows() as $vals) {
            $expected_ids[] = current($vals);
        }

        expect($this->engine_result)->toBeLike($expected_ids);
    }

    /**
     * @Given I have a(n) :op :term with the options:
     */
    public function iHaveAnTermWithTheOptions($term, $op, TableNode $table)
    {
        $term_class = sprintf('DeskPRO\Bundle\AppBundle\TermEngine\Term\%s', $term);

        $options = $this->filterTable($table);

        $term = new $term_class($options);
        $term->setOp($this->getOp($op));

        if ($this->term) {
            $composite = $this->composites[$this->composite_scope];
            $composite->addTerm($term);
        } else {
            $this->term = $term;
        }
    }

    /**
     * @When I fetch the grouped count from the executable query
     */
    public function iFetchTheGroupedCountFromTheExecutableQuery()
    {
        $this->engine_result = $this->engine_evaluation->fetchGroupedCount();
    }

    /**
     * @When I compile my terms
     */
    public function iCompileMyTerms()
    {
        $this->compiled = $this->get('term_engine.dbal_ticket_filters.compiler')->compile($this->term);
    }

    /**
     * @Then I should have a DbalCompiledResult
     */
    public function iShouldHaveADbalcompiledresult()
    {
        expect($this->compiled)->toBeAnInstanceOf(
            'DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalCompiledQuery'
        );
    }

    protected $param_name_mapping = [];

    /**
     * @Then the WHERE clause should be like:
     */
    public function theWhereShouldBeLike(PyStringNode $string)
    {
        $where    = trim($this->compiled->generateWhereString());
        $expected = trim($string->getRaw());

        list($where, $expected) = $this->dealWithParamAssertions(
            $expected,
            $where
        );

        expect($where)->toBeLike($expected);
    }

    /**
     * @Then the parameters should be:
     */
    public function theParametersShouldBe(TableNode $table)
    {
        $expected_params = $this->filterTable($table);
        $resolved_params = [];
        foreach ($expected_params as $param_name => $value) {
            $p                              = $this->param_name_mapping[':'.$param_name];
            $resolved_params[substr($p, 1)] = $value;
        }

        $actual_params = $this->convertTermEngineExpressionToString(
            $this->compiled->getParameters()
        );

        expect($actual_params)->toBeLike($resolved_params);
    }

    protected function convertTermEngineExpressionToString(array $params)
    {
        $the_params = [];

        foreach ($params as $pname => $pval) {
            if (is_array($pval)) {
                $pval = $this->convertTermEngineExpressionToString($pval);
            } elseif ($pval instanceof \DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression) {
                $pval = sprintf('TermEngineExpression(\'%s\')', $pval);
            }

            $the_params[$pname] = $pval;
        }

        return $the_params;
    }

    /**
     * @Then there should be no parameters
     */
    public function thereShouldBeNoParams()
    {
        expect($this->compiled->getParameters())->toHaveCount(0);
    }

    /**
     * @Given I enter a(n) :op composite term
     */
    public function iEnterACompositeTerm($op)
    {
        ++$this->composite_scope;
        $scope = $this->composite_scope;

        $composite = new CompositeTerm();
        $composite->setOp($op);

        if (!$this->term) {
            $this->term = $composite;
        }

        $this->composites[$scope] = $composite;
    }

    /**
     * @Given I close the composite term
     */
    public function iCloseTheCompositeTerm()
    {
        --$this->composite_scope;
    }

    /**
     * @Then the join string should be like:
     */
    public function theTableJoinsShouldBeLike(PyStringNode $string)
    {
        $expected = trim($string->getRaw());
        $real     = $this->compiled->generateJoinString();

        list($expected, $real) = $this->dealWithParamAssertions($expected, $real);

        expect($real)->toBeLike($expected);
    }

    /**
     * @Then the unique join string should be like:
     */
    public function theUniqueTableJoinsShouldBeLike(PyStringNode $string)
    {
        $expected = trim($string->getRaw());
        $real     = $this->compiled->generateUniqueJoinString();

        list($expected, $real) = $this->dealWithParamAssertions($expected, $real);

        expect($real)->toBeLike($expected);
    }

    /**
     * @Given I re-fill ticket search table
     */
    public function iRefillTicketSearchTable()
    {
        $this->repository(Ticket::class)->fillSearchTable();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function filterTable(TableNode $table)
    {
        // this is pretty terrible, but it did the trick. if problems, lets refactor it.

        $output = [];
        $rows   = $table->getRows();
        array_shift($rows);
        foreach ($rows as $row) {
            $v = trim($row[1]);

            // array syntax
            $len = strlen($v);
            if (preg_match('/^\\[(.*?)\\]/', $v, $matches)) {
                $array = $matches[1];

                $v  = explode(',', $array);
                $nv = [];
                foreach ($v as $vv) {
                    $b = trim($vv);
                    if (is_numeric($b)) {
                        $b = (int) $b;
                    }
                    $nv[] = $b;
                }
            } else {
                // no array
                if (is_numeric($v)) {
                    $v = (int) $v;
                }
                if ($v == 'null') {
                    $v = null;
                }
                $nv = $v;
            }

            $output[trim($row[0])] = $nv;
        }

        return $output;
    }

    /**
     * @param $op
     *
     * @return mixed
     */
    private function getOp($op)
    {
        return constant('DeskPRO\Bundle\AppBundle\TermEngine\TermInterface::OP_'.$op);
    }

    /**
     * @param $expected
     * @param $where
     *
     * @return array
     */
    private function dealWithParamAssertions($expected, $where)
    {
        $param_regex = '/:([\w]+)/';
        preg_match_all($param_regex, $expected, $expected_where_param_names);
        $expected_where_param_names = $expected_where_param_names[0];
        preg_match_all($param_regex, $where, $real_where_param_names);
        $real_where_param_names = $real_where_param_names[0];

        if (count($expected_where_param_names) !== count($real_where_param_names)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'expected %s params, but the real query has %s params',
                    count($expected_where_param_names),
                    count($real_where_param_names)
                )
            );
        }

        // keep the mapping
        foreach ($expected_where_param_names as $i => $expected_param) {
            $this->param_name_mapping[$expected_param] = $real_where_param_names[$i];
        }

        $where    = preg_replace($param_regex, '', $where);
        $expected = preg_replace($param_regex, '', $expected);

        return [$where, $expected];
    }
}
