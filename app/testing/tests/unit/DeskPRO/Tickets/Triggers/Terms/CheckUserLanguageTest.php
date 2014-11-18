<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

require_once 'AbstractEntityCheckTest.php';

class CheckUserLanguageTest extends AbstractEntityCheckTest
{
    /**
     * @param  int    $id
     * @param $object
     * @return Ticket
     */
    public function createTicket($id, $object)
    {
        $person = new Person();
        $person->id = $id;
        $person->language = $object;

        $ticket = new Ticket();
        $ticket->id = $id;
        $ticket->person = $person;

        return $ticket;
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckUserLanguage';
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'language_ids';
    }

    /**
     * The entity class we are checking
     * @return string
     */
    public function getEntityClass()
    {
        return 'Application\DeskPRO\Entity\Language';
    }
}
