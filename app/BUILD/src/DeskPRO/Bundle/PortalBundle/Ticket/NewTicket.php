<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Ticket;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\Tickets\DuplicateTicketException;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitTicketAbuseCheck;
use DeskPRO\Bundle\AppBundle\CustomField\Context\CustomPerFieldManager;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Person\Context\CreatePersonContext;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Person\PersonFactory;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * @var LanguageManager
     */
    private $language_manager;

    /**
     * @var PersonFactory
     */
    private $person_factory;

    /**
     * @var AntiAbuse
     */
    private $anti_abuse;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TicketManager         $ticket_manager
     * @param CustomPerFieldManager $custom_per_field_manager
     * @param LanguageManager       $language_manager
     * @param PersonFactory         $person_factory
     * @param AntiAbuse             $anti_abuse
     * @param UrlGeneratorInterface $urlGenerator
     * @param BrandStack            $brandStack
     */
    public function __construct(
        EntityManager         $em,
        TicketManager         $ticket_manager,
        CustomPerFieldManager $custom_per_field_manager,
        LanguageManager       $language_manager,
        PersonFactory         $person_factory,
        AntiAbuse             $anti_abuse,
        UrlGeneratorInterface $urlGenerator,
        BrandStack            $brandStack
    ) {
        $this->em                       = $em;
        $this->ticket_manager           = $ticket_manager;
        $this->custom_per_field_manager = $custom_per_field_manager;
        $this->language_manager         = $language_manager;
        $this->person_factory           = $person_factory;
        $this->anti_abuse               = $anti_abuse;
        $this->urlGenerator             = $urlGenerator;
        $this->brandStack               = $brandStack;
    }

    /**
     * @param Request     $request
     * @param string      $visitor_id
     * @param Person      $person
     * @param Brand       $brand
     * @param string|null $creationSystem
     *
     * @return Ticket
     */
    public function createNewTicket(
        Request $request,
        $visitor_id,
        Person $person = null,
        Brand $brand = null,
        $creationSystem = null
    ) {
        $language = $this->language_manager->getLanguageStack()->getActiveOrDefault();
        $person   = $person ?: new PersonGuest();
        $brand    = $brand ?: $this->brandStack->getActive()->getBrand();

        $ticket = $this->ticket_manager->createTicket();
        $ticket->setPerson($person);
        $ticket->setBrand($brand);
        $ticket->setLanguage($language);

        if ($creationSystem) {
            $ticket->setCreationSystem($creationSystem);
        }

        $ticket_message = new TicketMessage();
        $ticket_message->setVisitorId($visitor_id);
        $ticket_message->setIpAddress($request->getClientIp());
        $ticket_message->setPerson($person);
        $ticket->addMessage($ticket_message);

        return $ticket;
    }

    /**
     * @param Ticket  $ticket
     * @param Request $request
     * @param Form    $guestForm
     *
     * @return Ticket
     */
    public function acceptNewTicketForGuest(Ticket $ticket, Request $request, Form $guestForm)
    {
        $person = $ticket->getPerson();

        $exist_person = $this->person_factory->getPersonByEmail($person->getEmailAddress());
        if ($exist_person) {
            $person = $exist_person;
        } else {
            // in this case we are authorized to make a person from a guest
            $person_context = new CreatePersonContext(Person::CREATED_WEB_PERSON);
            $person->setName($person->getDisplayName());
            $person = $this->person_factory->createPersonByEmail($person->getEmailAddress(), $person_context);
        }

        $ticket->setPerson($person);
        $guestForm->handleRequest($request);

        return $this->acceptNewTicket($ticket, $request);
    }

    /**
     * @param Ticket  $ticket
     * @param Request $request
     *
     * @return Ticket
     */
    public function acceptNewTicket(Ticket $ticket, Request $request)
    {
        $person = $ticket->getPerson();
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
        $check->setResponse(new RedirectResponse($this->urlGenerator->generate('portal_new_ticket')));
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
        $this->ticket_manager->markAsManaged($ticket);

        $this->em->beginTransaction();

        try {
            // allow all blobs for a new ticket
            $this->em->persist($ticket);

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
