<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
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

namespace DpBehat\TermEngine;


use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use DeskPRO\Bundle\AppBundle\TermEngine\CompositeTermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpBehat\BaseContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalCompiledQuery;

class DbalCompilerContext extends BaseContext
{
    /**
     * @var TermInterface
     */
    protected $term;

    /**
     * @var CompositeTermInterface[]
     */
    protected $composites = array();

    /**
     * @var int
     */
    protected $composite_scope = 0;

    /**
     * @var DbalCompiledQuery
     */
    protected $compiled;

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
     * @When I compile my terms
     */
    public function iCompileMyTerms()
    {
        $this->compiled = $this->get('term_engine.dbal_ticket_filters.engine')->compile($this->term);
    }

    /**
     * @Then I should have a DbalCompiledResult
     */
    public function iShouldHaveADbalcompiledresult()
    {
        expect($this->compiled)->toBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalCompiledQuery');
    }

    protected $param_name_mapping = array();

    /**
     * @Then the WHERE clause should be like:
     */
    public function theWhereShouldBeLike(PyStringNode $string)
    {
        $where = trim($this->compiled->generateWhereString());
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
        $resolved_params = array();
        foreach ($expected_params as $param_name => $value) {
            $p = $this->param_name_mapping[':' . $param_name];
            $resolved_params[substr($p, 1)] = $value;
        }

        expect($this->compiled->getParameters())->toBeLike($resolved_params);
    }

    /**
     * @Given I enter a(n) :op composite term
     */
    public function iEnterACompositeTerm($op)
    {
        $this->composite_scope++;
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
        $this->composite_scope--;
    }

    /**
     * @Then the join string should be like:
     */
    public function theTableJoinsShouldBeLike(PyStringNode $string)
    {
        $expected = trim($string->getRaw());
        $real = $this->compiled->generateJoinString();

        list($expected, $real) = $this->dealWithParamAssertions($expected, $real);

        expect($real)->toBeLike($expected);
    }

    /**
     * @Then the unique join string should be like:
     */
    public function theUniqueTableJoinsShouldBeLike(PyStringNode $string)
    {
        $expected = trim($string->getRaw());
        $real = $this->compiled->generateUniqueJoinString();

        list($expected, $real) = $this->dealWithParamAssertions($expected, $real);

        expect($real)->toBeLike($expected);
    }

    /**
     * @param TableNode $table
     * @return array
     */
    private function filterTable(TableNode $table)
    {
        // this is pretty terrible, but it did the trick. if problems, lets refactor it.

        $output = array();
        $rows = $table->getRows();
        array_shift($rows);
        foreach ($rows as $row) {
            $v = trim($row[1]);

            // array syntax
            $len = strlen($v);
            if (preg_match('/^\\[(.*?)\\]/', $v, $matches)) {
                $array = $matches[1];

                $v = explode(',', $array);
                $nv = array();
                foreach ($v as $vv) {
                    $b = trim($vv);
                    if (is_numeric($b)) {
                        $b = (int)$b;
                    }
                    $nv[] = $b;
                }
            } else {
                // no array
                if (is_numeric($v)) {
                    $v = (int)$v;
                }
                $nv = $v;
            }


            $output[trim($row[0])] = $nv;
        }
        return $output;
    }

    /**
     * @param $op
     * @return mixed
     */
    private function getOp($op)
    {
        return constant('DeskPRO\Bundle\AppBundle\TermEngine\TermInterface::OP_' . $op);
    }

    /**
     * @param $expected
     * @param $where
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

        $where = preg_replace($param_regex, '', $where);
        $expected = preg_replace($param_regex, '', $expected);

        return array($where, $expected);
    }
}
