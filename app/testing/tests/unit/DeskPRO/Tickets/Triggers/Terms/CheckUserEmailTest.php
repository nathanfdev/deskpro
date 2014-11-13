<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;

require_once 'AbstractStringCheckTest.php';

class CheckUserEmailTest extends AbstractStringCheckTest
{
    /**
     * @param  int    $id
     * @param  string $test_string
     * @return Ticket
     */
    public function createTicket($id, $test_string)
    {
        $person = new Person();
        $person->id = $id;

        $email1 = new PersonEmail();
        $email1->email = '23847234@098309843.com';
        $person->addEmailAddress($email1);

        $email2 = new PersonEmail();
        $email2->email = $test_string;
        $person->addEmailAddress($email2);

        $ticket = new Ticket();
        $ticket->id = $id;
        $ticket->person = $person;

        return $ticket;
    }

    /**
     * @return string
     */
    public function getString1()
    {
        return 'example@example.com';
    }

    /**
     * @return string
     */
    public function getString2()
    {
        return 'example@example.com.other.tld';
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckUserEmail';
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'email';
    }
}
