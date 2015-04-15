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

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TermCompiler;


use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TicketChecker;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionProvider;
use DpTest\ApiTestCase;

abstract class AbstractPhpTermCompilerTest extends ApiTestCase
{
    protected function makChecker(PhpCheck $check, array $variables)
    {
        //
        // this method simulates the evaluateExpression method on the result PhpTicketCheckerInterface
        // in the tests assertions below, eval() will use this method
        //

        $lang = $this->get('term_engine.expression_language');
        $helper_pool = $this->get('term_engine.php.helper_pool');

        $agent = $this->prophesize('Application\DeskPRO\Entity\Person');
        $agent->getId()->willReturn(2); // in these tests, ME is always agent id=2
        $agent->getTeamIds()->willReturn(array(2)); // in these tests, ME is always agent id=2
        $context = new TermEngineContext($agent->reveal());

        // do some var replacing to emulate what the engine does...
        $check = clone $check;
        $i = 1;
        foreach ($check->getVariables() as $var_name => $val) {
            $check->renameVariable($var_name, 'var' . $i);
            $i++;
        }

        $checker = new TicketChecker($check, $context, $lang, $helper_pool);

        return $checker;
    }

    protected function assertTicketCheck(PhpCheck $php_check, $expected, $ticket_prophecy)
    {
        $ticket = $ticket_prophecy->reveal();

        $checker = $this->makChecker($php_check, array('ticket' => $ticket));

        $this->assertSame($expected, $checker->isTicketMatch($ticket), 'ticket check result is correct');
    }
}
