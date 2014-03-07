<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermInterface;

abstract class AbstractStringCheckTest extends \DpUnitTestCase
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
	private $exec_context;

	public function runBefore()
	{
		$this->ticket1 = $this->createTicket(1, "Test subject 123");
		$this->ticket2 = $this->createTicket(2, "Test subject 123 ABC 100 200 XYZ");
		$this->exec_context = new ExecutorContext();
	}


	/**
	 * @param int $id
	 * @param string $test_string
	 * @return Ticket
	 */
	abstract public function createTicket($id, $test_string);


	/**
	 * @param string $op
	 * @param array $options
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
	 * The term checker class
	 * @return string
	 */
	abstract protected function getCheckClass();

	/**
	 * The option key to supply IDs in
	 * @return string
	 */
	abstract protected function getCheckClassOptionKey();

	################# op = is #################

	public function testIsEmptySearchMatch()
	{
		$check = $this->createChecker('is', array('%OPT%' => ''));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsExactMatch()
	{
		$check = $this->createChecker('is', array('%OPT%' => 'Test subject 123'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsExactCaseMatch()
	{
		$check = $this->createChecker('is', array('%OPT%' => 'TEST SUBJECT 123'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsWithPartialMatch()
	{
		$check = $this->createChecker('is', array('%OPT%' => 'Test subject 123'));
		$this->assertFalse($check->isTriggerMatch($this->ticket2, $this->exec_context));
	}

	public function testIsWithNoMatch()
	{
		$check = $this->createChecker('is', array('%OPT%' => 'Not a match'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}


	################# op = not #################

	public function testNotEmptySearchMatch()
	{
		$check = $this->createChecker('not', array('%OPT%' => ''));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testNotExactMatch()
	{
		$check = $this->createChecker('not', array('%OPT%' => 'Test subject 123'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testNotExactCaseMatch()
	{
		$check = $this->createChecker('not', array('%OPT%' => 'TEST SUBJECT 123'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testNotWithPartialMatch()
	{
		$check = $this->createChecker('not', array('%OPT%' => 'Test subject 123'));
		$this->assertTrue($check->isTriggerMatch($this->ticket2, $this->exec_context));
	}

	public function testNotWithNoMatch()
	{
		$check = $this->createChecker('not', array('%OPT%' => 'Not a match'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}


	################# op = contains #################

	public function testContainsEmptySearchMatch()
	{
		$check = $this->createChecker('contains', array('%OPT%' => ''));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testContainsExactMatch()
	{
		$check = $this->createChecker('contains', array('%OPT%' => 'Test subject 123'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testContainsExactCaseMatch()
	{
		$check = $this->createChecker('contains', array('%OPT%' => 'TEST SUBJECT 123'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testContainsPartialExactMatch()
	{
		$check = $this->createChecker('contains', array('%OPT%' => 'Test subject 123'));
		$this->assertTrue($check->isTriggerMatch($this->ticket2, $this->exec_context));
	}

	public function testContainsPartialCaseMatch()
	{
		$check = $this->createChecker('contains', array('%OPT%' => 'TEST SUBJECT 123'));
		$this->assertTrue($check->isTriggerMatch($this->ticket2, $this->exec_context));
	}

	public function testContainsNoMatch()
	{
		$check = $this->createChecker('contains', array('%OPT%' => 'Not a match'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}


	################# op = notcontains #################

	public function testNotContainsEmptySearchMatch()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => ''));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testNotContainsExactMatch()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => 'Test subject 123'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testNotContainsExactCaseMatch()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => 'TEST SUBJECT 123'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testNotContainsPartialExactMatch()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => 'Test subject 123'));
		$this->assertFalse($check->isTriggerMatch($this->ticket2, $this->exec_context));
	}

	public function testNotContainsPartialCaseMatch()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => 'TEST SUBJECT 123'));
		$this->assertFalse($check->isTriggerMatch($this->ticket2, $this->exec_context));
	}

	public function testNotContainsNoMatch()
	{
		$check = $this->createChecker('notcontains', array('%OPT%' => 'Not a match'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}


	################# op = is_regex #################

	public function testIsRegexNoDelims()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => '[a-z]+'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsRegexWithDelims()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => '/[a-z]+/'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsRegexWithDelims2()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => '#[a-z]+#'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsRegexWithInvalidPattern()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => '/invlaid.+++/'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsRegexWithAnchorStart()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => '^Test'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsRegexWithAnchorEnd()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => 'XYZ$'));
		$this->assertTrue($check->isTriggerMatch($this->ticket2, $this->exec_context));
	}

	public function testIsRegexWithMod()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => '/^test ticket/i'));
		$this->assertTrue($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}

	public function testIsRegexWithEvalMod()
	{
		$check = $this->createChecker('is_regex', array('%OPT%' => '/^test ticket/e'));
		$this->assertFalse($check->isTriggerMatch($this->ticket1, $this->exec_context));
	}
}