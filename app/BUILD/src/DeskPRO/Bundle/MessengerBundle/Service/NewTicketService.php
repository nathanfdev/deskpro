<?php

namespace DeskPRO\Bundle\MessengerBundle\Service;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\Tickets\DuplicateTicketException;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\TicketStatusDataService;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Person\Context\CreatePersonContext;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Person\PersonFactory;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NewTicketService.
 */
class NewTicketService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TicketManager
     */
    private $ticketManager;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * @var PersonFactory
     */
    private $person_factory;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var TicketStatusDataService
     */
    private $ticketStatusService;

    /**
     * NewTicket constructor.
     *
     * @param EntityManager           $em
     * @param TicketManager           $ticketManager
     * @param LanguageManager         $languageManager
     * @param PersonFactory           $person_factory
     * @param BrandStack              $brandStack
     * @param TicketStatusDataService $ticketStatusService
     */
    public function __construct(
        EntityManager $em,
        TicketManager $ticketManager,
        LanguageManager $languageManager,
        PersonFactory $person_factory,
        BrandStack $brandStack,
        TicketStatusDataService $ticketStatusService
    ) {
        $this->em                  = $em;
        $this->ticketManager       = $ticketManager;
        $this->languageManager     = $languageManager;
        $this->person_factory      = $person_factory;
        $this->brandStack          = $brandStack;
        $this->ticketStatusService = $ticketStatusService;
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
        $language = $this->languageManager->getLanguageStack()->getActiveOrDefault();
        $person   = $person ?: new PersonGuest();
        $brand    = $brand ?: $this->brandStack->getActive()->getBrand();

        $ticket = $this->ticketManager->createTicket();
        $ticket->setPerson($person);
        $ticket->setBrand($brand);
        $ticket->setLanguage($language);

        if ($creationSystem) {
            $ticket->setCreationSystem($creationSystem);
        }

        $ticketMessage = new TicketMessage();
        $ticketMessage->setVisitorId($visitor_id);
        $ticketMessage->setIpAddress($request->getClientIp());
        $ticketMessage->setPerson($person);
        $ticket->addMessage($ticketMessage);

        return $ticket;
    }

    /**
     * @param Ticket  $ticket
     * @param array   $requestData
     * @param Form    $guestForm
     * @param string  $eventMethod
     *
     * @return Ticket
     */
    public function acceptNewTicketForGuest(Ticket $ticket, $requestData, Form $guestForm, $eventMethod)
    {
        $person = $ticket->getPerson();

        $existPerson = $this->person_factory->getPersonByEmail($person->getEmailAddress());
        if ($existPerson) {
            $person = $existPerson;
        } else {
            // in this case we are authorized to make a person from a guest
            $person_context = new CreatePersonContext(Person::CREATED_WEB_PERSON);
            $person->setName($person->getDisplayName());
            $person = $this->person_factory->createPersonByEmail($person->getEmailAddress(), $person_context);
        }

        $ticket->setPerson($person);
        $guestForm->submit($requestData);

        return $this->acceptNewTicket($ticket, $eventMethod);
    }

    /**
     * @param Ticket  $ticket
     * @param Request $request
     * @param string  $eventMethod
     *
     * @return Ticket
     */
    public function acceptNewTicket(Ticket $ticket, $eventMethod)
    {
        $person = $ticket->getPerson();

        return $this->saveNewTicket($ticket, $person, $eventMethod);
    }

    /**
     * @param Ticket $ticket
     * @param Person $person
     * @param string $eventMethod
     *
     * @throws \Exception
     *
     * @return Ticket
     */
    private function saveNewTicket(Ticket $ticket, Person $person, $eventMethod)
    {
        $this->ticketManager->markAsManaged($ticket);
        $ticketStatus = $this->ticketStatusService->findStatusOrException($ticket->getStatus());
        $ticket->setTicketStatus($ticketStatus);

        $this->em->beginTransaction();

        try {
            // allow all blobs for a new ticket
            $this->em->persist($ticket);

            $context = $this->ticketManager->createUserExecutorContext($person, 'newticket', $eventMethod);

            $this->ticketManager->saveTicket($ticket, $context);
            $this->em->flush();
            $this->em->commit();
        } catch (DuplicateTicketException $e) {
            $this->em->rollback();
            $ticket = $this->em->find(Ticket::class, $e->ticket_id);

            return $ticket;
        } catch (\Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        return $ticket;
    }
}
