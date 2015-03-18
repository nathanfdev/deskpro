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
     * @var ArbitraryHasher
     */
    private $hasher;

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
        // we maintain some state between compiles, clear them here.
        $this->resetCompilerState();

        foreach ($this->visitors as $visitor) {
            $visitor->visit($term);
        }

        $query = new DbalCompiledQuery();

        $query->setSelect('id');
        $query->setFromTable('tickets');
        $query->setFromAlias('ticket');

        $compiled_terms = $this->getTermCompiler($term)->compile($term, $this);
        $query->setWhere($compiled_terms);

        $query->setJoins($this->joins, $this->join_ons); // MUST be set after the compiler
        $query->setParameters($this->params);

        return $query;
    }

    public function getTermCompiler(TermInterface $term)
    {
        return $this->compiler_factory->getCompiler($term);
    }

    public function setParameter($value)
    {
        // remove the microtime input on the hash if we should share param names for
        // the exact same param value. different now because we might want to edit them
        // all independently.
        $name = $this->hasher->generateHash(array(microtime(), $value));

        $this->params[$name] = $value;

        return $name;
    }

    /**
     * @param $table_name
     * @param $suggested_alias
     * @param string|null $on
     * @return string
     */
    public function addJoin($table_name, $suggested_alias, $on = null)
    {
        if ($existing_alias = $this->getJoinAliasForTable($table_name)) {
            $resolved_alias_name = $existing_alias;
        } elseif ($suggested_alias) {
            $resolved_alias_name = $suggested_alias;
        } else {
            $resolved_alias_name = $this->hasher->generateHash($table_name);
        }

        if ($on) {
            // ensure correct alias
            $on = str_replace($suggested_alias . '.', $resolved_alias_name . '.', $on);
        } else {
            $on = '';
        }

        $this->joins[$resolved_alias_name] = $table_name;
        $this->join_ons[$resolved_alias_name] = $on;

        return $resolved_alias_name;
    }

    private function resetCompilerState()
    {
        $this->params = array();
        $this->joins = array();
        $this->join_ons = array();
        $this->hasher = new ArbitraryHasher();
    }


    private function getJoinAliasForTable($table_name)
    {
        foreach ($this->joins as $alias => $table) {
            if ($table_name === $table) {
                return $alias;
            }
        }

        return null;
    }
}
