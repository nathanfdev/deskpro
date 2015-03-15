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
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class TicketExpressionEngine
{
    /**
     * @var TicketExpressionCompilerInterface[]
     */
    protected $compilers;

    /**
     * @var ExpressionLanguage
     */
    protected $language;

    public function __construct($compilers)
    {
        $this->compilers = $compilers;
        $this->language = new ExpressionLanguage(/** it has built in caching... */);
    }

    public function compile(TermInterface $term)
    {
        /** @var TicketExpressionCompilerInterface $compiler */
        $compiler = $this->findCompiler($term);
        $compiled = $compiler->compile($term, $this);

        return $this->language->compile(
            $compiled,
            array(
                /** compilers have access to certain variables that this engine provides */
                /** access to the ticket in question */
                'ticket',
                /** access to the authenticated agent */
                'me',
                /** access to context/voters for security assertions */
                'security',
                /** etc */
                /** can also create our own expression language functions */
                /** container itself, is possible */
            )
        );
    }

    public function findCompiler(TermInterface $term)
    {
        foreach ($this->compilers as $compiler) {
            if ($compiler->supportsTerm($term)) {
                return $compiler;
            }
        }
    }

    protected function createMyTerms(TermInterface $term)
    {
        $my_terms = null;

        if ($term instanceof CompositeTermInterface) {
            $my_terms = new CompositeTerm();
            foreach ($term->getTerms() as $term) {
                $my_terms->addTerm($this->createMyTerms($term));
            }
        } else {
            return $term;
        }

        return $my_terms;
    }
}
