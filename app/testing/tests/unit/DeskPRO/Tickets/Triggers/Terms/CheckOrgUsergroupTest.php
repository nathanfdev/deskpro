<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Usergroup;

require_once 'AbstractEntityCheckTest.php';

class CheckOrgUsergroupTest extends AbstractEntityCheckTest
{
    /**
     * @param  int    $id
     * @param $object
     * @return Ticket
     */
    public function createTicket($id, $object)
    {
        $org = new Organization();

        $bogus = new Usergroup();
        $bogus->id = $id + 100;
        $org->usergroups->add($bogus);
        $org->usergroups->add($object);

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
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckOrgUsergroups';
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'usergroup_ids';
    }

    /**
     * The entity class we are checking
     * @return string
     */
    public function getEntityClass()
    {
        return 'Application\DeskPRO\Entity\Usergroup';
    }
}
