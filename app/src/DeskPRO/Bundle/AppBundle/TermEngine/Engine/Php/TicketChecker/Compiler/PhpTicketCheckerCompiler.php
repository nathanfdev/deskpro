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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\Compiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Compiler\PhpCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Dumper\PhpClass;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Dumper\PhpMethod;

class PhpTicketCheckerCompiler extends PhpCompiler
{
    public function enginePreCompile(PhpClass $php_class)
    {
        $php_class->addDependencyInjection(
            'context',
            '\DeskPRO\Bundle\AppBundle\TermEngine\TermEngineContext'
        );

        $php_class->addDependencyInjection(
            'expression_language',
            '\DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage'
        );

        // this is the method the outside world will call (well, the engine will call it)
        $ticket_check_method = new PhpMethod();
        $ticket_check_method->setName('isTicketMatch');
        $ticket_check_method->addArgument('ticket', '\Application\DeskPRO\Entity\Ticket');
        // here we add logging, events, or whatever other hooks we want to before the main check
        $ticket_check_method->setCode(
            'return (bool) $this->mainCheck($ticket);'
        );
        $php_class->addMethod($ticket_check_method);
    }

    public function enginePostCompile(PhpClass $php_class)
    {
        // let the term compilers use this method to evaluate expressions
        $evaluate_expression = new PhpMethod();
        $evaluate_expression->setName('evaluateExpression');
        $evaluate_expression->setVisibility('protected');
        $evaluate_expression->addArgument('expression');
        $evaluate_expression->setCode(
            'return $this->expression_language
    ->evaluate($expression, array(\'agent\' => $this->context->getAgent()));'
        );
    }
}
