<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\LabelOrganization;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Ticket;

require_once 'AbstractEntityCheckTest.php';

class CheckOrgLabelTest extends AbstractEntityCheckTest
{
    /**
     * @param int $id
     * @param $object
     *
     * @return Ticket
     */
    public function createTicket($id, $object)
    {
        if ($object === null) {
            // no test for nulls
            return;
        }

        $org = new Organization();

        $bogus        = new LabelOrganization();
        $bogus->label = 'bogus';

        $org->addLabel($bogus);
        $org->addLabel($object);

        $ticket               = new Ticket();
        $ticket->id           = $id;
        $ticket->organization = $org;

        return $ticket;
    }

    /**
     * Checks 'is label' on a ticket without an org.
     */
    public function testNoOrgIsLabel()
    {
        $ticket     = new Ticket();
        $ticket->id = 10;

        $checker = $this->createChecker('is', ['labels' => ['label']]);
        $this->assertFalse($checker->isTriggerMatch($ticket, $this->getExecContext()));

        $checker = $this->createChecker('is', ['labels' => ['label', 'label2']]);
        $this->assertFalse($checker->isTriggerMatch($ticket, $this->getExecContext()));

        $checker = $this->createChecker('contains', ['labels' => ['label']]);
        $this->assertFalse($checker->isTriggerMatch($ticket, $this->getExecContext()));

        $checker = $this->createChecker('contains', ['labels' => ['label', 'label2']]);
        $this->assertFalse($checker->isTriggerMatch($ticket, $this->getExecContext()));
    }

    /**
     * Check 'not label' on a ticket without an org.
     */
    public function testNoOrgNotLabel()
    {
        $ticket     = new Ticket();
        $ticket->id = 10;

        $checker = $this->createChecker('not', ['labels' => ['label']]);
        $this->assertTrue($checker->isTriggerMatch($ticket, $this->getExecContext()));

        $checker = $this->createChecker('not', ['labels' => ['label', 'label2']]);
        $this->assertTrue($checker->isTriggerMatch($ticket, $this->getExecContext()));

        $checker = $this->createChecker('notcontains', ['labels' => ['label']]);
        $this->assertTrue($checker->isTriggerMatch($ticket, $this->getExecContext()));

        $checker = $this->createChecker('notcontains', ['labels' => ['label', 'label2']]);
        $this->assertTrue($checker->isTriggerMatch($ticket, $this->getExecContext()));
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckOrgLabel';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'labels';
    }

    /**
     * The entity class we are checking.
     *
     * @return string
     */
    public function getEntityClass()
    {
        return 'Application\DeskPRO\Entity\LabelOrganization';
    }

    /**
     * @param int $id
     *
     * @return object
     */
    public function createEntityObject($id)
    {
        $object        = new LabelOrganization();
        $object->label = $id;

        return $object;
    }
}
