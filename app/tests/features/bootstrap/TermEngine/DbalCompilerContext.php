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
     * @Given I have a(n) :term (:op) with the options:
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
        $this->compiled = $this->get('term_engine.dbal')->compile($this->term);
    }

    /**
     * @Then I should have a DbalCompiledResult
     */
    public function iShouldHaveADbalcompiledresult()
    {
        expect($this->compiled)->toBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalCompiledQuery');
    }

    /**
     * @Then the SQL should be like:
     */
    public function theSqlShouldBeLike(PyStringNode $string)
    {
        expect(trim($this->compiled->getQueryString()))->toBeLike(trim($string->getRaw()));
    }

    /**
     * @Then the parameters should be:
     */
    public function theParametersShouldBe(TableNode $table)
    {
        $expected_params = $this->filterTable($table);

        expect($this->compiled->getParameters())->toBeLike($expected_params);
    }

    /**
     * @Given I enter a composite (:op) term
     */
    public function iEnterACompositeOrTerm($op)
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
     * @param TableNode $table
     * @return array
     */
    private function filterTable(TableNode $table)
    {
        $output = array();
        $rows = $table->getRows();
        array_shift($rows);
        foreach ($rows as $row) {
            $v = $row[1];

            $v = explode(',', $v);
            $nv = array();
            foreach ($v as $vv) {
                $nv[] = trim($vv);
            }


            $output[$row[0]] = $nv;
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
}
