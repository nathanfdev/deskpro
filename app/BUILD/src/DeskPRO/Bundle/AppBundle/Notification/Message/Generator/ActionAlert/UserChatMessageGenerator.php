<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\DataService\AgentDataService;
use DeskPRO\Bundle\AppBundle\EventListener\ClientMessage\ClientMessageEvent;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Event\UserChat\UserChatEvent;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\SystemEventGenerator;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine as TemplatingEngine;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class UserChatMessageGenerator.
 */
class UserChatMessageGenerator extends SystemEventGenerator
{
    /**
     * @var TemplatingEngine
     */
    private $templating;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage
     * @param AgentDataService      $agentDataService
     * @param TemplatingEngine      $templating
     * @param LanguageManager       $languageManager
     */
    public function __construct(
        EntityManager         $em,
        TokenStorageInterface $tokenStorage,
        AgentDataService      $agentDataService,
        TemplatingEngine      $templating,
        LanguageManager       $languageManager
    ) {
        parent::__construct($em, $tokenStorage, $agentDataService);

        $this->templating      = $templating;
        $this->languageManager = $languageManager;
    }

    /**
     * {@inheritdoc}
     *
     * @var UserChatEvent
     */
    public function createMessages(SystemEventInterface $event)
    {
        $messages = [];
        foreach ($this->getTarget($event) as $agentId) {
            $agent = $this->em->find(Person::class, $agentId);
            if ($agent) {
                $messages[] = new ActionAlert((int) $agentId, $this->getData($event, $agent), $event->getName());
            }
        }

        return $messages;
    }

    /**
     * @param LegacySystemEvent $event
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return array|int[]
     */
    protected function getTarget(LegacySystemEvent $event)
    {
        // new chat event should go to all online agents
        if ($event instanceof UserChatEvent && $event->getEventType() !== ClientMessageEvent::CHANNEL_CHAT_NEW) {
            $convo           = $this->getConversation($event);
            $agentId         = $convo->getAgentId();
            $participantsIds = $convo->getParticipantIds();

            return array_unique(array_merge($participantsIds, [$agentId]));
        }

        return parent::getTarget($event);
    }

    /**
     * @param UserChatEvent $event
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return ChatConversation|null|object
     */
    protected function getConversation(UserChatEvent $event)
    {
        $data = $event->getData();
        if (isset($data['conversation_id'])) {
            $convo = $this->em->find(ChatConversation::class, $data['conversation_id']);
            if (!$convo) {
                throw new NotFoundHttpException();
            }

            return $convo;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof UserChatEvent;
    }

    /**
     * @param UserChatEvent $event
     * @param Person        $agent
     *
     * @return array
     */
    private function getData(UserChatEvent $event, Person $agent)
    {
        $data = $event->getData();
        if ($event->getEventType() === ClientMessageEvent::CHANNEL_CHAT_NEW) {
            $convo = $this->getConversation($event);

            if ($convo->getPerson()) {
                /** @var \Application\DeskPRO\EntityRepository\Ticket $ticketRepo */
                $ticketRepo = $this->em->getRepository(Ticket::class);
                $tickets    = $ticketRepo->getLatestByUser($convo->getPerson(), 5, true);
            } else {
                $tickets = null;
            }

            if ($convo->getAgent()) {
                $data['agent_id'] = $convo->getAgent()->getId();
            }

            $this->languageManager->callWithLanguage($agent->getLanguage(), function () use (&$data, $convo, $tickets) {
                $data['html'] = $this->templating->render('AgentBundle:UserChat:chat-alert.html.twig', [
                    'convo'        => $convo,
                    'person'       => $convo->getPerson(),
                    'tickets'      => $tickets,
                    'session'      => $convo->getSession(),
                    'visitor_id'   => $convo->getVisitorId(),
                    'waiting_secs' => time() - $convo->getDateCreated()->getTimestamp(),
                    'url'          => null,
                ]);
            });
        }

        return $data;
    }
}
