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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker;

use DeskPRO\Bundle\AppBundle\Entity\Filter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Dumper\PhpFile;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\Compiler\PhpTicketCheckerCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\PhpTicketCheckerInterface;

class PhpTicketCheckerEngine
{
    /**
     * @var PhpTicketCheckerCompiler
     */
    private $compiler;

    /**
     * @var TermEngineExpressionLanguage
     */
    private $expression_language;

    public function __construct(
        PhpTicketCheckerCompiler $compiler,
        TermEngineExpressionLanguage $expression_language
    )
    {
        $this->compiler = $compiler;
        $this->expression_language = $expression_language;
    }

    /**
     * Takes a filter and the context and returns to you an instance of
     * PhpTicketCheckerInterface that satisfies the filters terms.
     *
     * @param Filter $filter
     * @param TermEngineContext $context
     * @return PhpTicketCheckerInterface
     */
    public function evaluate(Filter $filter, TermEngineContext $context)
    {
        $php_class = $this->compiler->compile($filter->getTerm());
        $php_file = new PhpFile();
        $php_file->setClass($php_class);

        // TODO: this is one method, we also will offer ability to "require"
        // a file from disk. In any case, we must ensure this is safely done.
        eval((string)$php_class);

        $class = $php_class->getName();

        return new $class($context, $this->expression_language);
    }
}
