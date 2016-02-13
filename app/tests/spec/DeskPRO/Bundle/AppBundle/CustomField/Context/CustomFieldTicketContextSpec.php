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

/**
 * DeskPRO.
 */
namespace spec\DeskPRO\Bundle\AppBundle\CustomField\Context;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\CustomField\Context\CustomFieldTicketContext
 */
class CustomFieldTicketContextSpec extends ObjectBehavior
{
    public function let(Ticket $ticket, Person $person, Organization $organization)
    {
        $ticket->getPerson()->willReturn($person);
        $ticket->getOrganization()->willReturn($organization);

        $this->beConstructedWith($ticket);
    }

    public function it_extends_the_base_context_calss()
    {
        $this->shouldHaveType('DeskPRO\Bundle\AppBundle\CustomField\Context\CustomFieldContext');
    }

    public function it_always_uses_the_ticket_as_the_owner(
        Ticket $ticket
    ) {
        $this->getOwner('Application\DeskPRO\Entity\Ticket')->shouldReturn($ticket);
    }

    public function it_can_get_the_correct_context_for_person(
        Person $person
    ) {
        $this->getContext('Application\DeskPRO\Entity\Person')->shouldReturn($person);
    }

    public function it_can_get_the_correct_context_for_organization(
        Organization $organization
    ) {
        $this->getContext('Application\DeskPRO\Entity\Organization')->shouldReturn($organization);
    }
}
