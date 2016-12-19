<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\LabelPerson;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

require_once 'AbstractEntityCheckTest.php';

class CheckUserLabelTest extends AbstractEntityCheckTest
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

        $person = new Person();

        $bogus        = new LabelPerson();
        $bogus->label = 'bogus';

        $person->addLabel($bogus);
        $person->addLabel($object);

        $ticket         = new Ticket();
        $ticket->id     = $id;
        $ticket->person = $person;

        return $ticket;
    }

    public function testNoLabelIs()
    {
        $person = new Person();

        $ticket         = new Ticket();
        $ticket->id     = 5000;
        $ticket->person = $person;

        $checker = $this->createChecker('is', ['labels' => ['test']]);
        $this->assertFalse($checker->isTriggerMatch($ticket, $this->getExecContext()));

        $checker = $this->createChecker('is', ['labels' => ['test', 'test2']]);
        $this->assertFalse($checker->isTriggerMatch($ticket, $this->getExecContext()));
    }

    public function testNoLabelNot()
    {
        $person = new Person();

        $ticket         = new Ticket();
        $ticket->id     = 5000;
        $ticket->person = $person;

        $checker = $this->createChecker('not', ['labels' => ['test']]);
        $this->assertTrue($checker->isTriggerMatch($ticket, $this->getExecContext()));

        $checker = $this->createChecker('not', ['labels' => ['test', 'test2']]);
        $this->assertTrue($checker->isTriggerMatch($ticket, $this->getExecContext()));
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckUserLabel';
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
        return 'Application\DeskPRO\Entity\LabelPerson';
    }

    /**
     * @param int $id
     *
     * @return object
     */
    public function createEntityObject($id)
    {
        $object        = new LabelPerson();
        $object->label = $id;

        return $object;
    }
}
