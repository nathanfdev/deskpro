<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\Tools;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\Actions\SendAgentAlert;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\StateChangeRecorder;
use Application\DeskPRO\Tickets\TicketManager;
use Doctrine\ORM\EntityManager;
use Faker\Factory as FakerFactory;
use Orb\Util\Strings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RandomAgentAlertGenerator.
 */
class RandomAgentAlertGenerator
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
     * @var DeskproContainer
     */
    private $container;

    /**
     * @var Person[]
     */
    private $peopleCache = null;

    /**
     * @var Ticket[]
     */
    private $ticketsCache = null;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param TicketManager      $ticketManager
     * @param ContainerInterface $container
     */
    public function __construct(EntityManager $em, TicketManager $ticketManager, ContainerInterface $container)
    {
        $this->faker         = FakerFactory::create();
        $this->em            = $em;
        $this->ticketManager = $ticketManager;
        $this->container     = $container;
    }

    /**
     * @param Person $person
     */
    public function generateRandomAlert(Person $person)
    {
        /** @var Ticket $ticket */
        /** @var Person $performer */
        $ticket    = $this->faker->randomElement($this->loadTickets());
        $performer = $this->faker->randomElement($this->loadPeople());
        $type      = $this->faker->randomElement(['new_ticket', 'updated', 'reply', 'note']);

        call_user_func_array(
            [$this, Strings::underscoreToCamelCase('generate_'.$type.'_alert')],
            [$person, $performer, $ticket]
        );
    }

    /**
     * @param Person $person
     * @param Person $performer
     * @param Ticket $ticket
     */
    public function generateNewTicketAlert(Person $person, Person $performer, Ticket $ticket)
    {
        $context = $this->ticketManager->createAgentExecutorContext(
            $performer,
            ExecutorContext::EVENT_NEW,
            ExecutorContext::METHOD_WEB
        );

        $stateRecorder = $ticket->getStateChangeRecorder();
        $reflection    = new \ReflectionClass(StateChangeRecorder::class);

        $property = $reflection->getProperty('no_id');
        $property->setAccessible(true);
        $property->setValue($stateRecorder, true);

        $this->sendAlert($person, $ticket, $context);
    }

    /**
     * @param Person $person
     * @param Person $performer
     * @param Ticket $ticket
     */
    public function generateUpdatedAlert(Person $person, Person $performer, Ticket $ticket)
    {
        $context = $this->ticketManager->createAgentExecutorContext(
            $performer,
            ExecutorContext::EVENT_UPDATE,
            ExecutorContext::METHOD_WEB
        );

        $ticket->setSubject('Changed subject');
        $this->sendAlert($person, $ticket, $context);
    }

    /**
     * @param Person $person
     * @param Person $performer
     * @param Ticket $ticket
     */
    public function generateReplyAlert(Person $person, Person $performer, Ticket $ticket)
    {
        $context = $this->ticketManager->createAgentExecutorContext(
            $performer,
            ExecutorContext::EVENT_REPLY,
            ExecutorContext::METHOD_WEB
        );

        $message = new TicketMessage();
        $message->setPerson($performer);
        $message->setMessage('Ticket message');

        $ticket->addMessage($message);
        $this->sendAlert($person, $ticket, $context);
    }

    /**
     * @param Person $person
     * @param Person $performer
     * @param Ticket $ticket
     */
    public function generateNoteAlert(Person $person, Person $performer, Ticket $ticket)
    {
        $context = $this->ticketManager->createAgentExecutorContext(
            $performer,
            ExecutorContext::EVENT_REPLY,
            ExecutorContext::METHOD_WEB
        );

        $message = new TicketMessage();
        $message->setPerson($performer);
        $message->setMessage('Ticket note');
        $message->setAsAgentNote(true);

        $ticket->addMessage($message);
        $this->sendAlert($person, $ticket, $context);
    }

    /**
     * @param Person                   $person
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    private function sendAlert(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $alertSender = new SendAgentAlert([
            'agent_ids'   => [$person->getId()],
            'ticket_logs' => [],
        ]);

        $alertSender->setContainer($this->container);
        $alertSender->applyAction($ticket, $context);
    }

    /**
     * @return Person[]
     */
    private function loadPeople()
    {
        if (null === $this->peopleCache) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('p')
                ->from(Person::class, 'p')
                ->setMaxResults(1000)
            ;

            $this->peopleCache = $qb->getQuery()->getResult();
        }

        return $this->peopleCache;
    }

    /**
     * @return Ticket[]
     */
    private function loadTickets()
    {
        if (null === $this->ticketsCache) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('t')
                ->from(Ticket::class, 't')
                ->setMaxResults(1000)
            ;

            $this->ticketsCache = $qb->getQuery()->getResult();
        }

        return $this->ticketsCache;
    }
}
