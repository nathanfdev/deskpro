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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Compiler;

use DeskPRO\Bundle\AppBundle\Helper\ArbitraryHasher;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalCompiledQueryWriter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalCompiledQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\DbalTermCompilerFactory;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Compiler\DbalCompilerInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use DeskPRO\Bundle\AppBundle\TermEngine\VisitorInterface;

abstract class DbalCompiler implements DbalCompilerInterface
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\DbalTermCompilerFactory
     */
    protected $compiler_factory;

    /**
     * @var VisitorInterface[]
     */
    protected $visitors;

    public function __construct(
        DbalTermCompilerFactory $compiler_factory,
        array $visitors
    )
    {
        $this->compiler_factory = $compiler_factory;
        $this->visitors = $visitors;
    }

    /**
     * An opportunity for this engine implemention to alter the query before compile starts
     *
     * @param DbalCompiledQueryWriter $query_writer
     * @return void
     */
    abstract protected function enginePreCompile(DbalCompiledQueryWriter $query_writer);

    /**
     * An opportunity for this engine implemention to alter the query after compile is completed
     *
     * @param \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalCompiledQueryWriter $query_writer
     * @return void
     */
    abstract protected function enginePostCompile(DbalCompiledQueryWriter $query_writer);

    /**
     * @param TermInterface $term
     * @return DbalCompiledQuery
     */
    public function compile(TermInterface $term)
    {
        // let visitors alter the term
        foreach ($this->visitors as $visitor) {
            $visitor->visit($term);
        }

        $query_writer = new DbalCompiledQueryWriter(new DbalCompiledQuery());

        // engine pre hook
        $this->enginePreCompile($query_writer);

        // use term compilers to write the query and return the complete WHERE string
        $compiled_where = $this->getTermCompiler($term)->compile($term, $query_writer, $this);
        $query_writer->replaceWhere($compiled_where);

        // engine post hook
        $this->enginePostCompile($query_writer);

        // result is a DbalCompiledQuery
        return $query_writer->getQuery();
    }

    public function getTermCompiler(TermInterface $term)
    {
        return $this->compiler_factory->getCompiler($term);
    }
}
