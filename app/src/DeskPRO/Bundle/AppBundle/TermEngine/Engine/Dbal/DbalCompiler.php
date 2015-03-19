<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal;

use DeskPRO\Bundle\AppBundle\Helper\ArbitraryHasher;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use DeskPRO\Bundle\AppBundle\TermEngine\VisitorInterface;

class DbalCompiler
{
    /**
     * @var DbalCompilerFactory
     */
    private $compiler_factory;

    /**
     * @var VisitorInterface[]
     */
    private $visitors;

    /**
     * @var array
     */
    private $params;

    /**
     * @var array
     */
    private $joins;

    /**
     * @var array
     */
    private $join_ons;

    /**
     * @var array
     */
    private $join_types;

    /**
     * @var DbalCompiledQuery
     */
    private $query;

    public function __construct(
        DbalCompilerFactory $compiler_factory,
        array $visitors
    )
    {
        $this->compiler_factory = $compiler_factory;
        $this->visitors = $visitors;
        $this->resetCompilerState();
    }

    /**
     * @param TermInterface $term
     * @return mixed
     */
    public function compile(TermInterface $term)
    {
        // we maintain some state between compiles, clear them here just in case.
        $this->resetCompilerState();

        foreach ($this->visitors as $visitor) {
            $visitor->visit($term);
        }

        $this->query->setFrom('tickets', 'ticket');

        $compiled_terms = $this->getTermCompiler($term)->compile($term, $this);
        $this->query->setWherePart($compiled_terms);

        $query = $this->query;

        // clear the compiler state
        $this->resetCompilerState();

        return $query;
    }

    public function getTermCompiler(TermInterface $term)
    {
        return $this->compiler_factory->getCompiler($term);
    }

    /**
     * Set a parameter, but the $name_prefix is just a prefix. The actual parameter
     * name will be returned to you.
     *
     * @param $name_prefix
     * @param $value
     * @return string the parameter name
     */
    public function addParameter($name_prefix, $value)
    {
        return $this->query->addParameter($name_prefix, $value);
    }

    /**
     * This will do nothing if the table is already joined, else it will join the
     * table with the given ON
     *
     * @param $table_name
     * @param string $on
     */
    public function addJoin($table_name, $on)
    {
        $this->query->addJoin($table_name, $on);
    }

    /**
     * You can use {alias} in the $on param as a placeholder for the real join alias.
     *
     * It returns the real join alias so you can reference it.
     *
     * @param $table_name
     * @param string $on
     * @param string $type
     * @return string the join alias
     */
    public function addUniqueJoin($table_name, $on, $type = 'LEFT')
    {
        return $this->query->addUniqueJoin($table_name, $on, $type);
    }

    private function resetCompilerState()
    {
        $this->query = new DbalCompiledQuery();
        $this->params = array();
        $this->joins = array();
        $this->join_ons = array();
        $this->join_types = array();
    }
}
