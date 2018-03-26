<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\LabelTicket;
use Application\DeskPRO\Entity\Ticket;

require_once 'AbstractEntityCheckTest.php';

class CheckLabelTest extends AbstractEntityCheckTest
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

        $bogus        = new LabelTicket();
        $bogus->label = 'bogus';

        $ticket     = new Ticket();
        $ticket->id = $id;
        $ticket->addLabel($bogus);
        $ticket->addLabel($object);

        return $ticket;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckLabel';
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
        return 'Application\DeskPRO\Entity\LabelTicket';
    }

    /**
     * @param int $id
     *
     * @return object
     */
    public function createEntityObject($id)
    {
        $object        = new LabelTicket();
        $object->label = $id;

        return $object;
    }
}
