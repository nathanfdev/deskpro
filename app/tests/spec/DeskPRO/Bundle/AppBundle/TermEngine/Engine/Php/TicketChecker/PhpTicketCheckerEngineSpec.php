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

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker;

use DeskPRO\Bundle\AppBundle\Entity\Filter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Dumper\PhpClass;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\Compiler\PhpTicketCheckerCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\PhpTicketCheckerEngine;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\PhpTicketCheckerEngine
 */
class PhpTicketCheckerEngineSpec extends ObjectBehavior
{
    function it_evals_the_compiler_result_and_returns_the_compiled_checker(
        PhpTicketCheckerCompiler $compiler,
        TermEngineExpressionLanguage $expression_language,
        Filter $filter,
        TermEngineContext $context,
        TermInterface $term,
        PhpClass $compiled_class
    )
    {
        $this->beConstructedWith($compiler, $expression_language);

        $filter->getTerm()->willReturn($term);
        $compiler->compile($term)->willReturn($compiled_class);
        $compiled_class->getName()->willReturn('myclass');
        $compiled_class->__toString()->willReturn(
            '
            class myclass  {
                public function __construct($context, $expression_lang) {}
            }
            '
        );

        $this->evaluate($filter, $context)->shouldBeAnInstanceOf('myclass');
    }
}
