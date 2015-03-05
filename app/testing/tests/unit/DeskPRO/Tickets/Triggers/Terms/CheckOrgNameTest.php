<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Ticket;

require_once 'AbstractStringCheckTest.php';

class CheckOrgNameTest extends AbstractStringCheckTest
{
    /**
     * @param  int    $id
     * @param  string $test_string
     * @return Ticket
     */
    public function createTicket($id, $test_string)
    {
        $org = new Organization();
        $org->id = $id;
        $org->name = $test_string;

        $ticket = new Ticket();
        $ticket->id = $id;
        $ticket->organization = $org;

        return $ticket;
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckOrgName';
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'name';
    }
}
