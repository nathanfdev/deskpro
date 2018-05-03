<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Usergroup;

require_once 'AbstractEntityCheckTest.php';

class CheckUserUsergroupTest extends AbstractEntityCheckTest
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

        $person     = new Person();
        $person->id = $id;

        $bogus_ug     = new Usergroup();
        $bogus_ug->id = $id + 100;

        $person->addUsergroup($bogus_ug);
        $person->addUsergroup($object);

        $ticket         = new Ticket();
        $ticket->id     = $id;
        $ticket->person = $person;

        return $ticket;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckUserUsergroups';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'usergroup_ids';
    }

    /**
     * The entity class we are checking.
     *
     * @return string
     */
    public function getEntityClass()
    {
        return 'Application\DeskPRO\Entity\Usergroup';
    }
}
