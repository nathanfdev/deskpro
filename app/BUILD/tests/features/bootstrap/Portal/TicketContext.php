<?php

namespace DpBehat\Portal;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\DomCrawler\Crawler;

class TicketContext extends BasePortalContext
{
    /**
     * @var array Map [ref => ticket]
     */
    public static $tickets = [];

    /**
     * @Given I remove all tickets
     */
    public function noTickets()
    {
        $tickets = $this->repository(Ticket::class)->findAll();
        foreach ($tickets as $ticket) {
            $this->em()->remove($ticket);
        }
        $this->em()->flush();
    }

    /**
     * @Given the following tickets exist:
     */
    public function theFollowingTicketsExist(TableNode $table)
    {
        $tickets = $table->getHash();

        foreach ($tickets as $ticket_data) {
            $person = $this->getWho($ticket_data['who']);
            App::setCurrentPerson($person);
            $ticket = new Ticket();
            $ticket->setPerson($person);
            $ticket->setSubject($ticket_data['subject']);
            $ticket->setDepartmentId(1);
            $ticket->setStatus($ticket_data['status']);
            $message          = new TicketMessage();
            $message->person  = $person;
            $message->ticket  = $ticket;
            $message->message = 'this is the message on the ticket';
            $org              = isset($ticket_data['organization']) ? $ticket_data['organization'] : null;
            if (strlen(trim($org)) > 0) {
                if ($organization = $this->getOrganization(trim($org))) {
                    $ticket->setOrganization($organization);
                }
            }
            $participant = isset($ticket_data['participant']) ? $ticket_data['participant'] : null;
            if (strlen(trim($participant)) > 0) {
                if ($participant = $this->getWho(trim($participant))) {
                    $ticket->addParticipantPerson($participant);
                }
            }
            $ticket->addMessage($message);
            $this->em()->persist($message);
            $this->em()->persist($ticket);
            $this->em()->flush();

            if (array_key_exists('ref', $ticket_data) && $ticket_data['ref']) {
                self::$tickets[$ticket_data['ref']] = $ticket;
            }
        }
    }

    /**
     * @Then I should see :num tickets :status
     */
    public function iShouldSeeTickets($num, $status)
    {
        $crawler       = new Crawler($html = $this->getSession()->getPage()->getHtml());
        $crawler       = $crawler->filter('.dpx-ticket-list-row');
        $status_counts = [];
        foreach ($crawler as $tr) {
            $tr_status = $tr->getAttribute('data-status');
            if (!array_key_exists($tr_status, $status_counts)) {
                $status_counts[$tr_status] = 0;
            }
            ++$status_counts[$tr_status];
        }

        expect(@$status_counts[$status] ?: null)->toBe($num);
    }

    /**
     * @Then I should see a header ticket count of :num
     */
    public function iShouldSeeAHeaderTicketCountOf($num)
    {
        $text = sprintf('Tickets (%s)', $num);
        $this->assertSession()->elementTextContains('css', '.dpx-ticket-count', $text);
    }

    /**
     * @Then I should see a header organization ticket count of :num
     */
    public function iShouldSeeAHeaderOrgTicketCountOf($num)
    {
        $text = sprintf('Organization Tickets (%s)', $num);
        $this->assertSession()->elementTextContains('css', '.dpx-ticket-count-org', $text);
    }

    /**
     * @Given I go to the ticket view page for ticket ID :id
     */
    public function iGoToTheTicketViewPageForTicketId($id)
    {
        $ticket = $this->getTicket($id);

        $this->visitPath('/ticket-view/'.$ticket->getAuth());
    }

    /**
     * @Then I should be on the ticket view page for ticket ID :id
     */
    public function iShouldBeOnTheTicketViewPageForTicketId($id)
    {
        $ticket = $this->getTicket($id);

        $this->assertSession()->addressEquals('/ticket-view/'.$ticket->getAuth());
        $this->assertSession()->statusCodeEquals(200);
    }

    /**
     * @Then :who should be a participant on ticket ID :id
     */
    public function shouldBeAParticipantOnTicketId($who, $id)
    {
        $ticket = $this->getTicket($id);
        $who    = $this->getWho($who);

        expect($ticket->isParticipant($who))->toBe(true);
    }

    /**
     * @Given :who am not involved with ticket ID :id
     */
    public function amNotInvolvedWithTicketId($who, $id)
    {
        $ticket = $this->getTicket($id);
        $who    = $this->getWho($who);

        expect($ticket->isInvolved($who))->toBe(false);
    }

    /**
     * @Then I should see the ticket reply form
     */
    public function iShouldNotSeeTheReplyForm()
    {
        $this->assertSession()->elementExists('css', '#ticket-reply-form');
    }

    /**
     * @param $org
     *
     * @return Organization|null|object
     */
    protected function getOrganization($org)
    {
        return $this->repository(Organization::class)->findOneBy(['name' => $org]);
    }

    /**
     * @param int $id
     *
     * @return Ticket|null|object
     */
    protected function getTicket($id)
    {
        return $this->repository(Ticket::class)->find($id);
    }

    /**
     * @param $who
     *
     * @return Person
     */
    protected function getWho($who)
    {
        return $this->get('user_details')->getWho($who);
    }
}
