<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\TicketChecker;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DpTest\ApiTestCase;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

abstract class AbstractPhpTermCompilerTest extends ApiTestCase
{
    /**
     * @var Logger
     */
    public static $logger;

    /**
     * @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermCompilerHelperPool
     */
    protected $helper_pool;

    /**
     * @param PhpCheck $check
     * @param array    $variables Extra vars you might need when making the helper pool
     *
     * @return \DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermCompilerHelperPool|object
     */
    protected function makeHelperPool(PhpCheck $check, array $variables)
    {
        if (!$this->helper_pool) {
            $this->helper_pool = $this->get('term_engine.php.helper_pool');
        }

        return $this->helper_pool;
    }

    /**
     * @param PhpCheck $check
     * @param array    $variables Extra vars you might need when making the checker
     *
     * @return TicketChecker
     */
    protected function makeChecker(PhpCheck $check, array $variables)
    {

        // this method simulates the evaluateExpression method on the result PhpTicketCheckerInterface
        // in the tests assertions below, eval() will use this method

        $lang = $this->get('term_engine.expression_language');

        $agent = $this->prophesize('Application\DeskPRO\Entity\Person');
        $agent->getId()->willReturn(2); // in these tests, ME is always agent id=2
        $agent->getTeamIds()->willReturn([2]); // in these tests, ME is always agent id=2
        $context = new TermEngineContext($agent->reveal());

        // do some var replacing to emulate what the engine does...
        $check = clone $check;
        $i     = 1;
        foreach ($check->getVariables() as $var_name => $val) {
            $check->renameVariable($var_name, 'var'.$i);
            ++$i;
        }

        if (!static::$logger) {
            static::$logger = new Logger('null');
            static::$logger->pushHandler(new NullHandler());
        }

        $checker = new TicketChecker($check, $context, $lang, $this->makeHelperPool($check, $variables), static::$logger);

        return $checker;
    }

    /**
     * @param PhpCheck $php_check
     * @param $expected
     * @param $ticket_prophecy
     */
    protected function assertTicketCheck(PhpCheck $php_check, $expected, $ticket_prophecy, $extra = [])
    {
        $ticket = $ticket_prophecy->reveal();

        $extra['ticket'] = $ticket;
        $checker         = $this->makeChecker($php_check, $extra);

        $this->assertSame($expected, $checker->isTicketMatch($ticket), 'ticket check result is correct');
    }
}
