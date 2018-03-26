<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermInterface;
use DpTest\DeskProTestCase;

abstract class AbstractStringCheckTest extends DeskProTestCase
{
    /**
     * @var Ticket
     */
    private $ticket1;

    /**
     * @var Ticket
     */
    private $ticket2;

    /**
     * @var ExecutorContext
     */
    private $exec_context1;

    /**
     * @var ExecutorContext
     */
    private $exec_context2;

    public function setup()
    {
        $this->ticket1       = $this->createTicket(1, $this->getString1());
        $this->ticket2       = $this->createTicket(2, $this->getString2());
        $this->exec_context1 = $this->createExecutorContext($this->ticket1);
        $this->exec_context2 = $this->createExecutorContext($this->ticket2);
    }

    /**
     * @param Ticket $ticket
     *
     * @return ExecutorContext
     */
    public function createExecutorContext(Ticket $ticket)
    {
        return new ExecutorContext();
    }

    /**
     * @return string
     */
    public function getString1()
    {
        return 'Test string 123';
    }

    /**
     * @return string
     */
    public function getString2()
    {
        return 'Test string 123 ABC 100 200 XYZ';
    }

    /**
     * @param int    $id
     * @param string $test_string
     *
     * @return Ticket
     */
    abstract public function createTicket($id, $test_string);

    /**
     * @param string $op
     * @param array  $options
     *
     * @return TriggerTermInterface
     */
    protected function createChecker($op, array $options)
    {
        $check_class = $this->getCheckClass();
        $opt_key     = $this->getCheckClassOptionKey();

        if (isset($options['%OPT%'])) {
            $options[$opt_key] = $options['%OPT%'];
            unset($options['%OPT%']);
        }

        $check = new $check_class($op, $options);

        return $check;
    }

    /**
     * The term checker class.
     *
     * @return string
     */
    abstract protected function getCheckClass();

    /**
     * The option key to supply IDs in.
     *
     * @return string
     */
    abstract protected function getCheckClassOptionKey();

    //################ op = is #################

