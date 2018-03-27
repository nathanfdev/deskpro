<?php

namespace spec\Application\DeskPRO\Entity;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketParticipant;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \Application\DeskPRO\Entity\Ticket
 */
class TicketSpec extends ObjectBehavior
{
    public function it_knows_if_a_given_person_is_involved_with_ticket(
        Person $bob,
        Person $alice,
        Person $colin,
        Person $dimitri,
        Person $walmart_employee_but_not_manager,
        Organization $walmart
    ) {
        $bob->getId()->willReturn(1);
        $bob->getPrimaryEmail()->willReturn(null);
        $bob->getOrganization()->willReturn($walmart);
        $bob->isOrganizationManager()->willReturn(true);
        $bob->isAgent()->willReturn(false);

        $alice->getId()->willReturn(2);
        $alice->getRealLanguage()->willReturn(null);
        $alice->getOrganization()->willReturn(null);
        $alice->isOrganizationManager()->willReturn(false);
        $alice->isAgent()->willReturn(false);

        $colin->getId()->willReturn(3);
        $colin->getPrimaryEmail()->willReturn(null);
        $colin->getOrganization()->willReturn(null);
        $colin->isOrganizationManager()->willReturn(false);
        $colin->isAgent()->willReturn(false);

        $dimitri->getId()->willReturn(4);
        $dimitri->getPrimaryEmail()->willReturn(null);
        $dimitri->getOrganization()->willReturn(null);
        $dimitri->isOrganizationManager()->willReturn(false);
        $dimitri->isAgent()->willReturn(false);

        $walmart_employee_but_not_manager->getId()->willReturn(5);
        $walmart_employee_but_not_manager->getPrimaryEmail()->willReturn(null);
        $walmart_employee_but_not_manager->getOrganization()->willReturn($walmart);
        $walmart_employee_but_not_manager->isOrganizationManager()->willReturn(false);
        $walmart_employee_but_not_manager->isAgent()->willReturn(false);

        $this->setPerson($alice);
        $this->addParticipantPerson($colin);
        $this->setOrganization($walmart);

        $this->isInvolved($bob)->shouldBe(true);
        $this->isInvolved($alice)->shouldBe(true);
        $this->isInvolved($colin)->shouldBe(true);
        $this->isInvolved($dimitri)->shouldBe(false);
        $this->isInvolved($walmart_employee_but_not_manager)->shouldBe(false);
    }

    public function it_knows_if_a_given_person_is_the_owner_or_not(
        Person $bob,
        Person $alice
    ) {
        $this->setPerson($bob);

        $this->isOwner($bob)->shouldBe(true);
        $this->isOwner($alice)->shouldBe(false);
    }

    public function it_knows_if_a_given_person_is_a_participant_or_not(
        Person $bob,
        Person $alice,
        TicketParticipant $part

    ) {
        $bob->getId()->willReturn(1);
        $bob->getPrimaryEmail()->willReturn(null);
        $alice->getId()->willReturn(2);

        $this->addParticipantPerson($bob);

        $this->isParticipant($bob)->shouldBe(true);
        $this->isParticipant($alice)->shouldBe(false);
    }

    public function it_knows_if_a_given_person_is_an_organization_manager_for_the_ticket_or_not(
        Person $bob,
        Person $alice,
        Person $colin,
        Organization $walmart
    ) {
        $bob->getOrganization()->willReturn($walmart);
        $bob->isOrganizationManager()->willReturn(true);

        // alice is in the right organization, but is not a manager
        $alice->getOrganization()->willReturn($walmart);
        $alice->isOrganizationManager()->willReturn(false);

        $this->setOrganization($walmart);

        $this->isOrganizationManager($bob)->shouldBe(true);
        $this->isOrganizationManager($alice)->shouldBe(false);
        $this->isOrganizationManager($colin)->shouldBe(false);
    }
}
