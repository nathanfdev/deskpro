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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\TicketExpression;

use DeskPRO\Bundle\AppBundle\TermEngine\CompositeTermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TicketExpression\Compiler\CompositeTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TicketExpression\Compiler\AgentTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TicketExpression\Compiler\DepartmentTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\DepartmentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class TicketExpressionEngine
{
    /**
     * @var TicketExpressionCompilerInterface[]
     */
    protected $compilers;

    public function __construct($compilers)
    {
        $this->compilers = $compilers;
    }

    public function compile(TermInterface $term)
    {
        $compiled = "
    <?php

    use Application\\DeskPRO\\Entity\\Ticket;

    class Checker_dlaj4
    {
        protected \$context;

        public function __construct(\$context) {
            \$this->context = \$context;
        }

        public function isCheck(Ticket \$ticket) {

            return
        ";

        /** @var TicketExpressionCompilerInterface $compiler */
        $compiler = $this->findCompiler($term);
        $compiled .= $compiler->compile($term, $this);

        $compiled .=
            ";

    }
}
        ";

        return $compiled;
    }

    public function findCompiler(TermInterface $term)
    {
        foreach ($this->compilers as $compiler) {
            if ($term instanceof AgentTerm && $compiler instanceof AgentTermCompiler) {
                return $compiler;
            }
            if ($term instanceof DepartmentTerm && $compiler instanceof DepartmentTermCompiler) {
                return $compiler;
            }
            if ($term instanceof CompositeTerm && $compiler instanceof CompositeTermCompiler) {
                return $compiler;
            }
        }
    }
}