    public function testIsEmptySearchMatch()
    {
        $check = $this->createChecker('is', ['%OPT%' => '']);
        $this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testIsExactMatch()
    {
        $check = $this->createChecker('is', ['%OPT%' => $this->getString1()]);
        $this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testIsExactCaseMatch()
    {
        $check = $this->createChecker('is', ['%OPT%' => strtoupper($this->getString1())]);
        $this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testIsWithPartialMatch()
    {
        $check = $this->createChecker('is', ['%OPT%' => $this->getString1()]);
        $this->assertFalse($check->isTriggerMatch($this->ticket2, $this->exec_context2));
    }

    public function testIsWithNoMatch()
    {
        $check = $this->createChecker('is', ['%OPT%' => 'Not a match']);
        $this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    //################ op = not #################

    public function testNotEmptySearchMatch()
    {
        $check = $this->createChecker('not', ['%OPT%' => '']);
        $this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testNotExactMatch()
    {
        $check = $this->createChecker('not', ['%OPT%' => $this->getString1()]);
        $this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testNotExactCaseMatch()
    {
        $check = $this->createChecker('not', ['%OPT%' => strtoupper($this->getString1())]);
        $this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testNotWithPartialMatch()
    {
        $check = $this->createChecker('not', ['%OPT%' => $this->getString1()]);
        $this->assertTrue($check->isTriggerMatch($this->ticket2, $this->exec_context2));
    }

    public function testNotWithNoMatch()
    {
        $check = $this->createChecker('not', ['%OPT%' => 'Not a match']);
        $this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    //################ op = contains #################

    public function testContainsEmptySearchMatch()
    {
        $check = $this->createChecker('contains', ['%OPT%' => '']);
        $this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testContainsExactMatch()
    {
        $check = $this->createChecker('contains', ['%OPT%' => $this->getString1()]);
        $this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testContainsExactCaseMatch()
    {
        $check = $this->createChecker('contains', ['%OPT%' => $this->getString1()]);
        $this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testContainsPartialExactMatch()
    {
        $check = $this->createChecker('contains', ['%OPT%' => $this->getString1()]);
        $this->assertTrue($check->isTriggerMatch($this->ticket2, $this->exec_context2));
    }

    public function testContainsPartialCaseMatch()
    {
        $check = $this->createChecker('contains', ['%OPT%' => $this->getString1()]);
        $this->assertTrue($check->isTriggerMatch($this->ticket2, $this->exec_context2));
    }

    public function testContainsNoMatch()
    {
        $check = $this->createChecker('contains', ['%OPT%' => 'Not a match']);
        $this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    //################ op = notcontains #################

    public function testNotContainsEmptySearchMatch()
    {
        $check = $this->createChecker('notcontains', ['%OPT%' => '']);
        $this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testNotContainsExactMatch()
    {
        $check = $this->createChecker('notcontains', ['%OPT%' => $this->getString1()]);
        $this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testNotContainsExactCaseMatch()
    {
        $check = $this->createChecker('notcontains', ['%OPT%' => $this->getString1()]);
        $this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testNotContainsPartialExactMatch()
    {
        $check = $this->createChecker('notcontains', ['%OPT%' => $this->getString1()]);
        $this->assertFalse($check->isTriggerMatch($this->ticket2, $this->exec_context2));
    }

    public function testNotContainsPartialCaseMatch()
    {
        $check = $this->createChecker('notcontains', ['%OPT%' => $this->getString1()]);
        $this->assertFalse($check->isTriggerMatch($this->ticket2, $this->exec_context2));
    }

    public function testNotContainsNoMatch()
    {
        $check = $this->createChecker('notcontains', ['%OPT%' => 'Not a match']);
        $this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    //################ op = is_regex #################

    public function testIsRegexNoDelims()
    {
        $check = $this->createChecker('is_regex', ['%OPT%' => '[a-z]+']);
        $this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testIsRegexWithDelims()
    {
        $check = $this->createChecker('is_regex', ['%OPT%' => '/[a-z]+/']);
        $this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testIsRegexWithDelims2()
    {
        $check = $this->createChecker('is_regex', ['%OPT%' => '#[a-z]+#']);
        $this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testIsRegexWithInvalidPattern()
    {
        $check = $this->createChecker('is_regex', ['%OPT%' => '/invlaid.+++/']);
        $this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testIsRegexWithAnchorStart()
    {
        $GLOBALS['begin'] = true;
        $check            = $this->createChecker('is_regex', ['%OPT%' => '/^'.preg_quote($this->getString1(), '/').'/']);
        $this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context1));
        unset($GLOBALS['begin']);
    }

    public function testIsRegexWithAnchorEnd()
    {
        $check = $this->createChecker('is_regex', ['%OPT%' => '/'.preg_quote($this->getString2(), '/').'$/']);
        $this->assertTrue($check->isTriggerMatch($this->ticket2, $this->exec_context2));
    }

    public function testIsRegexWithMod()
    {
        $check = $this->createChecker(
            'is_regex',
            ['%OPT%' => '/^'.preg_quote($this->getString1(), '/').'$/i']
        );
        $this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testIsRegexWithEvalMod()
    {
        $check = $this->createChecker(
            'is_regex',
            ['%OPT%' => '/^'.preg_quote($this->getString1(), '/').'$/e']
        );
        $this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    //################ op = is_not_regex #################

    public function testIsNotRegexWithMatch()
    {
        $check = $this->createChecker(
            'not_regex',
            ['%OPT%' => '/^'.preg_quote($this->getString1(), '/').'$/i']
        );
        $this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }

    public function testIsNotRegexMatch()
    {
        $check = $this->createChecker('not_regex', ['%OPT%' => '/^5319efc3d06e75319efc3d06e7$/i']);
        $this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context1));
    }
}
