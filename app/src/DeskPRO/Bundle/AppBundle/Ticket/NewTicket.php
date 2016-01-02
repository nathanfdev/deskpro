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
namespace DeskPRO\Bundle\AppBundle\Ticket;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\DuplicateTicketException;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitTicketAbuseCheck;
use DeskPRO\Bundle\AppBundle\Person\Context\CreatePersonContext;
use DeskPRO\Bundle\PortalBundle\CustomField\Context\CustomPerFieldManager;
use DeskPRO\Bundle\PortalBundle\Person\PersonFactory;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NewTicket.
 */
class NewTicket
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TicketManager
     */
    private $ticket_manager;

    /**
     * @var CustomPerFieldManager
     */
    private $custom_per_field_manager;

    /**
     * @var PersonFactory
     */
    private $person_factory;

    /**
     * @var AntiAbuse
     */
    private $anti_abuse;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TicketManager         $ticket_manager
     * @param CustomPerFieldManager $custom_per_field_manager
     * @param PersonFactory         $person_factory
     * @param AntiAbuse             $anti_abuse
     */
    public function __construct(
        EntityManager         $em,
        TicketManager         $ticket_manager,
        CustomPerFieldManager $custom_per_field_manager,
        PersonFactory         $person_factory,
        AntiAbuse             $anti_abuse
    ) {
        $this->em                       = $em;
        $this->ticket_manager           = $ticket_manager;
        $this->custom_per_field_manager = $custom_per_field_manager;
        $this->person_factory           = $person_factory;
        $this->anti_abuse               = $anti_abuse;
    }

    /**
     * @param Ticket        $ticket
     * @param TicketMessage $ticket_message
     * @param Person        $person
     * @param Request       $request
     *
     * @return Ticket
     */
    public function acceptNewTicketForGuest(Ticket $ticket, TicketMessage $ticket_message, Person $person, Request $request)
    {
        // in this case we are authorized to make a person from a guest
        $person_context = new CreatePersonContext(Person::CREATED_WEB_PERSON);
        $person->setName($person->getDisplayName());
        $person = $this->person_factory->createPersonByEmail($person->getEmailAddress(), $person_context);

        $ticket->person = $person;
        $ticket->setPerson($person);
        $ticket_message->setPerson($person);
        foreach ($ticket_message->getAttachments() as $attachment) {
            $blob = $attachment->getBlob();
            if ($blob) {
                $blob->is_temp = false;
            }

            $attachment->setPerson($person);
        }

        return $this->acceptNewTicket($ticket, $person, $request);
    }

    /**
     * @param Ticket  $ticket
     * @param Person  $person
     * @param Request $request
     *
     * @return Ticket
     */
    public function acceptNewTicket(Ticket $ticket, Person $person, Request $request)
    {
        $this->submitNewTicketAbuseCheck($person, $request->getClientIp());

        return $this->saveNewTicket($ticket, $person);
    }

    /**
     * @param string $person
     * @param string $ip
     */
    public function submitNewTicketAbuseCheck($person, $ip)
    {
        $check = new SubmitTicketAbuseCheck($person, $ip);
        $this->anti_abuse->check($check);
    }

    /**
     * @param Ticket $ticket
     * @param Person $person
     *
     * @throws \Exception
     *
     * @return Ticket
     */
    protected function saveNewTicket(Ticket $ticket, Person $person)
    {
        $this->em->beginTransaction();

        try {
            // allow all blobs for a new ticket
            foreach ($ticket->messages as $message) {
                foreach ($message->getAttachments() as $attachment) {
                    $blob = $attachment->getBlob();
                    if ($blob) {
                        $blob->is_temp = false;
                    }
                }
            }

            $this->em->persist($ticket);

            // we handle this the new way (TicketManager), so disable the doctrine auto ticket process
            $ticket->disableAutoTicketProcess();
            $context = $this->ticket_manager->createUserExecutorContext($person, 'newticket', 'portal');

            $this->ticket_manager->saveTicket($ticket, $context);
            $this->em->flush();
            $this->custom_per_field_manager->flushDataQueue();
            $this->em->commit();
        } catch (DuplicateTicketException $e) {
            $this->em->rollback();
            $ticket = $this->em->find('DeskPRO:Ticket', $e->ticket_id);

            return $ticket;
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        return $ticket;
    }
}
