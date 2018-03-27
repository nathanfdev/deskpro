<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationEmailDomain;
use Application\DeskPRO\Entity\Ticket;

require_once 'AbstractStringCheckTest.php';

class CheckOrgEmailDomainTest extends AbstractStringCheckTest
{
    /**
     * @param int    $id
     * @param string $test_string
     *
     * @return Ticket
     */
    public function createTicket($id, $test_string)
    {
        $org     = new Organization();
        $org->id = $id;

        $domain1         = new OrganizationEmailDomain();
        $domain1->domain = '12093821382103.com';
        $domain2         = new OrganizationEmailDomain();
        $domain2->domain = $test_string;

        $org->email_domains->add($domain1);
        $org->email_domains->add($domain2);

        $ticket               = new Ticket();
        $ticket->id           = $id;
        $ticket->organization = $org;

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
     * {@inheritdoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckOrgEmailDomain';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'email_domain';
    }
}
