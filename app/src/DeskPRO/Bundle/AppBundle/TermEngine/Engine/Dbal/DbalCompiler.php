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

    public function __construct(
        DbalCompilerFactory $compiler_factory,
        array $visitors
    )
    {
        $this->compiler_factory = $compiler_factory;
        $this->visitors = $visitors;
        $this->params = array();
    }

    /**
     * @param TermInterface $term
     * @return mixed
     */
    public function compile(TermInterface $term)
    {
        // we maintain some state between compiles, clear them here.
        $this->params = array();

        foreach ($this->visitors as $visitor) {
            $visitor->visit($term);
        }

        // TODO: we can easily add options on the method to manipulate this:
        $query_string = 'SELECT * FROM tickets ticket';

        $compiled = $this->getTermCompiler($term)->compile($term, $this);

        // TODO: we havent got to joins yet in the terms, but the compiler will maintain state
        // during this call to the compilers above, and we append to the sql string here

        $query_string .= ' WHERE ' . $compiled;

        return new DbalCompiledQuery($query_string, $this->params);
    }

    public function getTermCompiler(TermInterface $term)
    {
        return $this->compiler_factory->getCompiler($term);
    }

    public function setParameter($name, $value)
    {
        $this->params[$name] = $value;
    }
}
