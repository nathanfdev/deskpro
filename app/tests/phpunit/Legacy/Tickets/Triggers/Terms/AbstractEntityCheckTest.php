<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermInterface;
use DpTest\DeskProTestCase;

abstract class AbstractEntityCheckTest extends DeskProTestCase
{
    /**
     * @var Ticket
     */
    private $ticket;

    /**
     * @var Ticket
     */
    private $ticket_null;

    /**
     * @var ExecutorContext
     */
    private $exec_context;

    /**
     * @var object
     */
    protected $object1;

    /**
     * @var object
     */
    protected $object2;

    public function setup()
    {
        $this->object1 = $this->createEntityObject(1);
        $this->object2 = $this->createEntityObject(2);

        $this->exec_context = new ExecutorContext();

        $this->ticket      = $this->createTicket(1, $this->object1);
        $this->ticket_null = $this->createTicket(1, null);
    }

    /**
     * @return ExecutorContext
     */
    protected function getExecContext()
    {
        return $this->exec_context;
    }

    /**
     * Called exactly twice with $id being 1 and 2.
     *
     * IMPORTANT:
     * 1) The first object should reset the state, then attempt to re-apply the object property (for checking 'touched' types).
     *
     * @param int $id
     *
     * @return object
     */
    public function createEntityObject($id)
    {
        $ent_class = $this->getEntityClass();

        $object     = new $ent_class();
        $object->id = $id;

        return $object;
    }

    /**
     * Should return a ticket if ticket can be created with $object.
     *
     * @param int   $id
     * @param mixed $object An object, may also be null for null tests
     *
     * @return Ticket|null
     */
    abstract public function createTicket($id, $object);

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

    /**
     * The entity class we are checking.
     *
     * @return string
     */
    abstract public function getEntityClass();

    public function testIsMatchSingleOption()
    {
        $check = $this->createChecker('is', array('%OPT%' => array(1)));
        $this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
    }

    public function testMatchingNullOption()
    {
        if (!$this->ticket_null) {
            return;
        }

        $check = $this->createChecker('is', array('%OPT%' => array(0)));
        $this->assertTrue($check->isTriggerMatch($this->ticket_null, $this->exec_context));

        $check = $this->createChecker('not', array('%OPT%' => array(0)));
        $this->assertFalse($check->isTriggerMatch($this->ticket_null, $this->exec_context));

        $check = $this->createChecker('is', array('%OPT%' => array(0)));
        $this->assertFalse($check->isTriggerMatch($this->ticket, $this->exec_context));

        $check = $this->createChecker('not', array('%OPT%' => array(0)));
        $this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
    }

    public function testIsMatchMultipleOptions()
    {
        $check = $this->createChecker('is', array('%OPT%' => array(1, 2)));
        $this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
    }

    public function testIsMatchInvalidOptions()
    {
        $check = $this->createChecker('is', array('%OPT%' => array('invalid', 1, 2, 100)));
        $this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
    }

    public function testContainsMatchSingleOption()
    {
        $check = $this->createChecker('contains', array('%OPT%' => array(1)));
        $this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
    }

    public function testContainsMatchMultipleOptions()
    {
        $check = $this->createChecker('contains', array('%OPT%' => array(1, 2)));
        $this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
    }

    public function testContainsMatchInvalidOptions()
    {
        $check = $this->createChecker('contains', array('%OPT%' => array('invalid', 1, 2, 100)));
        $this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
    }

    public function testNotEmptyOption()
    {
        try {
            $check = $this->createChecker('not', array('%OPT%' => array()));
            $this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
        } catch (\Orb\Util\CheckedOptionsException $e) {
            // Some optiosn dont allow empty, so dont test those checks
        }
    }

    public function testNotInvalidOption()
    {
        $check = $this->createChecker('not', array('%OPT%' => array('invalid')));
        $this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
    }

    public function testNotOption()
    {
        $check = $this->createChecker('not', array('%OPT%' => array(2)));
        $this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
    }

    public function testNotMultipleOptions()
    {
        $check = $this->createChecker('not', array('%OPT%' => array(500, 600)));
        $this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
    }

    public function testNotContainsSingleOption()
    {
        $check = $this->createChecker('notcontains', array('%OPT%' => array(500)));
        $this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
    }

    public function testNotContainsMultipleOptions()
    {
        $check = $this->createChecker('notcontains', array('%OPT%' => array(500, 600)));
        $this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
    }

    public function testNotContainsInvalidOptions()
    {
        $check = $this->createChecker('notcontains', array('%OPT%' => array('invalid', 'abc')));
        $this->assertTrue($check->isTriggerMatch($this->ticket, $this->exec_context));
    }
}
