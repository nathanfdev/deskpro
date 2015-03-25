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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Compiler\DbalCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractDbalTermCompiler
{
    /**
     * Take a term and return its WHERE clause. Inside, you may also
     * interact with the DbalCompiler to add paramters, joins, etc.
     *
     * @param TermInterface $term
     * @param DbalCompiler $compiler
     * @return string
     */
    abstract public function doCompile(TermInterface $term, DbalCompiler $compiler);

    /**
     * Take a term and return its WHERE clause. Inside, you may also
     * interact with the DbalCompiler to add paramters, joins, etc.
     *
     * @param TermInterface $term
     * @param DbalCompiler $compiler
     * @return string
     */
    public function compile(TermInterface $term, DbalCompiler $compiler)
    {
        // I think there will be common things we do on each compiler, so this
        // is just a pre-emptive abstraction.
        return $this->doCompile($term, $compiler);
    }

    /**
     * Use this shortcut to see if two op codes are the same.
     *
     * This normalizes the codes and then does the comparrison in a safe way.
     *
     * @param string $op
     * @param string $code
     * @return bool
     */
    protected function isOp($op, $code)
    {
        return strtolower($op) === strtolower($code);
    }
}
