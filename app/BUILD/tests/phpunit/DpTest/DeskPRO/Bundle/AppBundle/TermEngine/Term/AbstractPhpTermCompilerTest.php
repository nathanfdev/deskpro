<?php

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
